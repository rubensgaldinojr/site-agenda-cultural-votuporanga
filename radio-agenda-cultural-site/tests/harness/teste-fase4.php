<?php
// Teste em PHP puro da lógica da Fase 4 (formatos, vídeo/capítulos, Spotify, título automático da
// música, tirinhas, relacionados, compartilhar, países/bandeiras, filtros do blog, autoria,
// anti-spam dos comentários). Roda sem navegador: php teste-fase4.php
$_SERVER['REQUEST_URI'] = '/f4/blog/';
require __DIR__ . '/wpstubs4.php';
require THEME_DIR . '/inc/helpers.php';
require THEME_DIR . '/inc/recados.php';
require THEME_DIR . '/inc/autor.php';
require THEME_DIR . '/inc/blog.php';
require THEME_DIR . '/inc/formatos.php';
require THEME_DIR . '/inc/comentarios.php';

$falhas = 0;
function ok($c, $m) { global $falhas; echo ($c ? 'OK   ' : 'FALHA') . ' ' . $m . "\n"; if (!$c) $falhas++; }
function tenta($fn) { try { $fn(); return null; } catch (Exception $e) { return $e->getMessage(); } }

/* ---- países e bandeiras ---- */
$p = rac_paises();
ok(count($p) >= 250 && $p['br'] === 'Brasil' && $p['ie'] === 'Irlanda' && $p['us'] === 'Estados Unidos', 'lista de países em português (' . count($p) . ')');
$semArquivo = array_filter(array_keys($p), fn($iso) => !is_file(THEME_DIR . "/assets/flags/$iso.svg"));
ok(!$semArquivo, 'todo país da lista tem o arquivo da bandeira' . ($semArquivo ? ': faltam ' . implode(',', $semArquivo) : ''));
ok(rac_bandeira_html('xx') === '' && strpos(rac_bandeira_html('BR'), '/assets/flags/br.svg') !== false, 'bandeira: código inexistente não gera imagem; maiúsculas aceitas');

/* ---- formatos ---- */
ok(array_keys(rac_formatos_post()) === ['texto', 'poesia', 'video', 'musica', 'tirinha', 'imagem', 'citacao'], 'os 7 formatos, na ordem do menu do editor');
ok(rac_formato_do_post(601) === 'texto' && rac_formato_do_post(604) === 'musica' && rac_formato_do_post(9999) === 'texto', 'formato do post; sem formato = texto');
$GLOBALS['fields'][601]['formato'] = 'inexistente';
ok(rac_formato_do_post(601) === 'texto', 'formato desconhecido cai em "texto"');
$GLOBALS['fields'][601]['formato'] = 'texto';
foreach (array_keys(rac_formatos_post()) as $f) ok(is_file(THEME_DIR . "/template-parts/formatos/$f.php"), "existe o arquivo de modelo do formato $f");

/* ---- campos ACF registrados ---- */
do_action('acf/init');
$grupos = $GLOBALS['acf_groups'] ?? [];
$chaves = array_column($grupos, 'key');
ok(in_array('group_rac_autor', $chaves, true) && count($grupos) >= 3, 'grupos de campos registrados por código: ' . implode(', ', $chaves));
$nomes = []; $keys = [];
$colhe = function ($campos) use (&$colhe, &$nomes, &$keys) { foreach ($campos as $c) { $nomes[] = $c['name'] ?? ''; $keys[] = $c['key']; if (!empty($c['sub_fields'])) $colhe($c['sub_fields']); } };
foreach ($grupos as $g) $colhe($g['fields']);
ok(count($keys) === count(array_unique($keys)), 'nenhuma chave de campo repetida');
foreach (['formato', 'video_url', 'video_capitulos', 'artista_nome', 'artista_pais', 'artista_genero', 'artista_link', 'artista_instagram', 'artista_estacao', 'artista_titulo_auto', 'tirinha_autor', 'tirinha_ano', 'tirinha_fonte', 'tirinha_numero', 'imagem_credito', 'citacao_texto', 'citacao_autor', 'citacao_obra', 'blog_titulo', 'blog_texto', 'autor_foto', 'autor_papel', 'autor_desde', 'autor_citacao', 'autor_citacao_de'] as $n) {
    ok(in_array($n, $nomes, true), "campo ACF registrado: $n");
}
$cond = [];
foreach ($grupos as $g) foreach ($g['fields'] as $c) if (($c['name'] ?? '') === 'video_url') $cond = $c['conditional_logic'] ?? [];
ok(!empty($cond) && $cond[0][0]['value'] === 'video', 'campos do vídeo só aparecem quando o formato é "Vídeo"');

