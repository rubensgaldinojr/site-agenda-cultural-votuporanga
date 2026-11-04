<?php
/**
 * CardPost (CardPost.dc.html).
 *
 * Argumentos (get_template_part):
 *   'post'      WP_Post|int|null  o post (padrão: o post atual do loop)
 *   'etiqueta'  string            (opcional) selinho com a tag/categoria que ligou o post à estação
 *
 * Mostra imagem destacada (ou o placeholder listrado), categoria + data,
 * título em até 2 linhas e o autor com link para a página dele.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$p = get_post( isset( $args['post'] ) ? $args['post'] : null );
if ( ! $p ) return;

$categorias = get_the_category( $p->ID );
$categoria  = ( $categorias && ! is_wp_error( $categorias ) ) ? $categorias[0]->name : '';
$link       = get_permalink( $p );
$imagem     = get_the_post_thumbnail( $p->ID, 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) );
?>
<article class="rac-card-post">
    <a class="rac-card-post__imagem rac-foto stripe-cover" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true"><?php echo $imagem; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
    <div class="rac-card-post__meta">
        <?php if ( $categoria ) : ?><span class="rac-card-post__categoria"><?php echo esc_html( $categoria ); ?></span><?php endif; ?>
        <time class="rac-card-post__data" datetime="<?php echo esc_attr( get_the_date( 'c', $p ) ); ?>"><?php echo esc_html( get_the_date( 'd M Y', $p ) ); ?></time>
    </div>
    <?php if ( ! empty( $args['etiqueta'] ) ) : ?><span class="rac-card-post__etiqueta"><?php echo esc_html( $args['etiqueta'] ); ?></span><?php endif; ?>
    <a class="rac-card-post__titulo" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a>
    <span class="rac-card-post__autor">por <a href="<?php echo esc_url( get_author_posts_url( $p->post_author ) ); ?>"><?php echo esc_html( get_the_author_meta( 'display_name', $p->post_author ) ); ?></a></span>
</article>
