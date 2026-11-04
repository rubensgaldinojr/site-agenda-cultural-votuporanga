<?php
// Simulação do WordPress para a FASE 4 (blog, autor, post, comentários) — SÓ para testar o tema. NÃO vai para o site.
// Reaproveita a parte genérica de wpstubs.php (copiada) e acrescenta um mini-WordPress com posts, usuários, termos e comentários.
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
function wp_date($f,$ts=null){ if($f==='Y-m-d' && isset($_GET['dia']) && $ts===null) return $_GET['dia']; $r=date($f,$ts ?? time()); return strpos($f,'M')!==false ? strtr($r,['Jan'=>'jan','Feb'=>'fev','Mar'=>'mar','Apr'=>'abr','May'=>'mai','Jun'=>'jun','Jul'=>'jul','Aug'=>'ago','Sep'=>'set','Oct'=>'out','Nov'=>'nov','Dec'=>'dez']) : $r; }
function get_theme_mod($k,$d=false){ return array_key_exists($k,$GLOBALS['mods']) ? $GLOBALS['mods'][$k] : $d; }
function get_nav_menu_locations(){ return []; } function wp_get_nav_menu_items($m){ return []; }
function wp_json_encode($d,$f=0){ return json_encode($d,$f); }
function wp_robots_no_robots($r){ return $r; }
function is_admin(){ return false; }
function sanitize_title($t){ return strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',remove_accents($t)),'-')); }
function remove_accents($s){ return strtr($s, ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','õ'=>'o','ô'=>'o','ú'=>'u','ç'=>'c','Á'=>'A','Ã'=>'A','É'=>'E','Ó'=>'O','Ç'=>'C']); }
function wp_kses_post($h){ return $h; }
function wp_strip_all_tags($t){ return strip_tags($t); }
function wp_trim_words($t,$n=55,$mais='...'){ $p=preg_split('/\s+/',trim($t)); if(count($p)<=$n) return implode(' ',$p); return implode(' ',array_slice($p,0,$n)).$mais; }

function wp_timezone(){ return new DateTimeZone('America/Sao_Paulo'); }
define('DAY_IN_SECONDS', 86400);
function human_time_diff($de,$ate=0){ $d=max(1,($ate?:time())-$de); if($d<3600) return max(1,(int)round($d/60)).' min'; if($d<86400){ $h=(int)round($d/3600); return $h.($h==1?' hora':' horas'); } $x=(int)round($d/86400); return $x.($x==1?' dia':' dias'); }
function site_url($p=''){ return 'http://localhost:8099'.$p; }
function wp_die($msg,$titulo='',$args=[]){ if(PHP_SAPI==='cli') throw new Exception($msg); http_response_code($args['response']??500); echo '<!doctype html><html><body><div id="error-page"><p>'.htmlspecialchars($msg).'</p></div></body></html>'; exit; }
// estações de teste
$nomes=['Blues & Jazz Clube','Bossa & Violão','Clássicos Contemporâneos','Vozes da Cidade','Sons do Brasil','Poesia Falada','Noites de Ópera','Rock Progressivo BR','MPB de Ouro','Viola Caipira','Samba & Choro','Trilhas de Cinema'];
$GLOBALS['estacoes_posts']=[]; $GLOBALS['fields']=[];
foreach($nomes as $i=>$n){ $id=100+$i; $GLOBALS['estacoes_posts'][]=(object)['ID'=>$id,'post_title'=>$n,'post_type'=>'estacao']; $GLOBALS['fields'][$id]=['url_do_stream'=>'http://localhost:8099/h/silencio.wav','cor_de_destaque'=>'#ee8133','ordem_de_exibicao'=>$i]; }

/* =====================================================================
   MINI-WORDPRESS DA FASE 4
   Posts, usuários, categorias, tags, comentários, consulta principal.
   ===================================================================== */
define('HOJE', '2026-10-30 12:00:00');

$GLOBALS['usuarios'] = [
 1=>['login'=>'marina','slug'=>'marina-alves','nome'=>'Marina Alves','bio'=>"Cronista e apresentadora do Blues & Jazz Clube. Escreve sobre os becos, as vozes e os discos de Votuporanga.\n\nGosta de café coado e de vinil riscado.",'meta'=>['rac_autor_genero'=>'autora'],'avatar'=>'',
    'campos'=>['autor_foto'=>55,'autor_papel'=>'Cronista e apresentadora','autor_desde'=>'Escreve desde 2021','autor_citacao'=>'O rádio é a companhia que a gente escolhe.','autor_citacao_de'=>'Clarice Lispector']],
 2=>['login'=>'igor','slug'=>'igor-bastos','nome'=>'Igor Bastos','bio'=>'Pesquisador de música e curador das descobertas.','meta'=>['rac_autor_genero'=>'autor'],'avatar'=>'/h/foto2.svg','campos'=>[]],
 3=>['login'=>'equipe','slug'=>'equipe-agenda-cultural','nome'=>'Equipe Agenda Cultural','bio'=>'','meta'=>[],'avatar'=>'/h/nao-existe-gravatar.png','campos'=>[]],
];
$GLOBALS['cats'] = [
 11=>['Crônicas','cronicas','Textos curtos sobre a cidade.'], 12=>['Descobertas Musicais','descobertas-musicais',''], 13=>['Reflexões','reflexoes',''],
 14=>['Podcasts','podcasts',''], 15=>['Poesia','poesia',''], 16=>['Vídeos','videos',''], 17=>['Tirinhas','tirinhas',''],
];
$GLOBALS['tags'] = [ 21=>['Blues & Jazz Clube','blues-jazz-clube'], 22=>['Memória','memoria'], 23=>['Choro','choro'] ];
$lorem = '<p>Primeiro parágrafo do texto, com <a href="/f4/blog/">um link</a> e bastante conteúdo para a leitura fluir. '.str_repeat('Palavras e mais palavras para o tempo de leitura. ',12).'</p><p>Segundo parágrafo da crônica.</p><h3>Um intertítulo</h3><p>Terceiro parágrafo, depois do intertítulo.</p><blockquote><p>Uma citação dentro do texto.</p></blockquote><p>Último parágrafo do texto.</p>';
$GLOBALS['P'] = [
 601=>['t'=>'O jazz que ecoa nos becos do centro','a'=>1,'c'=>[11],'g'=>[21,22],'d'=>'2026-10-28 10:00:00','thumb'=>'/h/foto1.svg','cap'=>'Beco da Rua Amazonas, ao entardecer.','ex'=>'Uma caminhada ao entardecer pelo centro, atrás do som que sobra das janelas.','html'=>$lorem,'f'=>['formato'=>'texto']],
 602=>['t'=>'Estrofes para a madrugada','a'=>1,'c'=>[15],'g'=>[22],'d'=>'2026-10-26 09:00:00','thumb'=>'','cap'=>'','ex'=>'Versos sobre a hora em que o rádio fica sozinho.','html'=>'<p>A noite desce devagar<br>sobre o telhado da estação.</p><p>O rádio fala baixinho<br>para quem não consegue dormir.</p><p>E a música, sem pressa,<br>atravessa a madrugada.</p>','f'=>['formato'=>'poesia']],
 603=>['t'=>'Conversa com o luthier da Rua Seis','a'=>2,'c'=>[16,14],'g'=>[23],'d'=>'2026-10-24 18:00:00','thumb'=>'/h/foto2.svg','cap'=>'','ex'=>'Quarenta anos fazendo violões, e ainda ouvindo cada corda.','html'=>'<p>Notas do episódio.</p>','f'=>['formato'=>'video','video_url'=>'https://www.youtube.com/watch?v=dQw4w9WgXcQ','video_capitulos'=>"00:00 | Abertura\n02:15 | A oficina\n1:05:30 | Despedida"]],
 604=>['t'=>'Descoberta Musical: Fulano Jhonson - Irlanda - Blues','a'=>2,'c'=>[12],'g'=>[21],'d'=>'2026-10-22 08:00:00','thumb'=>'','cap'=>'','ex'=>'','html'=>'<p>Um bluesman irlandês que merece ser ouvido por aqui.</p>','f'=>['formato'=>'musica','artista_nome'=>'Fulano Jhonson','artista_pais'=>'ie','artista_genero'=>'Blues','artista_link'=>'https://open.spotify.com/intl-pt/artist/4Z8W4fKeB5YxbusRsdQVPb?si=abc','artista_instagram'=>'https://instagram.com/fulano','artista_estacao'=>100]],
 605=>['t'=>'Tirinha: o volume do vizinho','a'=>3,'c'=>[17],'g'=>[],'d'=>'2026-10-20 07:00:00','thumb'=>'/h/foto1.svg','cap'=>'','ex'=>'','html'=>'<p>O vizinho de cima ouve Beethoven às seis da manhã.</p>','f'=>['formato'=>'tirinha','tirinha_autor'=>'Autor Exemplo','tirinha_ano'=>'2019','tirinha_fonte'=>'Jornal da Cidade']],
 606=>['t'=>'Tirinha: a antena torta','a'=>3,'c'=>[17],'g'=>[],'d'=>'2026-10-18 07:00:00','thumb'=>'/h/foto2.svg','cap'=>'','ex'=>'','html'=>'','f'=>['formato'=>'tirinha']],
 607=>['t'=>'A foto da praça às cinco da tarde','a'=>1,'c'=>[13],'g'=>[22],'d'=>'2026-10-16 17:00:00','thumb'=>'/h/foto1.svg','cap'=>'A praça central, de cima do coreto.','ex'=>'','html'=>'<p>Uma imagem que diz mais que três parágrafos.</p>','f'=>['formato'=>'imagem','imagem_credito'=>'Acervo da cidade']],
 608=>['t'=>'A frase que ficou','a'=>2,'c'=>[13],'g'=>[],'d'=>'2026-10-14 11:00:00','thumb'=>'','cap'=>'','ex'=>'','html'=>'<p>Algumas frases a gente carrega pela vida.</p>','f'=>['formato'=>'citacao','citacao_texto'=>'A música é o silêncio que fala.','citacao_autor'=>'Autor da Frase','citacao_obra'=>'Livro Qualquer, 1999']],
];
for($k=609;$k<=614;$k++){ $GLOBALS['P'][$k]=['t'=>'Texto de apoio número '.($k-608),'a'=>1+($k%3),'c'=>[$k%2?11:13],'g'=>$k%3?[23]:[],'d'=>'2026-10-'.str_pad(12-($k-609)*2,2,'0',STR_PAD_LEFT).' 10:00:00','thumb'=>$k%2?'/h/foto1.svg':'','cap'=>'','ex'=>'Resumo do texto '.($k-608).'.','html'=>$lorem,'f'=>['formato'=>'texto']]; }
$GLOBALS['P'][615]=['t'=>'Rascunho que não aparece','a'=>1,'c'=>[11],'g'=>[],'d'=>'2026-10-29 10:00:00','thumb'=>'','cap'=>'','ex'=>'','html'=>'<p>x</p>','f'=>['formato'=>'texto'],'status'=>'draft'];
foreach($GLOBALS['P'] as $id=>$p){ $GLOBALS['fields'][$id]=$p['f']; }
foreach($GLOBALS['usuarios'] as $uid=>$u){ $GLOBALS['fields']['user_'.$uid]=$u['campos']; }
$GLOBALS['paginas']=[2000=>'/f4/blog/', 2001=>'/f4/blogp/'];
$GLOBALS['ctx']='home'; $GLOBALS['obj']=null; $GLOBALS['paged']=1; $GLOBALS['post']=null;

// comentários do post 601 (árvore) e do 604
function _c($id,$post,$pai,$nome,$texto,$h,$uid=0,$aprov='1'){ return ['id'=>$id,'post'=>$post,'pai'=>$pai,'nome'=>$nome,'email'=>strtolower($nome).'@ex.com','uid'=>$uid,'texto'=>$texto,'h'=>$h,'aprov'=>$aprov]; }
$GLOBALS['C']=[
 _c(1,601,0,'Ana Paula','Lindo texto! Me lembrou as noites na praça.',60),
 _c(2,601,1,'Marina Alves','Obrigada, Ana! Volte sempre.',50,1),
 _c(3,601,2,'Ana Paula','Voltarei, com certeza.',40),
 _c(4,601,0,'Roberto S.','Qual o nome do disco que tocava na janela?',30),
 _c(5,601,0,'Juliana M.','Compartilhei com minha mãe.',20),
 _c(6,601,0,'Carlos Eduardo','Maravilha de crônica.',10),
 _c(7,601,0,'Dona Marta','Moro nessa rua e nunca reparei.',9),
 _c(8,601,0,'Beatriz','Concordo com tudo.',8),
 _c(9,601,0,'Visitante Novo','Meu comentário ainda está sendo analisado.',1,0,'0'),
 _c(10,604,0,'Helena','Ótima descoberta!',5),
];

function get_user_by($campo,$v){ foreach($GLOBALS['usuarios'] as $id=>$u){ if(($campo==='slug'&&$u['slug']===$v)||($campo==='id'&&$id==$v)||($campo==='login'&&$u['login']===$v)) return (object)['ID'=>$id,'display_name'=>$u['nome'],'user_nicename'=>$u['slug']]; } return false; }
function get_users($a=[]){ $o=[]; foreach($GLOBALS['usuarios'] as $id=>$u){ foreach($GLOBALS['P'] as $p){ if($p['a']===$id && ($p['status']??'publish')==='publish'){ $o[$id]=(object)['ID'=>$id,'display_name'=>$u['nome'],'user_nicename'=>$u['slug']]; break; } } } usort($o,fn($x,$y)=>strcmp($x->display_name,$y->display_name)); return array_values($o); }
function get_user_meta($id,$k,$single=false){ return $GLOBALS['usuarios'][$id]['meta'][$k] ?? ''; }
function get_avatar_url($id,$a=[]){ return $GLOBALS['usuarios'][$id]['avatar'] ?? ''; }
function is_user_logged_in(){ return !empty($_GET['logado']); }
function wp_get_current_user(){ return (object)['ID'=>3,'display_name'=>'Equipe Agenda Cultural']; }
function wp_get_current_commenter(){ return !empty($_GET['lembrado']) ? ['comment_author'=>'Lembrado','comment_author_email'=>'lembrado@ex.com','comment_author_url'=>''] : ['comment_author'=>'','comment_author_email'=>'','comment_author_url'=>'']; }
function is_wp_error($x){ return false; }
function checked($a,$b=true,$echo=true){ $r = ((string)$a===(string)$b) ? ' checked="checked"' : ''; if($echo) echo $r; return $r; }
function post_password_required(){ return false; }
function wpautop($t){ $ps=preg_split('/\n{2,}/',trim($t)); return implode('',array_map(fn($p)=>'<p>'.nl2br($p).'</p>',$ps)); }
function wp_oembed_get($u){ return $u ? '<iframe class="oembed-teste" src="'.esc_url($u).'"></iframe>' : false; }
function sanitize_key($k){ return preg_replace('/[^a-z0-9_\-]/','',strtolower($k)); }
function locate_template($n){ return file_exists(THEME_DIR.'/'.$n) ? THEME_DIR.'/'.$n : ''; }
function flush_rewrite_rules($h=true){}
function acf_add_local_field_group($g){ $GLOBALS['acf_groups'][]=$g; }
function get_post_type($id=null){ $id=is_object($id)?$id->ID:$id; return isset($GLOBALS['P'][$id])?'post':(($id>=100&&$id<200)?'estacao':false); }
function get_post_meta($id,$k,$s=false){ return $GLOBALS['meta'][$id][$k] ?? ''; }
function update_post_meta($id,$k,$v){ $GLOBALS['meta'][$id][$k]=$v; return true; }
function wp_unique_post_slug($slug,$id,$st,$tipo,$pai){ return $slug; }
function wp_update_post($d){ $id=$d['ID']; if(isset($d['post_title'])) $GLOBALS['P'][$id]['t']=$d['post_title']; if(isset($d['post_name'])) $GLOBALS['P'][$id]['slug']=$d['post_name']; $GLOBALS['atualizado'][]=$d; return $id; }
function add_query_arg(...$a){ if(is_array($a[0])){ $args=$a[0]; $url=$a[1]??($_SERVER['REQUEST_URI']??''); } else { $args=[$a[0]=>$a[1]]; $url=$a[2]??($_SERVER['REQUEST_URI']??''); } $hash=''; if(($p=strpos($url,'#'))!==false){ $hash=substr($url,$p); $url=substr($url,0,$p); } $q=''; if(($p=strpos($url,'?'))!==false){ $q=substr($url,$p+1); $url=substr($url,0,$p); } parse_str($q,$cur); foreach($args as $k=>$v){ if($v===false||$v===null) unset($cur[$k]); else $cur[$k]=$v; } $s=http_build_query($cur); return $url.($s?'?'.$s:'').$hash; }
function remove_query_arg($k,$url=false){ $url=$url?:($_SERVER['REQUEST_URI']??''); foreach((array)$k as $x) $url=add_query_arg($x,false,$url); return $url; }

/* ---- posts ---- */
function _id($p){ if(is_object($p)) return (int)$p->ID; if($p===null||$p===false||$p===0) return (int)($GLOBALS['post']->ID ?? 0); return (int)$p; }
function _post_obj($id){ if(isset($GLOBALS['P'][$id])){ $p=$GLOBALS['P'][$id]; return (object)['ID'=>$id,'post_title'=>$p['t'],'post_author'=>$p['a'],'post_content'=>$p['html'],'post_date'=>$p['d'],'post_status'=>$p['status']??'publish','post_name'=>$p['slug']??'','post_type'=>'post']; } foreach($GLOBALS['estacoes_posts'] as $e) if($e->ID==$id) return $e; return null; }
function get_post($p=null){ if(is_object($p)) return $p; return _post_obj(_id($p)); }
function get_post_field($f,$id=null){ $o=get_post($id); return $o ? ($o->$f ?? '') : ''; }
function get_the_ID(){ return (int)($GLOBALS['post']->ID ?? 0); }
function get_the_title($p=null){ $o=get_post($p); return $o ? $o->post_title : ''; }
function the_title(){ echo esc_html(get_the_title()); }
function the_content(){ echo get_post(null)->post_content; }
function has_excerpt($id=null){ return !empty($GLOBALS['P'][_id($id)]['ex']); }
function get_the_excerpt($id=null){ return $GLOBALS['P'][_id($id)]['ex'] ?? ''; }
function get_permalink($p=null){ $id=_id($p); if(isset($GLOBALS['P'][$id])) return '/f4/post-'.$id.'/'; if(isset($GLOBALS['paginas'][$id])) return $GLOBALS['paginas'][$id]; return '/estacao/'.$id.'/'; }
function get_the_date($f='',$p=null){ $id=_id($p); $d=$GLOBALS['P'][$id]['d'] ?? '2026-10-28 10:00:00'; return $f==='c' ? date('c',strtotime($d)) : wp_date($f?:'d M Y',strtotime($d)); }
function get_the_author_meta($k,$uid=null){ if($uid===null) $uid=$GLOBALS['P'][_id(null)]['a'] ?? 0; $u=$GLOBALS['usuarios'][$uid] ?? null; if(!$u) return ''; return ['ID'=>$uid,'display_name'=>$u['nome'],'description'=>$u['bio'],'user_nicename'=>$u['slug']][$k] ?? ''; }
function get_author_posts_url($uid){ return '/f4/autor/'.($GLOBALS['usuarios'][$uid]['slug'] ?? $uid).'/'; }
function get_field($n,$id=null){ $k = is_string($id) ? $id : _id($id); return $GLOBALS['fields'][$k][$n] ?? ''; }
function get_post_thumbnail_id($id=null){ return !empty($GLOBALS['P'][_id($id)]['thumb']) ? 5000+_id($id) : 0; }
function has_post_thumbnail($id=null){ return get_post_thumbnail_id($id)>0; }
function wp_get_attachment_caption($aid){ $id=$aid-5000; return $GLOBALS['P'][$id]['cap'] ?? ''; }
function get_the_post_thumbnail($id=null,$size='',$attr=[]){ $id=_id($id); $t=$GLOBALS['P'][$id]['thumb'] ?? ''; if(!$t) return ''; $c=isset($attr['class'])?' class="'.$attr['class'].'"':''; $alt=$attr['alt']??''; return '<img'.$c.' src="'.$t.'" alt="'.esc_attr($alt).'" loading="'.($attr['loading']??'lazy').'">'; }
function get_the_post_thumbnail_url($id,$s=''){ return $GLOBALS['P'][_id($id)]['thumb'] ?? ''; }
function wp_get_attachment_image($id,$size,$icon,$attr){ $src = ($id==7)?'/h/logo-claro.svg':(($id==55)?'/h/capa-1.svg':(($id==56)?'/h/foto2.svg':'/h/logo-escuro.svg')); return '<img src="'.$src.'" class="'.esc_attr($attr['class']??'').'" alt="'.esc_attr($attr['alt']??'').'" height="34">'; }
function get_option($k,$d=false){ if($k==='page_on_front') return 1000; if($k==='page_for_posts') return empty($GLOBALS['sem_pagina_posts']) ? 2000 : 0; return 0; }
function get_page_by_path($s){ return ($s==='blog' && !empty($GLOBALS['sem_pagina_posts'])) ? (object)['ID'=>2001] : null; }
function get_template_part($slug,$name=null,$args=[]){ include THEME_DIR.'/'.$slug.'.php'; }
function get_header(){ include THEME_DIR.'/header.php'; } function get_footer(){ include THEME_DIR.'/footer.php'; }

/* ---- termos ---- */
function _cat_obj($id){ if(!isset($GLOBALS['cats'][$id])) return null; $c=$GLOBALS['cats'][$id]; $n=0; foreach($GLOBALS['P'] as $p) if(in_array($id,$p['c'])&&($p['status']??'publish')==='publish') $n++; return (object)['term_id'=>$id,'name'=>$c[0],'slug'=>$c[1],'description'=>$c[2],'count'=>$n,'taxonomy'=>'category']; }
function _tag_obj($id){ if(!isset($GLOBALS['tags'][$id])) return null; $c=$GLOBALS['tags'][$id]; $n=0; foreach($GLOBALS['P'] as $p) if(in_array($id,$p['g'])) $n++; return (object)['term_id'=>$id,'name'=>$c[0],'slug'=>$c[1],'description'=>'','count'=>$n,'taxonomy'=>'post_tag']; }
function get_categories($a=[]){ $o=[]; foreach($GLOBALS['cats'] as $id=>$c){ $x=_cat_obj($id); if($x->count>0) $o[]=$x; } usort($o,fn($a,$b)=>strcmp(remove_accents($a->name),remove_accents($b->name))); return $o; }
function get_category_by_slug($s){ foreach($GLOBALS['cats'] as $id=>$c) if($c[1]===$s) return _cat_obj($id); return false; }
function get_category_link($c){ $id=is_object($c)?$c->term_id:(int)$c; return '/f4/categoria/'.($GLOBALS['cats'][$id][1] ?? $id).'/'; }
function get_tag_link($c){ $id=is_object($c)?$c->term_id:(int)$c; return '/f4/tag/'.($GLOBALS['tags'][$id][1] ?? $id).'/'; }
function get_the_category($id=null){ $id=_id($id); return array_map('_cat_obj',$GLOBALS['P'][$id]['c'] ?? []); }
function get_the_tags($id=null){ $id=_id($id); $g=$GLOBALS['P'][$id]['g'] ?? []; return $g ? array_map('_tag_obj',$g) : false; }
function wp_get_post_tags($id,$a=[]){ return $GLOBALS['P'][$id]['g'] ?? []; }
function wp_get_post_categories($id){ return $GLOBALS['P'][$id]['c'] ?? []; }
function has_term($termo,$tax,$id=null){ $id=_id($id); $lista = $tax==='post_tag' ? ($GLOBALS['P'][$id]['g'] ?? []) : ($GLOBALS['P'][$id]['c'] ?? []); foreach($lista as $t){ $o = $tax==='post_tag' ? _tag_obj($t) : _cat_obj($t); if($o->name===$termo||$o->slug===$termo||$o->term_id===$termo) return true; } return false; }
function get_term_by($campo,$v,$tax){ if($tax==='post_tag'){ foreach($GLOBALS['tags'] as $id=>$t){ if(($campo==='name'&&$t[0]===$v)||($campo==='slug'&&$t[1]===$v)) return _tag_obj($id); } } if($tax==='category'){ foreach($GLOBALS['cats'] as $id=>$t){ if(($campo==='name'&&$t[0]===$v)||($campo==='slug'&&$t[1]===$v)) return _cat_obj($id); } } return false; }

/* ---- consultas ---- */
function _publicado($id){ return ($GLOBALS['P'][$id]['status'] ?? 'publish')==='publish'; }
class WP_Query {
  public $q=[]; public $posts=[]; public $found_posts=0; public $max_num_pages=0; public $post_count=0; public $current=-1; public $principal=false; public $ctx='';
  function __construct($a=[]){ $this->q=$a; if($a) $this->consultar(); }
  function get($k,$d=''){ return $this->q[$k] ?? $d; } function set($k,$v){ $this->q[$k]=$v; }
  function is_main_query(){ return $this->principal; } function is_home(){ return $this->ctx==='home'; } function is_category(){ return $this->ctx==='category'; } function is_tag(){ return $this->ctx==='tag'; } function is_author(){ return $this->ctx==='author'; }
  function consultar(){
    $a=$this->q; $ids=[];
    if(($a['post_type']??'post')==='estacao'){ $this->posts=$GLOBALS['estacoes_posts']; $this->found_posts=$this->post_count=count($this->posts); return; }
    foreach($GLOBALS['P'] as $id=>$p){ if(!_publicado($id)) continue; $ids[]=$id; }
    $f=function($fn) use(&$ids){ $ids=array_values(array_filter($ids,$fn)); };
    if(!empty($a['post__not_in'])) $f(fn($i)=>!in_array($i,$a['post__not_in']));
    if(!empty($a['author_name'])) $f(fn($i)=>$GLOBALS['usuarios'][$GLOBALS['P'][$i]['a']]['slug']===$a['author_name']);
    if(!empty($a['author'])) $f(fn($i)=>$GLOBALS['P'][$i]['a']==$a['author']);
    if(!empty($a['category_name'])) $f(fn($i)=>(bool)array_filter($GLOBALS['P'][$i]['c'],fn($c)=>$GLOBALS['cats'][$c][1]===$a['category_name']));
    if(!empty($a['cat'])) $f(fn($i)=>in_array($a['cat'],$GLOBALS['P'][$i]['c']));
    if(!empty($a['tag_id'])) $f(fn($i)=>in_array($a['tag_id'],$GLOBALS['P'][$i]['g']));
    if(!empty($a['tag__in'])) $f(fn($i)=>(bool)array_intersect($a['tag__in'],$GLOBALS['P'][$i]['g']));
    if(!empty($a['category__in'])) $f(fn($i)=>(bool)array_intersect($a['category__in'],$GLOBALS['P'][$i]['c']));
    if(!empty($a['tax_query'])){ $ok=[]; foreach($a['tax_query'] as $q){ if(!is_array($q)||!isset($q['terms'])) continue; foreach($ids as $i){ $l = $q['taxonomy']==='post_tag' ? $GLOBALS['P'][$i]['g'] : $GLOBALS['P'][$i]['c']; if(in_array($q['terms'],$l)) $ok[$i]=1; } } $f(fn($i)=>isset($ok[$i])); }
    if(!empty($a['meta_key'])) $f(fn($i)=>($GLOBALS['P'][$i]['f'][$a['meta_key']] ?? null)===$a['meta_value']);
    if(!empty($a['date_query'][0])){ $d=$a['date_query'][0]; $ref=strtotime($d['before'] ?? $d['after']); $inc=!empty($d['inclusive']); $antes=isset($d['before']); $f(function($i) use($ref,$inc,$antes){ $t=strtotime($GLOBALS['P'][$i]['d']); return $antes ? ($inc ? $t<=$ref : $t<$ref) : ($inc ? $t>=$ref : $t>$ref); }); }
    $ord=$a['orderby'] ?? 'date'; $dir=strtoupper($a['order'] ?? 'DESC');
    usort($ids,function($x,$y) use($a,$ord,$dir){ $P=$GLOBALS['P'];
      if(!empty($a['rac_ordem_autor'])){ $c=strcmp($GLOBALS['usuarios'][$P[$x]['a']]['nome'],$GLOBALS['usuarios'][$P[$y]['a']]['nome']); if($c) return $c; return strcmp($P[$y]['d'],$P[$x]['d']); }
      $c = $ord==='title' ? strcmp(mb_strtolower(remove_accents($P[$x]['t'])),mb_strtolower(remove_accents($P[$y]['t']))) : strcmp($P[$x]['d'],$P[$y]['d']);
      return $dir==='ASC' ? $c : -$c; });
    $this->found_posts=count($ids); $por=(int)($a['posts_per_page'] ?? 10); $pg=max(1,(int)($a['paged'] ?? 1));
    if($por>0){ $this->max_num_pages=(int)ceil(count($ids)/$por); $ids=array_slice($ids,($pg-1)*$por,$por); } else $this->max_num_pages=1;
    $this->posts = (($a['fields'] ?? '')==='ids') ? $ids : array_map('_post_obj',$ids); $this->post_count=count($this->posts);
  }
  function have_posts(){ return $this->current+1 < $this->post_count; }
  function the_post(){ $this->current++; $GLOBALS['post']=$this->posts[$this->current]; }
}
function get_posts($a){ if(($a['post_type']??'post')==='livro') return []; $q=new WP_Query($a); return $q->posts; }
function have_posts(){ return $GLOBALS['wp_query']->have_posts(); }
function the_post(){ $GLOBALS['wp_query']->the_post(); }
function wp_reset_postdata(){ $GLOBALS['post']=$GLOBALS['wp_query']->posts[$GLOBALS['wp_query']->current] ?? null; }
function is_home(){ return $GLOBALS['ctx']==='home'; } function is_category(){ return $GLOBALS['ctx']==='category'; } function is_tag(){ return $GLOBALS['ctx']==='tag'; } function is_author(){ return $GLOBALS['ctx']==='author'; }
function get_queried_object(){ return $GLOBALS['obj']; } function get_queried_object_id(){ return $GLOBALS['obj']->term_id ?? ($GLOBALS['obj']->ID ?? 0); }
function get_query_var($k,$d=''){ if($k==='paged') return $GLOBALS['paged']; if($k==='cpage') return max(0,(int)($_GET['cpage'] ?? 0)); return $d; }
function get_pagenum_link($n=1){ $p=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH); $p=preg_replace('#page/\d+/?$#','',rtrim($p,'/').'/'); $q=$_SERVER['QUERY_STRING'] ?? ''; return $p.($n>1?'page/'.$n.'/':'').($q?'?'.$q:''); }

/* ---- comentários ---- */
function comments_open($id=null){ return empty($GLOBALS['sem_comentarios']); }
function get_comments_number($id=null){ $id=_id($id); return count(array_filter($GLOBALS['C'],fn($c)=>$c['post']===$id && $c['aprov']==='1')); }
function _com_obj($c){ return (object)['comment_ID'=>$c['id'],'comment_post_ID'=>$c['post'],'comment_parent'=>$c['pai'],'comment_author'=>$c['nome'],'comment_author_email'=>$c['email'],'user_id'=>$c['uid'],'comment_content'=>$c['texto'],'comment_date_gmt'=>gmdate('Y-m-d H:i:s',time()-$c['h']*3600),'comment_approved'=>$c['aprov']]; }
function get_comments($a){ $o=[]; foreach($GLOBALS['C'] as $c){ if(isset($a['post_id'])&&$c['post']!==$a['post_id']) continue; if(($a['status']??'')==='approve'&&$c['aprov']!=='1') continue; if(isset($a['parent'])&&$c['pai']!==$a['parent']) continue; $o[]=_com_obj($c); } return $o; }
function get_comment_text($c){ return $c->comment_content; }
function _com_visiveis(){ $id=_id(null); $args=apply_filters('comments_template_query_args',['order'=>'ASC']); $l=array_values(array_filter($GLOBALS['C'],fn($c)=>$c['post']===$id)); usort($l,fn($a,$b)=>$args['order']==='DESC' ? $a['h']<=>$b['h'] : $b['h']<=>$a['h']); return array_map('_com_obj',$l); }
function _com_topo(){ return array_values(array_filter(_com_visiveis(),fn($c)=>$c->comment_parent==0)); }
function comments_template(){ include THEME_DIR.'/comments.php'; }
function have_comments(){ return count(_com_topo())>0; }
function get_comment_pages_count(){ return (int)ceil(count(_com_topo())/5); }
function get_comments_pagenum_link($n){ $u=get_permalink(); $a=['cpage'=>$n>1?$n:false]; if(isset($_GET['comentarios'])) $a['comentarios']=$_GET['comentarios']; return add_query_arg($a,$u); }
function wp_list_comments($a){
  $topo=_com_topo(); $pg=max(1,(int)get_query_var('cpage')); $pg=min($pg,max(1,(int)ceil(count($topo)/5))); $todos=_com_visiveis();
  $imprimir=function($c,$prof) use(&$imprimir,$a,$todos){ call_user_func($a['callback'],$c,$a,$prof); $filhos=array_values(array_filter($todos,fn($x)=>$x->comment_parent==$c->comment_ID)); if($filhos){ echo '<div class="children">'; foreach($filhos as $f) $imprimir($f,min($prof+1,$a['max_depth'])); echo '</div>'; } echo '</div>'; };
  foreach(array_slice($topo,($pg-1)*5,5) as $c) $imprimir($c,1);
}