/* ---- vídeo ---- */
ok(rac_dados_video('https://www.youtube.com/watch?v=dQw4w9WgXcQ') === ['plataforma' => 'youtube', 'id' => 'dQw4w9WgXcQ'], 'YouTube: link de watch');
ok(rac_dados_video('https://youtu.be/dQw4w9WgXcQ?t=10')['id'] === 'dQw4w9WgXcQ', 'YouTube: link curto');
ok(rac_dados_video('https://www.youtube.com/shorts/dQw4w9WgXcQ')['id'] === 'dQw4w9WgXcQ' && rac_dados_video('https://www.youtube.com/embed/dQw4w9WgXcQ')['id'] === 'dQw4w9WgXcQ' && rac_dados_video('https://www.youtube.com/live/dQw4w9WgXcQ')['id'] === 'dQw4w9WgXcQ', 'YouTube: shorts, embed e live');
ok(rac_dados_video('https://www.youtube.com/watch?feature=share&v=dQw4w9WgXcQ')['id'] === 'dQw4w9WgXcQ', 'YouTube: v= depois de outro parâmetro');
ok(rac_dados_video('https://vimeo.com/76979871') === ['plataforma' => 'vimeo', 'id' => '76979871'], 'Vimeo');
ok(rac_dados_video('https://exemplo.com/video.mp4') === null && rac_dados_video('') === null, 'link desconhecido ou vazio: sem player');
$yt = ['plataforma' => 'youtube', 'id' => 'dQw4w9WgXcQ'];
ok(rac_src_video($yt) === 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0' && rac_src_video($yt, 90, true) === 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0&start=90&autoplay=1', 'endereço do iframe do YouTube (privacidade, início e autoplay)');
ok(rac_src_video(['plataforma' => 'vimeo', 'id' => '5'], 30, true) === 'https://player.vimeo.com/video/5?autoplay=1#t=30s', 'endereço do Vimeo com início');
ok(rac_segundos('02:15') === 135 && rac_segundos('1:05:30') === 3930 && rac_segundos('45') === 45 && rac_segundos('00:00') === 0, 'tempo "mm:ss", "h:mm:ss" e "ss" para segundos');
$cap = rac_capitulos("00:00 | Abertura\n  02:15 - A oficina  \n1:05:30 – Despedida\nlinha sem tempo\n\n3:00|");
ok(count($cap) === 3 && $cap[1] === ['seg' => 135, 'tempo' => '02:15', 'nome' => 'A oficina'] && $cap[2]['seg'] === 3930, 'capítulos: aceita "|", "-" e "–"; ignora linhas sem tempo ou sem título');

/* ---- player do artista ---- */
foreach (['artist' => 352, 'album' => 352, 'playlist' => 352, 'track' => 152, 'episode' => 152, 'show' => 352] as $tipo => $altura) {
    $h = rac_embed_artista_html("https://open.spotify.com/$tipo/AbC123xyz?si=zzz");
    ok(strpos($h, "open.spotify.com/embed/$tipo/AbC123xyz?utm_source=generator") !== false && strpos($h, 'height="' . $altura . '"') !== false && strpos($h, 'si=zzz') === false, "Spotify ($tipo): vira o player incorporado, altura $altura, sem rastreador ?si=");
}
ok(strpos(rac_embed_artista_html('https://open.spotify.com/intl-pt/artist/4Z8W4fKeB5Y'), '/embed/artist/4Z8W4fKeB5Y') !== false, 'Spotify: link com "intl-pt" (idioma)');
ok(strpos(rac_embed_artista_html('https://youtube.com/watch?v=abc'), 'oembed-teste') !== false && rac_embed_artista_html('') === '', 'outras plataformas usam o oEmbed do WordPress; vazio = nada');
ok(strpos(rac_embed_artista_html('https://open.spotify.com/artist/x"><script>'), '<script>') === false, 'link malicioso não injeta HTML');

