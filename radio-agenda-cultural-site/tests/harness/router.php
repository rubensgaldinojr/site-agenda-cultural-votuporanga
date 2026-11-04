<?php
require __DIR__.'/wpstubs.php';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri==='/h/silencio.wav' && !is_file(__DIR__.'/silencio.wav')) { $w=fopen(__DIR__.'/silencio.wav','wb'); $n=8000*60*2; fwrite($w,'RIFF'.pack('V',36+$n).'WAVEfmt '.pack('VvvVVvv',16,1,1,8000,16000,2,16).'data'.pack('V',$n).str_repeat("\0",$n)); fclose($w); }
if (strpos($uri,'/theme/')===0) { $f=THEME_DIR.substr($uri,6); }
elseif (strpos($uri,'/plugin/')===0) { $f=PLUGIN_DIR.substr($uri,7); }
elseif (strpos($uri,'/h/')===0) { $f=__DIR__.substr($uri,2); }
else $f=null;
if ($f && is_file($f)) { $ext=pathinfo($f,PATHINFO_EXTENSION); $t=['css'=>'text/css','js'=>'application/javascript','svg'=>'image/svg+xml','wav'=>'audio/wav'][$ext]??'application/octet-stream'; header('Content-Type: '.$t); readfile($f); return; }

if (isset($_GET['semlogo'])) $GLOBALS['mods']['custom_logo']=0;
if (isset($_GET['logoescuro'])) $GLOBALS['mods']['rac_logo_escuro']=8;
$GLOBALS['mods'] += ['rac_logo_escuro'=>0];

// endpoint AJAX do metadado (plugin real)
if ($uri==='/wp-admin/admin-ajax.php') { require PLUGIN_DIR.'/racr-player-radio.php'; do_action('wp_ajax_nopriv_'.($_POST['action']??'')); exit; }
// plugin real do player
require PLUGIN_DIR.'/racr-player-radio.php';
// CSS do tema pai (simulado), enfileirado antes do CSS do tema filho
add_action('wp_enqueue_scripts', function(){ wp_enqueue_style('kadence-sim','/h/kadence-sim.css'); }, 10);
// tema real
require THEME_DIR.'/functions.php';
do_action('after_setup_theme');

$nav = '<p><a href="/">Home</a> · <a href="/estacoes/">Estações</a> · <a href="/blog/">Blog</a> · <a href="/blog/?c=Podcasts">Podcasts</a> · <a href="/blog/post-x/">Post X</a> · <a href="/sobre/">Sobre</a> · <a href="/contato/">Contato</a> · <a href="/pagina-inexistente-xyz/">404</a></p>';
$rotas = [
  '/'=>['Home','home'], '/estacoes/'=>['Estações','page'], '/blog/'=>['Blog','page'], '/blog/post-x/'=>['Post X','single'],
  '/sobre/'=>['Sobre','page'], '/contato/'=>['Contato','page'],
];
if ($uri==='/componentes/') { $GLOBALS['title']='Componentes'; include THEME_DIR.'/page-componentes.php'; return; }
if ($uri==='/estacoes/') { $GLOBALS['queried']=900; if(isset($_GET['hero'])) { $GLOBALS['fields'][900]=['hero_titulo'=>'Título editado no painel','hero_texto'=>'Texto editado no painel.']; } $GLOBALS['title']='Estações'; include THEME_DIR.'/page-estacoes.php'; return; }
if (preg_match('#^/estacao/(\d+)/$#',$uri,$m)) { $sid=(int)$m[1]; $nome=$GLOBALS['estacoes_posts'][$sid-100]->post_title ?? ('Estação '.$sid); $GLOBALS['title']=$nome; $GLOBALS['loop']['posts']=[['id'=>$sid,'title'=>$nome,'url'=>$uri,'content'=>'']]; include THEME_DIR.'/single-estacao.php'; return; }
if (isset($rotas[$uri]) || isset($rotas[rtrim($uri,'/').'/'])) {
  $k = isset($rotas[$uri]) ? $uri : rtrim($uri,'/').'/';
  [$titulo,$tpl] = $rotas[$k];
  $GLOBALS['title'] = $titulo.' — teste';
  $GLOBALS['loop']['posts'] = [['title'=>$titulo,'url'=>$k,'content'=>$nav.'<p>Conteúdo da página '.$titulo.'. '.str_repeat('Lorem ipsum dolor sit amet. ',40).'</p>']];
  do_action('wp_loaded'); do_action('wp_enqueue_scripts_pre');
  include THEME_DIR.'/'.($tpl==='single'?'single':'page').'.php';
} else {
  http_response_code(404); $GLOBALS['title']='404';
  do_action('wp_loaded'); include THEME_DIR.'/404.php';
}
