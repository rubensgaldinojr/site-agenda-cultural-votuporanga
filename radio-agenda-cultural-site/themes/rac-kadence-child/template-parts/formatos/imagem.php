<?php
/** Formato Imagem: foto grande (imagem destacada) com a legenda da imagem e o crédito. */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( $args['fase'] !== 'topo' || ! has_post_thumbnail( $args['id'] ) ) return;
$legenda = wp_get_attachment_caption( get_post_thumbnail_id( $args['id'] ) );
$credito = trim( (string) rac_campo( 'imagem_credito', $args['id'], '' ) );
?>
<figure class="rac-post__foto-grande">
    <div class="rac-post__foto-grande-img rac-foto"><?php echo get_the_post_thumbnail( $args['id'], 'full', array( 'alt' => $legenda ? $legenda : '', 'loading' => 'eager', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <?php if ( $legenda || $credito ) : ?>
        <figcaption><span><?php echo esc_html( $legenda ); ?></span><?php if ( $credito ) : ?><span class="rac-post__credito">Foto: <?php echo esc_html( $credito ); ?></span><?php endif; ?></figcaption>
    <?php endif; ?>
</figure>
