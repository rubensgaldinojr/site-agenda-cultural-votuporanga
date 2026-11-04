<?php
// Os modelos rodam dentro de uma função (como no WordPress), para que suas variáveis locais não colidam com as globais da simulação.
function f4_incluir($arquivo){ include $arquivo; }
// Rotas da Fase 4 (blog, categorias, tags, autor, post). Só para o teste. Veja README.md.
function f4_principal($ctx,$obj,$args,$paged,$tpl){
  $q=new WP_Query(); $q->principal=true; $q->ctx=$ctx; $q->q=array_merge(['post_type'=>'post','paged'=>$paged],$args);
  $GLOBALS['wp_query']=$q; $GLOBALS['ctx']=$ctx; $GLOBALS['obj']=$obj; $GLOBALS['paged']=$paged;
  if(isset($_GET['pp'])) add_filter('pre_get_posts', function($q){ $q->set('posts_per_page',(int)$_GET['pp']); }, 20);
  if($ctx!=='page') { do_action('pre_get_posts',$q); $q->consultar(); }
  if(!empty($_GET['semcom'])) $GLOBALS['sem_comentarios']=true;
  f4_incluir(THEME_DIR.'/'.$tpl);
}
function f4_pagina($u){ if(preg_match('#/page/(\d+)/?$#',$u,$m)) return [(int)$m[1], preg_replace('#page/\d+/?$#','',$u)]; return [1,$u]; }
[$pg,$base]=f4_pagina($uri);
$GLOBALS['title']='Fase 4';

if ($base==='/f4/blog/') { $GLOBALS['title']='Blog'; f4_principal('home',(object)['ID'=>2000],[],$pg,'home.php'); return; }
if ($base==='/f4/blogp/') { $GLOBALS['sem_pagina_posts']=true; $GLOBALS['title']='Blog (página comum)'; f4_principal('page',(object)['ID'=>2001],[],$pg,'page-blog.php'); return; }
if (preg_match('#^/f4/categoria/([a-z0-9-]+)/$#',$base,$m)) { $t=get_category_by_slug($m[1]); if($t){ $GLOBALS['title']=$t->name; f4_principal('category',$t,['category_name'=>$m[1]],$pg,'category.php'); return; } }
if (preg_match('#^/f4/tag/([a-z0-9-]+)/$#',$base,$m)) { $t=get_term_by('slug',$m[1],'post_tag'); if($t){ $GLOBALS['title']='#'.$t->name; f4_principal('tag',$t,['tag_id'=>$t->term_id],$pg,'tag.php'); return; } }
if (preg_match('#^/f4/autor/([a-z0-9-]+)/$#',$base,$m)) { $u=get_user_by('slug',$m[1]); if($u){ $GLOBALS['title']=$u->display_name; f4_principal('author',$u,['author_name'=>$m[1]],$pg,'author.php'); return; } }
if (preg_match('#^/f4/post-(\d+)/$#',$uri,$m) && isset($GLOBALS['P'][(int)$m[1]]) && _publicado((int)$m[1])) {
  $id=(int)$m[1]; $q=new WP_Query(); $q->principal=true; $q->ctx='single'; $q->posts=[_post_obj($id)]; $q->post_count=1; $q->found_posts=1;
  $GLOBALS['wp_query']=$q; $GLOBALS['ctx']='single'; $GLOBALS['obj']=_post_obj($id); $GLOBALS['title']=$GLOBALS['P'][$id]['t'];
  if(!empty($_GET['semcom'])) $GLOBALS['sem_comentarios']=true;
  if(!empty($_GET['semrel'])) { $GLOBALS['P']=array_intersect_key($GLOBALS['P'],[$id=>1]); }
  f4_incluir(THEME_DIR.'/single.php'); return;
}
http_response_code(404); $GLOBALS['title']='404'; f4_incluir(THEME_DIR.'/404.php');