/* ---- título automático da música ---- */
ok(rac_titulo_musica(604) === 'Descoberta Musical: Fulano Jhonson - Irlanda - Blues', 'título automático: "Descoberta Musical: nome - país - estilo"');
$GLOBALS['fields'][700] = ['formato' => 'musica', 'artista_nome' => 'Só Nome'];
ok(rac_titulo_musica(700) === 'Descoberta Musical: Só Nome', 'título automático com campos faltando: só o que existe, sem traços sobrando');
$GLOBALS['fields'][701] = ['formato' => 'musica'];
ok(rac_titulo_musica(701) === '', 'sem nenhum campo: título vazio (não troca o título)');
// salvamento: troca título e endereço
$GLOBALS['P'][702] = ['t' => 'Rascunho automático', 'a' => 1, 'c' => [], 'g' => [], 'd' => '2026-10-01 10:00:00', 'thumb' => '', 'cap' => '', 'ex' => '', 'html' => '', 'f' => [], 'status' => 'draft', 'slug' => ''];
$GLOBALS['fields'][702] = ['formato' => 'musica', 'artista_nome' => 'Fulano Jhonson', 'artista_pais' => 'ie', 'artista_genero' => 'Blues', 'artista_titulo_auto' => 1];
$GLOBALS['atualizado'] = [];
rac_aplicar_titulo_automatico(702);
ok(count($GLOBALS['atualizado']) === 1 && $GLOBALS['atualizado'][0]['post_title'] === 'Descoberta Musical: Fulano Jhonson - Irlanda - Blues' && $GLOBALS['atualizado'][0]['post_name'] === 'descoberta-musical-fulano-jhonson-irlanda-blues', 'ao salvar: grava o título e o endereço automáticos');
ok(has_action('acf/save_post', 'rac_aplicar_titulo_automatico'), 'o gancho foi recolocado depois de salvar (sem laço infinito)');
$GLOBALS['atualizado'] = []; $GLOBALS['fields'][702]['artista_titulo_auto'] = 0;
rac_aplicar_titulo_automatico(702);
ok(!$GLOBALS['atualizado'], 'com a caixa "gerar título automático" desmarcada, não mexe no título');
$GLOBALS['fields'][702]['artista_titulo_auto'] = 1; $GLOBALS['fields'][702]['formato'] = 'texto';
rac_aplicar_titulo_automatico(702);
ok(!$GLOBALS['atualizado'], 'em post de outro formato, não mexe no título');
$GLOBALS['fields'][702]['formato'] = 'musica'; $GLOBALS['P'][702]['status'] = 'publish'; $GLOBALS['P'][702]['slug'] = 'meu-endereco-manual';
rac_aplicar_titulo_automatico(702);
ok(isset($GLOBALS['atualizado'][0]) && !isset($GLOBALS['atualizado'][0]['post_name']) && $GLOBALS['atualizado'][0]['post_title'] !== '', 'post publicado com endereço editado à mão: troca o título mas preserva o endereço');
ok(tenta(fn() => rac_aplicar_titulo_automatico('user_5')) === null, 'ID que não é de post (ex.: "user_5") é ignorado');

/* ---- tempo de leitura ---- */
$GLOBALS['P'][703] = ['t' => 'L', 'a' => 1, 'c' => [], 'g' => [], 'd' => '2026-10-01 10:00:00', 'thumb' => '', 'cap' => '', 'ex' => '', 'html' => '<p>' . str_repeat('palavra ', 450) . '</p>', 'f' => []];
$GLOBALS['P'][704] = ['t' => 'C', 'a' => 1, 'c' => [], 'g' => [], 'd' => '2026-10-01 10:00:00', 'thumb' => '', 'cap' => '', 'ex' => '', 'html' => '', 'f' => []];
ok(rac_tempo_leitura(703) === '3 min de leitura' && rac_tempo_leitura(704) === '1 min de leitura', 'tempo de leitura: 200 palavras por minuto, mínimo de 1 min (450 palavras = 3 min)');

