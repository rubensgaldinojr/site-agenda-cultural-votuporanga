<?php
// Teste em PHP puro da lógica do Contato (Fase 5B): validações, anti-spam, limites, destino do e-mail,
// cabeçalhos, mensagens no painel e campos ACF. Roda sem navegador: php teste-contato.php
$tmp = sys_get_temp_dir() . '/rac-contato-' . getmypid(); @mkdir($tmp);
putenv('RAC_HARNESS_TMP=' . $tmp);
$_SERVER['REQUEST_URI'] = '/f4/contato/';
require __DIR__ . '/wpstubs4.php';
require THEME_DIR . '/inc/helpers.php';
require THEME_DIR . '/inc/contato.php';

$falhas = 0;
function ok($c, $m) { global $falhas; echo ($c ? 'OK   ' : 'FALHA') . ' ' . $m . "\n"; if (!$c) $falhas++; }
function zerar() { global $tmp; foreach (glob("$tmp/contato-*") as $f) @unlink($f); }
function valido($mudar = array()) {
    return array_merge( array( 'rac_ms' => '5000', 'assunto' => 'outro', 'nome' => 'Ana Paula', 'email' => 'ana@exemplo.com', 'mensagem' => 'Adoro a rádio, parabéns!', 'pagina' => '4000' ), $mudar );
}
function msgs() { return _ler('contato-msgs.json'); }
function emails() { return _ler('contato-emails.json'); }

/* ---- assuntos ---- */
ok(array_keys(rac_contato_assuntos()) === ['sugestao-de-musica', 'pedido-musical', 'parcerias', 'outro'], '4 assuntos, na ordem do mockup');
ok(rac_contato_assunto_musical('sugestao-de-musica') && rac_contato_assunto_musical('pedido-musical') && !rac_contato_assunto_musical('parcerias') && !rac_contato_assunto_musical('outro'), 'só sugestão e pedido pedem Música e Artista');

/* ---- validações (cada erro, uma mensagem clara; nada é salvo) ---- */
zerar();
$casos = array(
    'armadilha preenchida'          => array( valido( array( 'rac_site' => 'robo' ) ), 'Não foi possível enviar a mensagem.' ),
    'menos de 3 s'                  => array( valido( array( 'rac_ms' => '2999' ) ), 'Calma' ),
    'sem medição de tempo'          => array( array_diff_key( valido(), array( 'rac_ms' => 1 ) ), 'Calma' ),
    'assunto inexistente'           => array( valido( array( 'assunto' => 'hackear' ) ), 'Escolha um assunto' ),
    'sem assunto'                   => array( array_diff_key( valido(), array( 'assunto' => 1 ) ), 'Escolha um assunto' ),
    'nome vazio'                    => array( valido( array( 'nome' => '   ' ) ), 'Informe seu nome' ),
    'nome com mais de 100 letras'   => array( valido( array( 'nome' => str_repeat( 'a', 101 ) ) ), 'Informe seu nome' ),
    'e-mail inválido'               => array( valido( array( 'email' => 'sem-arroba' ) ), 'e-mail válido' ),
    'sem e-mail'                    => array( array_diff_key( valido(), array( 'email' => 1 ) ), 'e-mail válido' ),
    'sugestão sem a música'         => array( valido( array( 'assunto' => 'sugestao-de-musica' ) ), 'qual música' ),
    'pedido com música em branco'   => array( valido( array( 'assunto' => 'pedido-musical', 'musica' => ' ' ) ), 'qual música' ),
    'música longa demais'           => array( valido( array( 'assunto' => 'pedido-musical', 'musica' => str_repeat( 'm', 151 ) ) ), '150 letras' ),
    'mensagem curta'                => array( valido( array( 'mensagem' => 'oi' ) ), 'Escreva sua mensagem' ),
    'mensagem de só tags'           => array( valido( array( 'mensagem' => '<b></b> <i></i>' ) ), 'Escreva sua mensagem' ),
    'mensagem gigante'              => array( valido( array( 'mensagem' => str_repeat( 'x', 3001 ) ) ), '3000 letras' ),
);
foreach ( $casos as $nome => $c ) {
    $r = rac_contato_processar( $c[0], '1.1.1.1' );
    ok( ! $r['ok'] && strpos( $r['erro'], $c[1] ) !== false, "recusa: $nome → \"" . ( $r['erro'] ?? '' ) . '"' );
}
ok(msgs() === [] && emails() === [], 'nenhuma recusa salvou mensagem nem mandou e-mail');
ok(_ler('contato-transients.json') === [], 'recusas por validação não gastam o limite de envios da pessoa');

