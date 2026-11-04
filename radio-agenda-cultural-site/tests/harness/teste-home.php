<?php
// Teste em PHP puro da lógica da página inicial (mais ouvidas, descoberta do editor,
// agendamento do cache). Roda sem navegador: php teste-home.php
require __DIR__ . '/wpstubs.php';
require PLUGIN_DIR . '/racr-player-radio.php';
require THEME_DIR . '/inc/helpers.php';
require THEME_DIR . '/inc/acf-campos.php';
require THEME_DIR . '/inc/home.php';

$falhas = 0;
function ok($c, $m) { global $falhas; echo ($c ? 'OK   ' : 'FALHA') . ' ' . $m . "\n"; if (!$c) $falhas++; }
$ids = fn($l) => array_map(fn($e) => (int) $e['id'], $l);
function limpar_ouvintes() { foreach ($GLOBALS['estacoes_posts'] as $p) unset($GLOBALS['fields'][$p->ID]['ouvintes']); }

/* ---- mais ouvidas ---- */
$top = rac_estacoes_mais_ouvidas(2);
ok($ids($top) === [101, 108], 'com números: as duas com mais ouvintes (Bossa 1240 e MPB 1120) → ' . implode(',', $ids($top)));
ok($top[0]['ouvintes'] === 1240, 'a mais ouvida vem primeiro');

limpar_ouvintes();
ok($ids(rac_estacoes_mais_ouvidas(2)) === [100, 101], 'sem nenhum número: segue a ordem do plugin (ordem de exibição, depois alfabética)');

limpar_ouvintes();
$GLOBALS['fields'][104]['ouvintes'] = 500; $GLOBALS['fields'][103]['ouvintes'] = 500; $GLOBALS['fields'][102]['ouvintes'] = 500;
ok($ids(rac_estacoes_mais_ouvidas(2)) === [102, 103], 'empate: desempata pela ordem do plugin');

limpar_ouvintes();
$GLOBALS['fields'][110]['ouvintes'] = 'abc'; $GLOBALS['fields'][111]['ouvintes'] = -5; $GLOBALS['fields'][105]['ouvintes'] = '7';
ok($ids(rac_estacoes_mais_ouvidas(1)) === [105], 'valores estranhos: texto vira 0, negativo vira 0, "7" vira 7');

/* ---- descoberta ---- */
limpar_ouvintes();
foreach ([100=>980,101=>1240,102=>610,103=>420,104=>870,105=>310,106=>290,107=>540,108=>1120,109=>760,110=>690,111=>450] as $i=>$n) $GLOBALS['fields'][$i]['ouvintes'] = $n;
$topIds = $ids(rac_estacoes_mais_ouvidas(2));
$GLOBALS['fields'][1000] = [];

$escolhas = []; $naoNoTop = true; $estavel = true;
for ($d = 0; $d < 366; $d++) {
    $dia = (new DateTime('2026-01-01'))->modify("+$d day")->format('Y-m-d');
    $e = rac_estacao_descoberta($topIds, 1000, $dia);
    $e2 = rac_estacao_descoberta($topIds, 1000, $dia);
    if (in_array((int) $e['id'], $topIds, true)) $naoNoTop = false;
    if ($e['id'] !== $e2['id']) $estavel = false;
    $escolhas[] = (int) $e['id'];
}
ok($naoNoTop, 'sorteio: nunca escolhe uma das duas mais ouvidas (366 dias)');
ok($estavel, 'sorteio: o mesmo dia sempre dá a mesma estação');
$repeticoes = 0; for ($i = 1; $i < count($escolhas); $i++) if ($escolhas[$i] === $escolhas[$i - 1]) $repeticoes++;
ok($repeticoes <= 40, "sorteio: repetições em dias seguidos só nas viradas de ciclo ($repeticoes em 365 trocas; pool de 10)");
$pool = 10; $dias0 = (int) floor((new DateTime('2026-01-01', new DateTimeZone('UTC')))->getTimestamp() / 86400);
$inicio = (intdiv($dias0, $pool) + 1) * $pool;               // primeiro dia de um ciclo completo
$ciclo = []; for ($k = 0; $k < $pool; $k++) { $dia = (new DateTime('@' . (($inicio + $k) * 86400)))->format('Y-m-d'); $ciclo[] = (int) rac_estacao_descoberta($topIds, 1000, $dia)['id']; }
ok(count(array_unique($ciclo)) === $pool, 'sorteio: num ciclo de 10 dias passa por todas as 10 estações do pool, sem repetir');
ok(count(array_unique($escolhas)) === $pool, 'sorteio: em um ano, todas as estações do pool aparecem');

