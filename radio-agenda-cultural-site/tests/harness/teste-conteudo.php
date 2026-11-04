<?php
// Teste em PHP puro do conteúdo da 3B-2: filtro anti-spam dos recados, tempo relativo,
// livros e séries de podcast. Roda sem navegador: php teste-conteudo.php
require __DIR__ . '/wpstubs.php';
require PLUGIN_DIR . '/racr-player-radio.php';
require THEME_DIR . '/inc/helpers.php';
require THEME_DIR . '/inc/conteudo.php';
require THEME_DIR . '/inc/recados.php';

$falhas = 0;
function ok($c, $m) { global $falhas; echo ($c ? 'OK   ' : 'FALHA') . ' ' . $m . "\n"; if (!$c) $falhas++; }
function tenta($fn) { try { $fn(); return null; } catch (Exception $e) { return $e->getMessage(); } }

/* ---- filtro anti-spam dos recados (comentários na página inicial) ---- */
$dados = ['comment_post_ID' => 1000, 'comment_content' => 'oi'];
$_POST = ['rac_ms' => '5200', 'rac_site' => ''];
ok(tenta(fn() => rac_filtrar_recado($dados)) === null, 'recado normal (5,2 s, armadilha vazia) passa');
$_POST = ['rac_ms' => '5200', 'rac_site' => 'http://spam.com'];
ok(strpos((string) tenta(fn() => rac_filtrar_recado($dados)), 'Não foi possível') === 0, 'armadilha preenchida: bloqueado');
$_POST = ['rac_ms' => '800', 'rac_site' => ''];
ok(strpos((string) tenta(fn() => rac_filtrar_recado($dados)), 'Calma') === 0, 'enviado em menos de 3 s: bloqueado');
$_POST = ['rac_site' => ''];
ok(tenta(fn() => rac_filtrar_recado($dados)) !== null, 'sem a medição de tempo do navegador (envio direto): bloqueado');
$_POST = ['rac_ms' => '3000'];
ok(tenta(fn() => rac_filtrar_recado($dados)) === null, 'exatamente 3 s: passa');
$_POST = [];
ok(tenta(fn() => rac_filtrar_recado(['comment_post_ID' => 501, 'comment_content' => 'comentário num post'])) === null, 'comentário em OUTRA página (post do blog) não é afetado');

/* ---- textos dos recados ---- */
ok(rac_rotulo_recados(1) === '1 recado' && rac_rotulo_recados(7) === '7 recados', 'rótulo "1 recado" / "N recados"');
ok(rac_inicial('maria') === 'M' && rac_inicial('  élio') === 'É' && rac_inicial('') === 'A', 'inicial do avatar (acentuada, vazia → A)');
$recentes = _com('x', 'y', 2); $antigo = _com('x', 'y', 24 * 20);
ok(rac_tempo_relativo($recentes) === 'há 2 horas', 'recado de 2 horas atrás → "' . rac_tempo_relativo($recentes) . '"');
$esperada = wp_date('d M Y', time() - 24 * 20 * 3600);
ok(rac_tempo_relativo($antigo) === $esperada && preg_match('/^\d{2} [a-z]{3} \d{4}$/', $esperada) === 1, 'recado de 20 dias → data curta da época do recado (' . rac_tempo_relativo($antigo) . ')');
$limite = _com('x', 'y', 24 * 6); ok(strpos(rac_tempo_relativo($limite), 'há ') === 0, 'com 6 dias ainda é relativo');
ok(count(rac_recados(1000)) === 7, 'lista os recados aprovados da página');

/* ---- posts e livros ---- */
ok(rac_posts_recentes(6) === [501, 502, 503], 'posts recentes (3 no total de teste)');
ok(count(rac_livros_recentes(8)) === 8 && rac_livros_recentes(3) === [701, 702, 703], 'livros recentes respeitam o limite');
$bl = rac_livros_para_estacao('Blues & Jazz Clube', 6);
ok(array_slice($bl, 0, 2) === [703, 705] && count($bl) === 6 && count(array_unique($bl)) === 6, 'livros da estação: os da categoria de mesmo nome primeiro, completando com os recentes, sem repetir (' . implode(',', $bl) . ')');
ok(rac_livros_para_estacao('Poesia Falada', 6) === [701, 702, 703, 704, 705, 706], 'estação sem categoria de livro: os 6 mais recentes');
$GLOBALS['livros_db'] = [];
ok(rac_livros_para_estacao('Poesia Falada', 6) === [] && rac_livros_recentes() === [], 'sem livros cadastrados: lista vazia (a seção some)');

/* ---- podcasts ---- */
ok(rac_series_podcast() === [], 'sem a categoria Podcasts / sem subcategorias: nenhuma série (a seção some)');
$GLOBALS['com_podcasts'] = true;
$s = rac_series_podcast();
ok(count($s) === 2 && $s[0]['nome'] === 'Bastidores da Cena' && $s[0]['episodios'] === 32 && $s[0]['descricao'] === 'Entrevistas com artistas e curadores locais.' && $s[1]['descricao'] === '' && strpos($s[0]['url'], '/category/podcasts/') === 0, 'séries: nome, descrição, episódios e link');

echo "\nFALHAS: $falhas\n";
exit($falhas ? 1 : 0);
