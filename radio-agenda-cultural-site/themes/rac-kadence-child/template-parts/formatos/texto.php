<?php
/** Formato Texto/Crônica: imagem de destaque com legenda (se houver). */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( $args['fase'] !== 'topo' || ! has_post_thumbnail( $args['id'] ) ) return;
$legenda = wp_get_attachment_caption( get_post_thumbnail_id( $args['id'] ) );
?>
<figure class="rac-post__destaque">
    <div class="rac-post__destaque-img rac-foto"><?php echo get_the_post_thumbnail( $args['id'], 'large', array( 'alt' => '', 'loading' => 'eager', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <?php if ( $legenda ) : ?><figcaption><?php echo esc_html( $legenda ); ?></figcaption><?php endif; ?>
</figure>
