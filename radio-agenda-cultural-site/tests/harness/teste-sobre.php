<?php
// Teste em PHP puro da lógica da página Sobre (Fase 5A): quem aparece em "Quem faz" e em que ordem,
// itens de cada pessoa, textos padrão e campos ACF. Roda sem navegador: php teste-sobre.php
$_SERVER['REQUEST_URI'] = '/f4/sobre/';
require __DIR__ . '/wpstubs4.php';
require THEME_DIR . '/inc/helpers.php';
require THEME_DIR . '/inc/home.php';
require THEME_DIR . '/inc/recados.php';
require THEME_DIR . '/inc/autor.php';
require THEME_DIR . '/inc/blog.php';
require THEME_DIR . '/inc/sobre.php';

$falhas = 0;
function ok($c, $m) { global $falhas; echo ($c ? 'OK   ' : 'FALHA') . ' ' . $m . "\n"; if (!$c) $falhas++; }

/* ---- quem aparece e em que ordem ---- */
ok(rac_autores_sobre() === [5, 1, 2, 6], 'ordem: posição 1, 2, 3 e, sem posição, por último (Fundador, Marina, Igor, Joana)');
$GLOBALS['usuarios'][1]['meta']['autor_ordem_sobre'] = '3'; // empate com o Igor (3): desempata pelo nome (Igor antes de Marina)
ok(rac_autores_sobre() === [5, 2, 1, 6], 'empate na posição: ordem alfabética pelo nome');
$GLOBALS['usuarios'][1]['meta']['autor_ordem_sobre'] = '2';
$GLOBALS['usuarios'][3]['meta']['autor_no_sobre'] = '1';
ok(in_array(3, rac_autores_sobre(), true), 'basta marcar o usuário para ele entrar na lista');
unset($GLOBALS['usuarios'][3]['meta']['autor_no_sobre']);
$GLOBALS['usuarios'][1]['meta']['autor_no_sobre'] = '0';
ok(!in_array(1, rac_autores_sobre(), true), 'desmarcar tira a pessoa da lista');
$GLOBALS['usuarios'][1]['meta']['autor_no_sobre'] = '1';
foreach ($GLOBALS['usuarios'] as $i => $u) unset($GLOBALS['usuarios'][$i]['meta']['autor_no_sobre']);
ok(rac_autores_sobre() === [], 'ninguém marcado: lista vazia (a seção some)');
$GLOBALS['usuarios'][5]['meta']['autor_no_sobre'] = '1'; $GLOBALS['usuarios'][1]['meta']['autor_no_sobre'] = '1'; $GLOBALS['usuarios'][2]['meta']['autor_no_sobre'] = '1'; $GLOBALS['usuarios'][6]['meta']['autor_no_sobre'] = '1';

/* ---- itens de cada pessoa ---- */
$i1 = rac_itens_autor_sobre(1);
ok(count($i1) === 3 && $i1[0]['tipo'] === 'Livro' && $i1[0]['titulo'] === 'Cidade em voz baixa' && $i1[1]['tipo'] === 'Artigo' && $i1[2]['tipo'] === 'Artigo', 'Marina: o livro mais recente primeiro e os textos mais novos para completar 3');
ok($i1[0]['url'] === 'http://localhost:8099/literatura/' && $i1[1]['url'] === '/f4/post-601/', 'livro leva à biblioteca (não há página de livro); texto leva ao post');
ok(strpos($i1[0]['thumb'], 'capa-1.svg') !== false && $i1[2]['thumb'] === '' || strpos($i1[2]['thumb'], '<img') !== false, 'miniatura: a capa do livro e a imagem destacada do texto (vazia quando não há)');
ok(count(rac_itens_autor_sobre(5)) === 1 && rac_itens_autor_sobre(5)[0]['titulo'] === 'Crônicas de uma vitrola', 'quem tem só 1 livro e nenhum texto: 1 item');
$i2 = rac_itens_autor_sobre(2);
ok(count($i2) === 3 && array_column($i2, 'tipo') === ['Artigo', 'Artigo', 'Artigo'] && $i2[0]['titulo'] === 'Conversa com o luthier da Rua Seis', 'quem não tem livro: 3 textos, do mais novo ao mais antigo');
ok(rac_itens_autor_sobre(6) === [], 'quem não publicou nada: nenhum item');
ok(count(rac_itens_autor_sobre(2, 2)) === 2, 'o máximo de itens pode ser trocado');
$GLOBALS['P'][603]['status'] = 'draft';
ok(rac_itens_autor_sobre(2)[0]['titulo'] !== 'Conversa com o luthier da Rua Seis', 'rascunhos não aparecem');
unset($GLOBALS['P'][603]['status']);