/* ---- tirinhas ---- */
ok(rac_tirinha_numero(606) === 1 && rac_tirinha_numero(605) === 2, 'número da tirinha: posição entre as publicadas');
$GLOBALS['fields'][605]['tirinha_numero'] = 42;
ok(rac_tirinha_numero(605) === 42, 'número manual (campo) tem prioridade');
$GLOBALS['fields'][605]['tirinha_numero'] = '';
ok(rac_tirinha_vizinha(605, 'anterior') === 606 && rac_tirinha_vizinha(605, 'proxima') === 0 && rac_tirinha_vizinha(606, 'proxima') === 605 && rac_tirinha_vizinha(606, 'anterior') === 0, 'tirinha anterior/próxima (0 quando não há)');

/* ---- relacionados ---- */
$r = rac_posts_relacionados(601, 3);
ok(count($r) === 3 && !in_array(601, $r) && $r[0] === 602 && $r[1] === 604, 'relacionados: tags em comum primeiro (602 e 604), sem o próprio post: ' . implode(',', $r));
$r = rac_posts_relacionados(608, 3);
ok(count($r) === 3 && !in_array(608, $r) && count(array_unique($r)) === 3, 'relacionados de um post sem tags: completa por categoria e depois pelos mais recentes, sem repetir');
ok(!in_array(615, rac_posts_relacionados(601, 20)), 'relacionados nunca trazem rascunho');

/* ---- compartilhar ---- */
$l = rac_links_compartilhar(601);
ok($l['whatsapp'] === 'https://wa.me/?text=' . rawurlencode('O jazz que ecoa nos becos do centro /f4/post-601/') && $l['x'] === 'https://twitter.com/intent/tweet?text=' . rawurlencode('O jazz que ecoa nos becos do centro') . '&url=' . rawurlencode('/f4/post-601/') && $l['url'] === '/f4/post-601/', 'links de compartilhar corretamente codificados (sem chave de API)');
$GLOBALS['P'][601]['t'] = 'Título & "aspas" <b>negrito</b> ç';
$l = rac_links_compartilhar(601);
ok(strpos($l['titulo'], '<b>') === false && strpos($l['whatsapp'], '%26') !== false && strpos($l['whatsapp'], ' ') === false, 'título com &, aspas e HTML: tags removidas e caracteres codificados');
$GLOBALS['P'][601]['t'] = 'O jazz que ecoa nos becos do centro';

