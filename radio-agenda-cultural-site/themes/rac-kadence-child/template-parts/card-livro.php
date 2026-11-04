<?php
/**
 * Card de livro (Home e página da estação): capa, título e autor.
 *
 * Argumentos: 'livro' => ID do livro (CPT "livro" do plugin de catálogo).
 * Ainda não existe página individual de livro; o card leva à página
 * Literatura (o catálogo, onde está o botão de baixar).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$livro = isset( $args['livro'] ) ? get_post( $args['livro'] ) : null;
if ( ! $livro ) return;

$capa  = get_the_post_thumbnail( $livro->ID, 'medium', array( 'alt' => '', 'loading' => 'lazy' ) );
$autor = get_the_author_meta( 'display_name', $livro->post_author );
?>
<a class="rac-livro" href="<?php echo esc_url( rac_url_pagina( 'literatura' ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $livro ) . ( $autor ? ', ' . $autor : '' ) . ' — abrir a biblioteca' ); ?>">
    <span class="rac-livro__capa rac-foto stripe-cover"><?php echo $capa; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
    <span class="rac-livro__titulo"><?php echo esc_html( get_the_title( $livro ) ); ?></span>
    <?php if ( $autor ) : ?><span class="rac-livro__autor"><?php echo esc_html( $autor ); ?></span><?php endif; ?>
</a>
