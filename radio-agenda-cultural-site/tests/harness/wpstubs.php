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
function remove_action($t,$f,$p=10){ if(isset($GLOBALS['hooks'][$t][$p])) { foreach($GLOBALS['hooks'][$t][$p] as $i=>$x) if($x===$f) unset($GLOBALS['hooks'][$t][$p][$i]); } return true; }
function has_action($t,$f=false){ foreach(($GLOBALS['hooks'][$t]??[]) as $fs) foreach($fs as $x) if($x===$f) return true; return false; }
function wp_dequeue_style($h){ unset($GLOBALS['styles'][$h]); }
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
function wp_date($f){ if($f==='Y-m-d' && isset($_GET['dia'])) return $_GET['dia']; return date($f); }
function get_theme_mod($k,$d=false){ return array_key_exists($k,$GLOBALS['mods']) ? $GLOBALS['mods'][$k] : $d; }
function get_nav_menu_locations(){ return []; } function wp_get_nav_menu_items($m){ return []; }
function get_option($k,$d=false){ return 0; }
function get_permalink($p=null){ $id = is_object($p)?$p->ID:($p??($GLOBALS['loop']['posts'][$GLOBALS['loop']['i']]['url']??0)); if(is_int($id)&&isset($GLOBALS['posts_db'][$id])) return '/blog/post-'.$id.'/'; return is_string($id)?$id:'/estacao/'.$id.'/'; }
function get_category_by_slug($s){ return false; } function get_category_link($c){ return '/'; }
function wp_get_attachment_image($id,$size,$icon,$attr){ $src = ($id==7)?'/h/logo-claro.svg':(($id==55)?'/h/capa-1.svg':(($id==56)?'/h/foto2.svg':'/h/logo-escuro.svg')); return '<img src="'.$src.'" class="'.esc_attr($attr['class']??'').'" alt="'.esc_attr($attr['alt']??'').'" height="34">'; }
function get_template_part($slug,$name=null,$args=[]){ include THEME_DIR.'/'.$slug.'.php'; }
function get_header(){ include THEME_DIR.'/header.php'; } function get_footer(){ include THEME_DIR.'/footer.php'; }
function wp_json_encode($d,$f=0){ return json_encode($d,$f); }
function get_field($n,$id){ return $GLOBALS['fields'][$id][$n] ?? ''; }
function get_the_title($p=null){ if(is_object($p)) return $p->post_title; return $GLOBALS['loop']['posts'][$GLOBALS['loop']['i']]['title']; }
class WP_Query { public $posts=[]; function __construct($a){ $this->posts=$GLOBALS['estacoes_posts']; } }
function get_posts($a){
  if(($a['post_type']??'')==='estacao') return $GLOBALS['estacoes_posts'];
  $ids=array_keys($GLOBALS['posts_db']);
  if(!empty($a['tax_query'])){ $alvo=[]; foreach($a['tax_query'] as $q){ if(is_array($q)&&isset($q['terms'])&&isset($GLOBALS['tag_map'][$q['terms']])) $alvo=array_merge($alvo,$GLOBALS['tag_map'][$q['terms']]); } $ids=array_values(array_unique($alvo)); }
  if(!empty($a['post__not_in'])) $ids=array_values(array_diff($ids,$a['post__not_in']));
  $ids=array_slice($ids,0,$a['posts_per_page']??10);
  return (($a['fields']??'')==='ids') ? $ids : array_map('get_post',$ids);
}
function wp_robots_no_robots($r){ return $r; }
function get_transient($k){ if(strpos($k,'racr_metadado_')===0){ return ((int)substr($k,14)%2===0)?'Willie Dixon - Save My Child':'Barry White - Just the Way You Are'; } return false; } function set_transient(...$a){} function wp_send_json_success($d){ echo json_encode(['success'=>true,'data'=>$d]); exit; } function wp_send_json_error(){ echo json_encode(['success'=>false]); exit; }
function is_admin(){ return false; }
// loop
function have_posts(){ return $GLOBALS['loop']['i']+1 < count($GLOBALS['loop']['posts']); }
function the_post(){ $GLOBALS['loop']['i']++; }
function the_title(){ echo esc_html(get_the_title()); } function the_permalink(){ echo esc_url(get_permalink()); }
function the_content(){ echo $GLOBALS['loop']['posts'][$GLOBALS['loop']['i']]['content']; }
function the_excerpt(){ echo '<p>Resumo.</p>'; } function get_the_date($f='',$p=null){ if($f==='c') return '2026-10-28T10:00:00-03:00'; if(is_object($p)&&isset($GLOBALS['posts_db'][$p->ID])) return $GLOBALS['posts_db'][$p->ID]['data']; return '28 out 2026'; }
function the_author_posts_link(){ echo '<a href="/autor/marina/">Marina Alves</a>'; }
function the_posts_pagination($a=[]){}
// estações de teste
$nomes=['Blues & Jazz Clube','Bossa & Violão','Clássicos Contemporâneos','Vozes da Cidade','Sons do Brasil','Poesia Falada','Noites de Ópera','Rock Progressivo BR','MPB de Ouro','Viola Caipira','Samba & Choro','Trilhas de Cinema'];
$GLOBALS['estacoes_posts']=[]; $GLOBALS['fields']=[];
foreach($nomes as $i=>$n){ $id=100+$i; $GLOBALS['estacoes_posts'][]=(object)['ID'=>$id,'post_title'=>$n]; $GLOBALS['fields'][$id]=['url_do_stream'=>'http://localhost:8099/h/silencio.wav','cor_de_destaque'=>'#ee8133','ordem_de_exibicao'=>$i]; }