/* ---- envio correto: salva, manda e-mail ---- */
zerar();
$r = rac_contato_processar( valido( array( 'assunto' => 'pedido-musical', 'musica' => 'Save My Child', 'artista' => 'Willie Dixon', 'mensagem' => "Primeira linha\nSegunda linha" ) ), '2.2.2.2' );
ok($r['ok'] && $r['id'] === 9001 && $r['email_enviado'] === true, 'envio correto: salvo (nº 9001) e e-mail enviado');
$m = msgs()[9001];
ok($m['tipo'] === 'mensagem_contato' && $m['status'] === 'publish' && $m['titulo'] === 'Pedido musical — Ana Paula' && $m['conteudo'] === "Primeira linha\nSegunda linha", 'mensagem salva: tipo próprio, título "Assunto — Nome" e o texto com as quebras de linha');
ok($m['meta']['contato_assunto'] === 'Pedido musical' && $m['meta']['contato_nome'] === 'Ana Paula' && $m['meta']['contato_email'] === 'ana@exemplo.com' && $m['meta']['contato_musica'] === 'Save My Child' && $m['meta']['contato_artista'] === 'Willie Dixon' && $m['meta']['contato_email_enviado'] === '1', 'dados guardados à parte (assunto, nome, e-mail, música, artista, e-mail enviado)');
$e = emails()[0];
ok($e['assunto'] === '[Contato do site] Pedido musical — Ana Paula', 'assunto do e-mail: "[Contato do site] Assunto — Nome"');
ok(strpos($e['corpo'], "Música: Save My Child\nArtista: Willie Dixon") !== false && strpos($e['corpo'], "Mensagem:\nPrimeira linha\nSegunda linha") !== false && strpos($e['corpo'], 'nº 9001') !== false, 'corpo do e-mail: dados, mensagem e o número da mensagem no painel');
ok(in_array('Reply-To: Ana Paula <ana@exemplo.com>', $e['cabecalhos'], true) && in_array('Content-Type: text/plain; charset=UTF-8', $e['cabecalhos'], true) && in_array('From: Rádio Agenda Cultural <wordpress@localhost>', $e['cabecalhos'], true), '"Responder" vai para quem escreveu; remetente com o domínio do próprio site');

/* ---- música/artista só valem nos assuntos musicais ---- */
zerar();
rac_contato_processar( valido( array( 'assunto' => 'parcerias', 'musica' => 'Não deveria salvar', 'artista' => 'Ninguém' ) ), '3.3.3.3' );
$m = msgs()[9001];
ok(!isset($m['meta']['contato_musica']) && !isset($m['meta']['contato_artista']), 'em Parcerias/Outro, Música e Artista enviados à força são ignorados');

/* ---- limpeza de entrada e injeção de cabeçalho ---- */
zerar();
rac_contato_processar( valido( array( 'nome' => "Ana\r\nBcc: todos@spam.com", 'mensagem' => '<script>alert(1)</script>Texto <b>com</b> tags' ) ), '4.4.4.4' );
$e = emails()[0]; $m = msgs()[9001];
ok(count(array_filter($e['cabecalhos'], fn($h) => preg_match('/^(Bcc|Cc):/i', $h) || strpbrk($h, "\r\n") !== false)) === 0 && strpos($e['assunto'], "\n") === false && $m['meta']['contato_nome'] === 'Ana Bcc: todos@spam.com', 'nome com quebra de linha não consegue injetar cabeçalhos no e-mail');
ok(strpos($m['conteudo'], '<') === false && strpos($m['conteudo'], 'Texto com tags') !== false, 'tags HTML da mensagem são removidas');
zerar();
rac_contato_processar( valido( array( 'nome' => 'Maria "Mimi" <x@y.com>, Silva' ) ), '4.4.4.5' );
ok(in_array('Reply-To: Maria Mimi x@y.com  Silva <ana@exemplo.com>', emails()[0]['cabecalhos'], true) || count(array_filter(emails()[0]['cabecalhos'], fn($h) => strpos($h, 'Reply-To:') === 0 && substr_count($h, '<') === 1 && substr_count($h, '>') === 1)) === 1, 'aspas, vírgulas e < > no nome não quebram o cabeçalho Reply-To');