$GLOBALS['fields'][1000] = ['home_descoberta_modo' => 'fixar', 'home_descoberta_estacao' => 105];
ok((int) rac_estacao_descoberta($topIds, 1000, '2026-11-04')['id'] === 105 && (int) rac_estacao_descoberta($topIds, 1000, '2027-03-09')['id'] === 105, 'fixar: sempre a estação escolhida, qualquer dia');
$GLOBALS['fields'][1000] = ['home_descoberta_modo' => 'fixar', 'home_descoberta_estacao' => 101];
$r = rac_estacao_descoberta($topIds, 1000, '2026-11-04');
ok(!in_array((int) $r['id'], $topIds, true), 'fixar uma das mais ouvidas: cai no sorteio (sem repetir o destaque)');
$GLOBALS['fields'][1000] = ['home_descoberta_modo' => 'fixar', 'home_descoberta_estacao' => 0];
ok(rac_estacao_descoberta($topIds, 1000, '2026-11-04') !== null, 'fixar sem escolher estação: cai no sorteio');
$GLOBALS['fields'][1000] = ['home_descoberta_modo' => 'fixar', 'home_descoberta_estacao' => 9999];
ok(rac_estacao_descoberta($topIds, 1000, '2026-11-04') !== null, 'fixar uma estação que não existe/sem stream: cai no sorteio');
ok(rac_estacao_descoberta($ids(rac_listar_estacoes()), 1000, '2026-11-04') === null, 'sem estação fora do top: devolve null (a seção some)');

/* ---- textos padrão e cache ---- */
$GLOBALS['fields'][1000] = [];
ok(rac_home_texto('home_hero_titulo', 1000) === 'Música, cultura e boas conversas, 24 horas por dia', 'campo vazio: texto padrão provisório');
$GLOBALS['fields'][1000] = ['home_hero_titulo' => 'Outro título'];
ok(rac_home_texto('home_hero_titulo', 1000) === 'Outro título', 'campo preenchido: usa o texto do painel');

$purgas = []; add_action('litespeed_purge_url', function ($u) use (&$purgas) { $purgas[] = $u; });
do_action('rac_limpar_cache_home');
ok($purgas === ['http://localhost:8099/'], 'limpeza diária pede ao LiteSpeed para limpar só a página inicial');
$purgas = []; do_action('save_post_estacao');
ok(count($purgas) === 1, 'salvar uma estação também limpa a Home');
rac_agendar_limpeza_diaria();
[$ts, $rec, $hook] = $GLOBALS['agenda_args'];
$h = (new DateTime('@' . $ts))->setTimezone(wp_timezone());
ok($rec === 'daily' && $hook === 'rac_limpar_cache_home' && $h->format('H:i') === '00:05' && $ts > time(), "evento agendado todo dia às 00:05 no fuso do site (próximo: {$h->format('Y-m-d H:i')})");
$antes = $GLOBALS['agenda_args']; rac_agendar_limpeza_diaria();
ok($GLOBALS['agenda_args'] === $antes, 'não agenda duas vezes');
rac_desagendar_limpeza();
ok(!wp_next_scheduled('rac_limpar_cache_home'), 'ao trocar de tema o agendamento é removido');

echo "\nFALHAS: $falhas\n";
exit($falhas ? 1 : 0);