/* ---- listagem do blog ---- */
$_GET = [];
ok(rac_param_listagem() === ['categoria' => '', 'autor' => '', 'ordem' => 'recentes'], 'filtros padrão');
$_GET = ['ordem' => 'titulo', 'categoria' => 'Poesia!!', 'autor' => 'Marina Alves'];
ok(rac_param_listagem() === ['categoria' => 'poesia', 'autor' => 'marina-alves', 'ordem' => 'titulo'], 'filtros da URL são saneados (slug)');
$_GET = ['ordem' => 'DROP TABLE'];
ok(rac_param_listagem()['ordem'] === 'recentes', 'ordem inválida volta ao padrão');
ok(rac_vars_ordem('antigos') === ['orderby' => 'date', 'order' => 'ASC'] && rac_vars_ordem('titulo')['orderby'] === 'title' && !empty(rac_vars_ordem('autor')['rac_ordem_autor']) && rac_vars_ordem('xyz')['order'] === 'DESC', 'variáveis de consulta de cada ordenação');
ok(rac_rotulo_posts(1) === '1 post' && rac_rotulo_posts(0) === '0 posts' && rac_rotulo_posts(14) === '14 posts', 'rótulo "N posts"');
$_GET = ['autor' => 'igor-bastos', 'ordem' => 'titulo'];
$GLOBALS['ctx'] = 'home';
ok(rac_url_listagem(['categoria' => 'poesia']) === '/f4/blog/?categoria=poesia&autor=igor-bastos&ordem=titulo', 'URL de filtro mantém os outros filtros');
ok(rac_url_listagem(['autor' => '', 'ordem' => 'recentes']) === '/f4/blog/', 'remover filtros deixa a URL limpa');
$f = rac_filtros_ativos();
ok($f['tem'] === true && count($f['etiquetas']) === 1 && $f['etiquetas'][0]['rotulo'] === 'Igor Bastos' && $f['limpar'] === '/f4/blog/', 'filtros ativos: etiqueta do autor e endereço de "Limpar filtros"');
$_GET = ['ordem' => 'titulo'];
ok(rac_filtros_ativos()['tem'] === true && rac_filtros_ativos()['etiquetas'] === [], 'só ordenar já mostra "Limpar filtros", mas não cria etiqueta');
$_GET = [];
$cats = rac_opcoes_categoria();
ok($cats[0]['rotulo'] === 'Todas as categorias' && $cats[0]['ativa'] && count($cats) === 8 && $cats[1]['rotulo'] === 'Crônicas', 'opções de categoria: "Todas" + as que têm posts, A–Z');
$aut = rac_opcoes_autor();
ok(array_column($aut, 'rotulo') === ['Todos', 'Equipe Agenda Cultural', 'Igor Bastos', 'Marina Alves'], 'opções de autor(a)');
ok(rac_valor_ativo($cats) === 'Todas as categorias' && rac_valor_ativo([]) === '', 'valor mostrado no seletor');
$pg = new WP_Query(['post_type' => 'post', 'posts_per_page' => 6, 'paged' => 2]);
$_SERVER['REQUEST_URI'] = '/f4/blog/page/2/?ordem=titulo'; $_SERVER['QUERY_STRING'] = 'ordem=titulo';
$pag = rac_paginacao($pg);
ok($pag['total'] === 3 && $pag['atual'] === 2 && $pag['anterior'] === '/f4/blog/?ordem=titulo' && $pag['proxima'] === '/f4/blog/page/3/?ordem=titulo' && count($pag['paginas']) === 3, 'paginação: 3 páginas, anterior e próxima mantêm a ordem');

/* ---- consulta principal (gancho pre_get_posts) ---- */
$q = new WP_Query(); $q->principal = true; $q->ctx = 'home';
$_GET = ['autor' => 'marina-alves', 'categoria' => 'cronicas', 'ordem' => 'antigos'];
rac_ajustar_consulta($q);
ok($q->get('posts_per_page') === 6 && $q->get('author_name') === 'marina-alves' && $q->get('category_name') === 'cronicas' && $q->get('order') === 'ASC' && $q->get('ignore_sticky_posts') === true, 'pre_get_posts: 6 por página, autor, categoria e ordem aplicados');
$q = new WP_Query(); $q->principal = true; $q->ctx = 'category'; $q->q = ['category_name' => 'poesia'];
rac_ajustar_consulta($q);
ok($q->get('category_name') === 'poesia', 'na página de categoria, o filtro de categoria da URL não sobrescreve a própria categoria');
$q = new WP_Query(); $q->principal = true; $q->ctx = 'author';
$_GET = ['autor' => 'outro', 'ordem' => 'titulo'];
rac_ajustar_consulta($q);
ok($q->get('posts_per_page') === 6 && $q->get('author_name') === '' && $q->get('orderby') === '', 'página do autor: só 6 por página (filtros do blog não se aplicam)');
$q = new WP_Query(); $q->principal = false; $q->ctx = 'home';
rac_ajustar_consulta($q);
ok($q->get('posts_per_page') === '', 'consultas secundárias não são alteradas');
$_GET = [];

/* ---- autoria ---- */
$a = rac_dados_autor(1);
ok($a['nome'] === 'Marina Alves' && $a['primeiro'] === 'Marina' && $a['rotulo'] === 'Autora' && $a['foto_id'] === 55 && $a['avatar_url'] === '' && $a['url'] === '/f4/autor/marina-alves/', 'dados do autor com foto: sem Gravatar, "Autora", endereço /autor/');
$a = rac_dados_autor(2);
ok($a['rotulo'] === 'Autor' && $a['foto_id'] === 0 && $a['avatar_url'] === '/h/foto2.svg' && $a['papel'] === '', 'autor sem foto: usa o Gravatar; sem papel');
ok(rac_paragrafos("Um.\n\nDois.\nTrês.\n\n\n") === ['Um.', 'Dois.', 'Três.'] && rac_paragrafos('') === [], 'bio em parágrafos (linha em branco ou quebra)');
ok(strpos(rac_avatar_html(rac_dados_autor(3), 'x'), '<b aria-hidden="true">E</b>') !== false && strpos(rac_avatar_html(rac_dados_autor(3)), 'onerror="this.remove()"') !== false, 'avatar com Gravatar: a inicial fica atrás e a imagem se remove se falhar');
ok(strpos(rac_avatar_html(['foto_id' => 0, 'avatar_url' => '', 'nome' => 'Zé']), 'rac-avatar--inicial') !== false, 'avatar sem foto nem Gravatar: só a inicial');
ok(rac_primeiro_nome('  Ana  Paula ') === 'Ana' && rac_primeiro_nome('') === '', 'primeiro nome');