/* ---- destino do e-mail ---- */
ok(rac_contato_destino(0) === 'admin@site.test', 'sem página: vai para o e-mail do administrador');
$GLOBALS['fields'][4000] = [];
ok(rac_contato_destino(4000) === 'admin@site.test', 'campo "E-mail de destino" vazio: e-mail do administrador');
$GLOBALS['fields'][4000] = ['contato_email_destino' => 'redacao@exemplo.com'];
ok(rac_contato_destino(4000) === 'redacao@exemplo.com', 'campo preenchido: vai para o endereço do painel');
$GLOBALS['fields'][4000] = ['contato_email_destino' => 'isso nao e email'];
ok(rac_contato_destino(4000) === 'admin@site.test', 'campo com endereço inválido: cai no administrador (nunca fica sem destino)');
$GLOBALS['fields'][4000] = ['contato_email_destino' => 'redacao@exemplo.com'];
zerar();
rac_contato_processar( valido(), '5.5.5.5' );
ok(emails()[0]['para'] === 'redacao@exemplo.com', 'o e-mail enviado usa o destino do painel');
zerar();
rac_contato_processar( valido( array( 'pagina' => '999999' ) ), '5.5.5.6' );
ok(emails()[0]['para'] === 'admin@site.test', 'número de página falso ou inexistente: usa o administrador (não dá para forçar outro destino)');
$GLOBALS['fields'][4000] = [];

/* ---- e-mail que falha: a mensagem fica guardada ---- */
zerar(); file_put_contents("$tmp/contato-falha-email.flag", '1');
$r = rac_contato_processar( valido(), '6.6.6.6' );
ok($r['ok'] && $r['email_enviado'] === false && msgs()[9001]['meta']['contato_email_enviado'] === '0' && emails() === [], 'se o e-mail falhar, o envio vale (mensagem salva, marcada como "e-mail não saiu")');
@unlink("$tmp/contato-falha-email.flag");

/* ---- limites por hora ---- */
zerar();
$res = []; for ($i = 0; $i < 7; $i++) $res[] = rac_contato_processar( valido( array( 'nome' => "Pessoa $i" ) ), '7.7.7.7' )['ok'];
ok($res === [true, true, true, true, true, false, false], 'limite por visitante: 5 por hora, da 6ª em diante recusa');
ok(rac_contato_processar( valido(), '8.8.8.8' )['ok'], 'outro visitante não é afetado pelo limite do primeiro');
ok(count(msgs()) === 6, 'só as mensagens aceitas foram salvas (5 + 1)');
zerar();
for ($i = 0; $i < 40; $i++) rac_contato_processar( valido(), '10.0.' . intdiv($i, 4) . '.' . ($i % 4) );
$r = rac_contato_processar( valido(), '9.9.9.9' );
ok(!$r['ok'] && strpos($r['erro'], 'várias mensagens') !== false, 'limite geral: depois de 40 mensagens na hora, até um visitante novo é recusado (proteção contra enxurrada)');
zerar();

/* ---- painel: tipo de post, colunas e caixa lateral ---- */
do_action('init');
$t = $GLOBALS['cpts']['mensagem_contato'] ?? null;
ok($t && $t['public'] === false && $t['publicly_queryable'] === false && $t['show_ui'] === true && $t['show_in_rest'] === false && $t['has_archive'] === false, '"Mensagens de contato": só no painel (nada público, fora da busca e da API)');
ok(($t['capabilities']['create_posts'] ?? '') === 'do_not_allow' && $t['labels']['menu_name'] === 'Mensagens de contato', 'não aparece "Adicionar novo" (só o formulário cria mensagens) e o menu tem nome claro');
ok(array_values(rac_colunas_mensagens(['cb' => 'x', 'title' => 'y', 'date' => 'z'])) === ['x', 'Assunto e nome', 'De', 'E-mail enviado?', 'Data'], 'colunas da lista: assunto e nome, de, e-mail enviado?, data');
rac_contato_processar( valido( array( 'nome' => 'Bia' ) ), '11.1.1.1' );
ob_start(); rac_conteudo_coluna_mensagem('rac_remetente', 9001); rac_conteudo_coluna_mensagem('rac_email', 9001); $col = ob_get_clean();
ok(strpos($col, 'Bia') !== false && strpos($col, 'mailto:ana@exemplo.com') !== false && strpos($col, '✓ enviado') !== false, 'colunas mostram quem escreveu (com link de e-mail) e "✓ enviado"');
ob_start(); rac_exibir_dados_mensagem((object)['ID' => 9001]); $caixa = ob_get_clean();
ok(strpos($caixa, 'Responder por e-mail') !== false && strpos($caixa, 'Assunto:') !== false && strpos($caixa, 'Chegou por e-mail também') !== false, 'caixa lateral da mensagem: dados e botão "Responder por e-mail"');
zerar(); file_put_contents("$tmp/contato-falha-email.flag", '1');
rac_contato_processar( valido(), '12.1.1.1' );
ob_start(); rac_conteudo_coluna_mensagem('rac_email', 9001); $col2 = ob_get_clean();
ok(strpos($col2, 'não saiu') !== false, 'mensagem cujo e-mail falhou aparece com o aviso "⚠ não saiu" na lista');
@unlink("$tmp/contato-falha-email.flag"); zerar();