/* ---- foto ---- */
$f = rac_foto_autor_html(rac_dados_autor(1));
ok(strpos($f, 'capa-1.svg') !== false, 'foto do perfil quando existe');
$f = rac_foto_autor_html(rac_dados_autor(3));
ok(strpos($f, '<b aria-hidden="true">E</b>') !== false && strpos($f, 'onerror="this.remove()"') !== false, 'Gravatar com a inicial atrás (some se a imagem falhar)');
$f = rac_foto_autor_html(rac_dados_autor(6));
ok($f === '<b aria-hidden="true">J</b>', 'sem foto nem Gravatar: só a inicial');

/* ---- textos padrão ---- */
$GLOBALS['fields'][3000] = [];
ok(rac_sobre_texto('sobre_titulo', 3000) === 'Uma rádio que nasceu de uma coleção de discos', 'campo vazio mostra o texto provisório');
$GLOBALS['fields'][3000] = ['sobre_titulo' => 'Meu título'];
ok(rac_sobre_texto('sobre_titulo', 3000) === 'Meu título', 'campo preenchido substitui o provisório');
ok(substr_count(rac_sobre_texto('sobre_historia_texto', 3000), '<p>') === 3 && substr_count(rac_sobre_texto('sobre_historia_texto_2', 3000), '<p>') === 1, 'história provisória: 3 parágrafos no texto principal e 1 na continuação');

/* ---- blocos da história ---- */
$GLOBALS['fields'][3000] = [];
$h = rac_sobre_historia(3000);
ok(strpos($h['abertura'], 'Tudo começou') === 0 && substr_count($h['texto1'], '<p>') === 2 && substr_count($h['texto2'], '<p>') === 1 && $h['img1'] === 0 && $h['img2'] === 0, 'tudo vazio: abertura + 2 parágrafos (bloco 1) + continuação (bloco 2), sem imagens');
$GLOBALS['fields'][3000] = ['sobre_historia_texto' => '<p>Minha abertura.</p><p>Meu corpo.</p>'];
$h = rac_sobre_historia(3000);
ok($h['abertura'] === 'Minha abertura.' && $h['texto1'] === '<p>Meu corpo.</p>' && $h['texto2'] === '', 'texto principal preenchido: a continuação NÃO usa o parágrafo provisório');
$GLOBALS['fields'][3000] = ['sobre_historia_texto' => '<p class="x">Só a abertura.</p>'];
$h = rac_sobre_historia(3000);
ok($h['abertura'] === 'Só a abertura.' && $h['texto1'] === '', 'texto com um parágrafo só (mesmo com atributos): vira só a abertura, sem bloco 1');
$GLOBALS['fields'][3000] = ['sobre_historia_texto' => '<p>A.</p><p>B.</p>', 'sobre_historia_texto_2' => '<p>Continua.</p>', 'sobre_imagem_1' => 56, 'sobre_imagem_1_legenda' => '  Legenda 1  ', 'sobre_imagem_2' => 55, 'sobre_imagem_2_legenda' => 'Legenda 2'];
$h = rac_sobre_historia(3000);
ok($h['texto2'] === '<p>Continua.</p>' && $h['img1'] === 56 && $h['img2'] === 55 && $h['leg1'] === 'Legenda 1' && $h['leg2'] === 'Legenda 2', 'continuação, imagens e legendas (com espaços aparados)');
$GLOBALS['fields'][3000] = ['sobre_historia_texto_2' => '<p>Só continuação.</p>'];
$h = rac_sobre_historia(3000);
ok(strpos($h['abertura'], 'Tudo começou') === 0 && $h['texto2'] === '<p>Só continuação.</p>', 'principal vazio e continuação própria: o principal é o provisório e a continuação é a sua');
$GLOBALS['fields'][3000] = [];
ok(rac_sobre_texto('campo_que_nao_existe', 3000) === '', 'campo desconhecido: vazio');

