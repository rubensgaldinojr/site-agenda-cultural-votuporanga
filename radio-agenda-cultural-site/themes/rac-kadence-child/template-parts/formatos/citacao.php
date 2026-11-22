<?php
/** Formato Citação: a frase em destaque, com autor e obra. */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( $args['fase'] !== 'topo' ) return;
$texto = trim( (string) rac_campo( 'citacao_texto', $args['id'], '' ) );
if ( $texto === '' ) return;
$autor = trim( (string) rac_campo( 'citacao_autor', $args['id'], '' ) );
$obra  = trim( (string) rac_campo( 'citacao_obra', $args['id'], '' ) );
?>
<figure class="rac-post__citacao">
    <span class="rac-post__citacao-aspas" aria-hidden="true">“</span>
    <blockquote><?php echo esc_html( $texto ); ?></blockquote>
    <?php if ( $autor || $obra ) : ?>
        <figcaption><?php if ( $autor ) : ?><span class="rac-post__citacao-autor"><?php echo esc_html( $autor ); ?></span><?php endif; ?><?php if ( $obra ) : ?><span class="rac-post__citacao-obra"><?php echo esc_html( $obra ); ?></span><?php endif; ?></figcaption>
    <?php endif; ?>
</figure>