/* ---- comentários ---- */
ok(rac_titulo_comentarios(0) === 'Comentários' && rac_titulo_comentarios(1) === '1 comentário' && rac_titulo_comentarios(8) === '8 comentários', 'título dos comentários');
$c1 = (object) ['comment_author' => 'Ana', 'comment_author_email' => 'ana@ex.com'];
ok(rac_cor_avatar($c1) === rac_cor_avatar($c1) && strpos(rac_cor_avatar($c1), 'var(--') === 0, 'cor do avatar é estável e vem de token do tema');
$_GET = []; ok(!rac_comentarios_novos_primeiro(), 'ordem padrão: mais antigos primeiro');
$_GET = ['comentarios' => 'novos']; ok(rac_comentarios_novos_primeiro() && rac_args_comentarios(['order' => 'ASC'])['order'] === 'DESC', '?comentarios=novos inverte a ordem');
$_GET = [];

/* ---- anti-spam: comentários dos posts ---- */
$dados = ['comment_post_ID' => 601, 'comment_content' => 'oi'];
$_POST = [];
ok(tenta(fn() => rac_filtrar_recado($dados)) === null, 'comentário de post SEM o marcador do formulário do tema (ex.: app, Akismet): segue só a moderação');
$_POST = ['rac_form' => '1', 'rac_ms' => '5000', 'rac_site' => ''];
ok(tenta(fn() => rac_filtrar_recado($dados)) === null, 'comentário do formulário do tema (5 s, armadilha vazia): passa');
$_POST = ['rac_form' => '1', 'rac_ms' => '5000', 'rac_site' => 'spam'];
ok(tenta(fn() => rac_filtrar_recado($dados)) !== null, 'armadilha preenchida: bloqueado');
$_POST = ['rac_form' => '1', 'rac_ms' => '900', 'rac_site' => ''];
ok(strpos((string) tenta(fn() => rac_filtrar_recado($dados)), 'Calma') === 0, 'enviado em menos de 3 s: bloqueado ("Calma!…")');
$_POST = ['rac_form' => '1'];
ok(tenta(fn() => rac_filtrar_recado($dados)) !== null, 'formulário do tema sem a medição de tempo: bloqueado');
$_POST = ['rac_ms' => '10', 'rac_site' => ''];
ok(tenta(fn() => rac_filtrar_recado(['comment_post_ID' => 1000, 'comment_content' => 'x'])) !== null, 'os recados da Home continuam protegidos como antes');

/* ---- o código do tema não tem cores soltas nem endereços do ambiente de teste ---- */
$css = file_get_contents(THEME_DIR . '/assets/css/blog.css');
preg_match_all('/#[0-9a-fA-F]{3,6}\b/', $css, $m);
ok(array_diff(array_unique($m[0]), ['#fff', '#111', '#000']) === [], 'blog.css: sem cores fora dos tokens (exceções: preto do vídeo/X e branco das marcas)');
$js = file_get_contents(THEME_DIR . '/assets/js/blog.js') . file_get_contents(THEME_DIR . '/assets/js/compartilhar.js') . file_get_contents(THEME_DIR . '/assets/js/comentarios.js');
ok(strpos($js, 'localhost') === false && strpos($js, '/f4/') === false, 'JS do tema sem endereços do ambiente de teste');

echo "\nFALHAS: $falhas\n";
exit($falhas ? 1 : 0);