// posts de teste para os cards
$GLOBALS['posts_db']=[
 501=>['title'=>'O jazz que ecoa nos becos do centro','cat'=>'Crônicas','data'=>'28 out 2026','aid'=>1,'thumb'=>'/h/foto1.svg'],
 502=>['title'=>'Cinco discos de choro para ouvir sem pressa, com um título bem longo que precisa quebrar em duas linhas e cortar','cat'=>'Descobertas Musicais','data'=>'25 out 2026','aid'=>2,'thumb'=>'/h/foto2.svg'],
 503=>['title'=>'Por que ainda ouvimos rádio (post sem imagem)','cat'=>'Reflexões','data'=>'22 out 2026','aid'=>3,'thumb'=>''],
];
$GLOBALS['autores']=[1=>'Marina Alves',2=>'Igor Bastos',3=>'Nome do Fundador'];
function get_post($p=null){ if(is_object($p)) return $p; if(is_int($p)&&isset($GLOBALS['posts_db'][$p])) return (object)['ID'=>$p,'post_title'=>$GLOBALS['posts_db'][$p]['title'],'post_author'=>$GLOBALS['posts_db'][$p]['aid']]; return null; }
function get_the_category($id){ return isset($GLOBALS['posts_db'][$id]) ? [(object)['name'=>$GLOBALS['posts_db'][$id]['cat']]] : []; }
function is_wp_error($x){ return false; }
function get_the_post_thumbnail($id,$size='',$attr=[]){ $t=$GLOBALS['posts_db'][$id]['thumb']??''; if(!$t){ $n=$id-100; if($n>=0&&$n<4) $t='/h/capa-'.$n.'.svg'; } $c=isset($attr['class'])?' class="'.$attr['class'].'"':''; return $t ? '<img'.$c.' src="'.$t.'" alt="" loading="lazy">' : ''; }
function has_post_thumbnail($id=null){ return get_the_post_thumbnail($id)!==''; }
function get_the_post_thumbnail_url($id,$size=''){ if(isset($GLOBALS['posts_db'][$id])) return $GLOBALS['posts_db'][$id]['thumb']; $n=$id-100; return ($n>=0&&$n<4) ? '/h/capa-'.$n.'.svg' : ''; }
function get_author_posts_url($aid){ return '/autor/'.$aid.'/'; }
function get_the_author_meta($k,$aid=null){ return $GLOBALS['autores'][$aid]??''; }

function get_queried_object_id(){ return $GLOBALS['queried'] ?? 0; }
function get_the_ID(){ return $GLOBALS['loop']['posts'][$GLOBALS['loop']['i']]['id'] ?? 0; }
function get_page_by_path($p){ return null; }
function sanitize_title($t){ return strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',remove_accents($t)),'-')); }
function remove_accents($s){ return strtr($s, ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','õ'=>'o','ô'=>'o','ú'=>'u','ç'=>'c','Á'=>'A','Ã'=>'A','É'=>'E','Ó'=>'O','Ç'=>'C']); }
function wp_kses_post($h){ return $h; }
function get_term_by($campo,$valor,$tax){ if($tax==='post_tag' && $campo==='name' && $valor==='Blues & Jazz Clube') return (object)['term_id'=>77]; return false; }
$GLOBALS['tag_map']=[77=>[502]];
$GLOBALS['fields'][100]['texto_sobre_a_estacao']='<p>Primeiro parágrafo da estação Blues, que funciona como abertura.</p><p>Segundo parágrafo com mais detalhes sobre a programação.</p><p>Terceiro parágrafo de fechamento.</p>';

function wp_strip_all_tags($t){ return strip_tags($t); }
function wp_trim_words($t,$n=55,$mais='...'){ $p=preg_split('/\s+/',trim($t)); if(count($p)<=$n) return implode(' ',$p); return implode(' ',array_slice($p,0,$n)).$mais; }

function wp_timezone(){ return new DateTimeZone('America/Sao_Paulo'); }
function wp_next_scheduled($h){ return $GLOBALS['agendado'][$h] ?? false; }
function wp_schedule_event($t,$rec,$h){ $GLOBALS['agendado'][$h]=$t; $GLOBALS['agenda_args']=[$t,$rec,$h]; return true; }
function wp_clear_scheduled_hook($h){ unset($GLOBALS['agendado'][$h]); }
function wp_list_pluck($l,$campo){ return array_map(function($x) use($campo){ return is_array($x)?$x[$campo]:$x->$campo; },$l); }
// audiência das estações (números do mockup) e página inicial
$ouv=[100=>980,101=>1240,102=>610,103=>420,104=>870,105=>310,106=>290,107=>540,108=>1120,109=>760,110=>690,111=>450];
foreach($ouv as $id=>$n){ $GLOBALS['fields'][$id]['ouvintes']=$n; }