/* ---- destaques (iguais aos da Home) ---- */
$d = rac_destaques_home();
ok(count($d) === 3 && $d[0]['titulo'] === '12 estações no ar, 24h por dia' && $d[1]['kicker'] === 'Literatura' && $d[2]['texto'] !== '', 'destaques: Estações (com o número real), Literatura e Blog');
ok($d[0]['url'] === 'http://localhost:8099/estacoes/' && $d[0]['link'] === 'Veja todas as estações' && $d[1]['url'] === 'http://localhost:8099/literatura/' && $d[1]['link'] === 'Veja todos os livros' && $d[2]['url'] === '/f4/blog/' && $d[2]['link'] === 'Veja todas as publicações', 'cada destaque tem o seu link (Estações, Literatura e Blog)');
$GLOBALS['fields'][1000] = ['home_destaque2_titulo' => 'Biblioteca aberta'];
ok(rac_destaques_home()[1]['titulo'] === 'Biblioteca aberta', 'os destaques seguem o que foi editado na página inicial');

/* ---- campos ACF ---- */
do_action('acf/init');
$grupos = []; foreach ($GLOBALS['acf_groups'] as $g) $grupos[$g['key']] = $g;
ok(isset($grupos['group_rac_pagina_sobre']) && $grupos['group_rac_pagina_sobre']['location'][0][0]['value'] === 'page-sobre.php', 'grupo "Textos da página Sobre" ligado ao modelo page-sobre.php');
$nomes = array_column($grupos['group_rac_pagina_sobre']['fields'], 'name');
foreach (['sobre_selo', 'sobre_titulo', 'sobre_texto', 'sobre_historia_rotulo', 'sobre_historia_titulo', 'sobre_historia_texto', 'sobre_imagem_1', 'sobre_imagem_1_legenda', 'sobre_historia_texto_2', 'sobre_imagem_2', 'sobre_imagem_2_legenda', 'sobre_equipe_titulo', 'sobre_cta_titulo', 'sobre_cta_texto'] as $n) ok(in_array($n, $nomes, true), "campo da página: $n");
$nomesU = array_column($grupos['group_rac_autor']['fields'], 'name');
ok(in_array('autor_no_sobre', $nomesU, true) && in_array('autor_ordem_sobre', $nomesU, true), 'campos do perfil: "Aparecer na página Sobre" e "Posição na página Sobre"');
$chaves = []; foreach ($GLOBALS['acf_groups'] as $g) foreach ($g['fields'] as $c) $chaves[] = $c['key'];
ok(count($chaves) === count(array_unique($chaves)), 'nenhuma chave de campo repetida');
$abas = array_values(array_filter($grupos['group_rac_pagina_sobre']['fields'], fn($c) => $c['type'] === 'tab'));
ok(count($abas) === 3 && array_column($abas, 'label') === ['Topo', 'Nossa história', 'Quem faz e convite'], 'campos da página organizados em 3 abas: Topo, Nossa história e Quem faz e convite');
$imagens = array_values(array_filter($grupos['group_rac_pagina_sobre']['fields'], fn($c) => $c['type'] === 'image'));
ok(count($imagens) === 2 && $imagens[0]['return_format'] === 'id' && strpos($imagens[0]['instructions'], '4:5') !== false && strpos($imagens[1]['instructions'], '3:2') !== false, 'dois campos de imagem (retorno por ID) com a orientação e as proporções indicadas');

ok(is_file(THEME_DIR . '/page-sobre.php') && is_file(THEME_DIR . '/assets/css/sobre.css'), 'modelo e CSS da página existem');
$css = file_get_contents(THEME_DIR . '/assets/css/sobre.css');
preg_match_all('/#[0-9a-fA-F]{3,6}\b/', $css, $m);
ok($m[0] === [], 'sobre.css: nenhuma cor fora dos tokens');

echo "\nFALHAS: $falhas\n";
exit($falhas ? 1 : 0);
