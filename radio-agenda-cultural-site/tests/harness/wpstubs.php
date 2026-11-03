<?php
// Simulação mínima do WordPress — SÓ para testar o tema no navegador. NÃO vai para o site.
// Veja README.md desta pasta.
define('ABSPATH', '/');
define('THEME_DIR', dirname(__DIR__, 2) . '/themes/rac-kadence-child');
define('PLUGIN_DIR', dirname(__DIR__, 2) . '/plugins/racr-player-radio');
$GLOBALS['hooks'] = []; $GLOBALS['styles'] = []; $GLOBALS['scripts'] = []; $GLOBALS['inline'] = [];
$GLOBALS['mods'] = ['custom_logo' => 7];
$GLOBALS['loop'] = ['posts' => [], 'i' => -1];
function add_action($t,$f,$p=10,$a=1){ $GLOBALS['hooks'][$t][$p][]=$f; return true; }
function add_filter($t,$f,$p=10,$a=1){ return add_action($t,$f,$p,$a); }
function do_action($t,...$args){ if(empty($GLOBALS['hooks'][$t])) return; $h=$GLOBALS['hooks'][$t]; ksort($h); foreach($h as $fs) foreach($fs as $f) call_user_func_array($f,$args); }
function apply_filters($t,$v,...$args){ if(empty($GLOBALS['hooks'][$t])) return $v; $h=$GLOBALS['hooks'][$t]; ksort($h); foreach($h as $fs) foreach($fs as $f) $v=call_user_func_array($f,array_merge([$v],$args)); return $v; }
function add_theme_support(...$a){} function register_nav_menus($a){}
function get_stylesheet_directory(){ return THEME_DIR; }
function get_stylesheet_directory_uri(){ return '/theme'; }
function plugin_dir_url($f){ return '/plugin/'; }
function admin_url($p=''){ return '/wp-admin/'.$p; }
function home_url($p=''){ return 'http://localhost:8099'.$p; }
function wp_enqueue_style($h,$src='',$d=[],$v=null){ $GLOBALS['styles'][$h]=$src; }
function wp_enqueue_script($h,$src='',$d=[],$v=null,$f=false){ if($src!=='') $GLOBALS['scripts'][$h]=$src; else $GLOBALS['scripts'][$h]=$GLOBALS['scripts'][$h]??''; }
function wp_register_script($h,$src='',...$a){ $GLOBALS['scripts'][$h]=$src; }
function wp_add_inline_script($h,$js,$pos='after'){ $GLOBALS['inline'][$h][]=$js; }
function _stub_print_scripts(){ foreach($GLOBALS['scripts'] as $h=>$src){ if($src) echo '<script src="'.$src.'"></script>'."\n"; foreach(($GLOBALS['inline'][$h]??[]) as $js) echo '<script>'.$js.'</script>'."\n"; } }
add_action('wp_footer','_stub_print_scripts',20);
function wp_head(){ do_action('wp_enqueue_scripts'); foreach($GLOBALS['styles'] as $h=>$s) echo '<link rel="stylesheet" href="'.$s.'">'."\n"; do_action('wp_head'); echo '<title>'.htmlspecialchars($GLOBALS['title']??'Teste').'</title>'; }
function wp_footer(){ do_action('wp_footer'); }
function wp_body_open(){}
function language_attributes(){ echo 'lang="pt-BR"'; }
function bloginfo($k){ echo get_bloginfo($k); }
function get_bloginfo($k=''){ return $k==='charset' ? 'UTF-8' : 'Rádio Agenda Cultural'; }
function body_class($c=''){ echo 'class="'.implode(' ',apply_filters('body_class',['page'])).'"'; }
function post_class($c=''){ echo 'class="post '.$c.'"'; }
function esc_html($s){ return htmlspecialchars((string)$s, ENT_QUOTES); } function esc_attr($s){ return esc_html($s); }
function esc_url($s){ return htmlspecialchars((string)$s, ENT_QUOTES); } function esc_url_raw($s){ return (string)$s; }
function esc_textarea($s){ return esc_html($s); }
function sanitize_textarea_field($s){return $s;} function sanitize_email($s){return $s;} function sanitize_text_field($s){return $s;}
function antispambot($e){ return $e; }
function wp_unslash($s){ return $s; }
function wp_parse_url($u,$c=-1){ return $c===-1 ? parse_url($u) : parse_url($u,$c); }
function untrailingslashit($s){ return rtrim($s,'/\\'); }
function wp_date($f){ return date($f); }
function get_theme_mod($k,$d=false){ return array_key_exists($k,$GLOBALS['mods']) ? $GLOBALS['mods'][$k] : $d; }
function get_nav_menu_locations(){ return []; } function wp_get_nav_menu_items($m){ return []; }
function get_option($k,$d=false){ return 0; }
function get_permalink($p=null){ $id = is_object($p)?$p->ID:($p??($GLOBALS['loop']['posts'][$GLOBALS['loop']['i']]['url']??0)); return is_string($id)?$id:'/estacao/'.$id.'/'; }
function get_category_by_slug($s){ return false; } function get_category_link($c){ return '/'; }
function wp_get_attachment_image($id,$size,$icon,$attr){ $src = ($id==7)?'/h/logo-claro.svg':'/h/logo-escuro.svg'; return '<img src="'.$src.'" class="'.esc_attr($attr['class']).'" alt="'.esc_attr($attr['alt']).'" height="34">'; }
function get_template_part($slug,$name=null,$args=[]){ include THEME_DIR.'/'.$slug.'.php'; }
function get_header(){ include THEME_DIR.'/header.php'; } function get_footer(){ include THEME_DIR.'/footer.php'; }
function wp_json_encode($d,$f=0){ return json_encode($d,$f); }
function get_field($n,$id){ return $GLOBALS['fields'][$id][$n] ?? ''; }
function get_the_title($p=null){ if(is_object($p)) return $p->post_title; return $GLOBALS['loop']['posts'][$GLOBALS['loop']['i']]['title']; }
class WP_Query { public $posts=[]; function __construct($a){ $this->posts=$GLOBALS['estacoes_posts']; } }
function get_posts($a){ return $GLOBALS['estacoes_posts']; }
function get_transient($k){ return false; } function set_transient(...$a){} function wp_send_json_success($d){ echo json_encode(['success'=>true,'data'=>$d]); exit; } function wp_send_json_error(){ echo json_encode(['success'=>false]); exit; }
function is_admin(){ return false; }
// loop
function have_posts(){ return $GLOBALS['loop']['i']+1 < count($GLOBALS['loop']['posts']); }
function the_post(){ $GLOBALS['loop']['i']++; }
function the_title(){ echo esc_html(get_the_title()); } function the_permalink(){ echo esc_url(get_permalink()); }
function the_content(){ echo $GLOBALS['loop']['posts'][$GLOBALS['loop']['i']]['content']; }
function the_excerpt(){ echo '<p>Resumo.</p>'; } function get_the_date(){ return '28 out 2026'; }
function the_author_posts_link(){ echo '<a href="/autor/marina/">Marina Alves</a>'; }
function the_posts_pagination($a=[]){}
// estações de teste
$nomes=['Blues & Jazz Clube','Bossa & Violão','Clássicos Contemporâneos','Vozes da Cidade','Sons do Brasil','Poesia Falada','Noites de Ópera','Rock Progressivo BR','MPB de Ouro','Viola Caipira','Samba & Choro','Trilhas de Cinema'];
$GLOBALS['estacoes_posts']=[]; $GLOBALS['fields']=[];
foreach($nomes as $i=>$n){ $id=100+$i; $GLOBALS['estacoes_posts'][]=(object)['ID'=>$id,'post_title'=>$n]; $GLOBALS['fields'][$id]=['url_do_stream'=>'http://localhost:8099/h/silencio.wav','cor_de_destaque'=>'#ee8133','ordem_de_exibicao'=>$i]; }