/* ---- XSS na exibição do painel ---- */
$GLOBALS['meta'] = [];
update_post_meta(9500, 'contato_nome', '<img src=x onerror=alert(1)>'); update_post_meta(9500, 'contato_email', 'x"><script>@y.com');
ob_start(); rac_conteudo_coluna_mensagem('rac_remetente', 9500); $x = ob_get_clean();
ok(strpos($x, '<img') === false && strpos($x, '<script') === false, 'nome e e-mail são escapados na lista do painel');

/* ---- textos, redes e campos ACF ---- */
$GLOBALS['fields'][4000] = [];
ok(rac_contato_texto('contato_titulo', 4000) === 'Fale com a gente' && strpos(rac_contato_texto('contato_texto', 4000), 'Quer sugerir uma música') === 0 && rac_contato_texto('contato_local', 4000) === 'Votuporanga, SP — Brasil', 'textos provisórios do mockup quando os campos estão vazios');
ok(rac_contato_redes(4000) === [], 'sem links preenchidos: nenhuma rede');
$GLOBALS['fields'][4000] = ['contato_instagram' => 'https://instagram.com/x', 'contato_youtube' => 'https://youtube.com/@x'];
ok(array_column(rac_contato_redes(4000), 'sigla') === ['IG', 'YT'], 'só as redes com link aparecem (IG e YT)');
$GLOBALS['fields'][4000] = [];
do_action('acf/init');
$grupo = null; foreach ($GLOBALS['acf_groups'] as $g) if ($g['key'] === 'group_rac_pagina_contato') $grupo = $g;
ok($grupo && $grupo['location'][0][0]['value'] === 'page-contato.php', 'grupo "Página Contato" ligado ao modelo page-contato.php');
$nomes = array_column($grupo['fields'], 'name');
foreach (['contato_email_destino', 'contato_titulo', 'contato_texto', 'contato_email_publico', 'contato_local', 'contato_instagram', 'contato_facebook', 'contato_youtube', 'contato_enviado'] as $n) ok(in_array($n, $nomes, true), "campo da página: $n");
ok($grupo['fields'][0]['name'] === 'contato_email_destino' && $grupo['fields'][0]['type'] === 'email', 'o primeiro campo é o "E-mail de destino" (tipo e-mail)');
$chaves = []; foreach ($GLOBALS['acf_groups'] as $g) foreach ($g['fields'] as $c) $chaves[] = $c['key'];
ok(count($chaves) === count(array_unique($chaves)), 'nenhuma chave de campo repetida');

/* ---- ganchos e arquivos ---- */
ok(has_action('wp_ajax_nopriv_rac_contato', 'rac_contato_ajax') && has_action('wp_ajax_rac_contato', 'rac_contato_ajax'), 'o formulário responde para visitantes e para quem está logado');
ok(is_file(THEME_DIR . '/page-contato.php') && is_file(THEME_DIR . '/assets/css/contato.css') && is_file(THEME_DIR . '/assets/js/contato.js'), 'modelo, CSS e JS existem');
$css = file_get_contents(THEME_DIR . '/assets/css/contato.css'); preg_match_all('/#[0-9a-fA-F]{3,6}\b/', $css, $mm);
ok($mm[0] === [], 'contato.css: nenhuma cor fora dos tokens');
$js = file_get_contents(THEME_DIR . '/assets/js/contato.js');
ok(strpos($js, "form.getAttribute('action')") !== false && strpos($js, 'form.action,') === false, 'contato.js lê o atributo action (a propriedade form.action é escondida pelo campo oculto "action")');

zerar(); @rmdir($tmp);
echo "\nFALHAS: $falhas\n";
exit($falhas ? 1 : 0);
