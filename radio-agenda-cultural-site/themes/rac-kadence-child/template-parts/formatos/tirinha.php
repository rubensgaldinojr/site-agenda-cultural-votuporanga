<?php
/**
 * Formato Tirinha: as imagens e o texto vêm do editor (na ordem que você colocar;
 * se não houver imagem no texto, usa a imagem destacada). Depois: direitos/referência
 * (opcionais) e a navegação para a tirinha anterior e a próxima.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$id = $args['id'];

if ( $args['fase'] === 'topo' ) {
    if ( has_post_thumbnail( $id ) && stripos( (string) get_post_field( 'post_content', $id ), '<img' ) === false ) {
        echo '<figure class="rac-post__tira rac-foto">' . get_the_post_thumbnail( $id, 'large', array( 'alt' => '', 'loading' => 'eager' ) ) . '</figure>'; // phpcs:ignore WordPress.Security.EscapeOutput
    }
    return;
}

$partes = array_filter( array(
    trim( (string) rac_campo( 'tirinha_autor', $id, '' ) ),
    trim( (string) rac_campo( 'tirinha_ano', $id, '' ) ),
    trim( (string) rac_campo( 'tirinha_fonte', $id, '' ) ),
), 'strlen' );
$anterior = rac_tirinha_vizinha( $id, 'anterior' );
$proxima  = rac_tirinha_vizinha( $id, 'proxima' );
?>
<?php if ( $partes ) : ?>
    <p class="rac-tira-direitos"><b>Direitos/referência:</b> <?php echo esc_html( implode( ' · ', $partes ) ); ?></p>
<?php endif; ?>
<nav class="rac-tira-nav" aria-label="Outras tirinhas">
    <?php if ( $anterior ) : ?><a class="rac-botao-claro" href="<?php echo esc_url( get_permalink( $anterior ) ); ?>">‹ Tirinha anterior</a><?php else : ?><span></span><?php endif; ?>
    <span class="rac-tira-nav__num">Tirinha nº <?php echo (int) rac_tirinha_numero( $id ); ?> · <?php echo esc_html( get_the_date( 'd M Y', $id ) ); ?></span>
    <?php if ( $proxima ) : ?><a class="rac-botao-claro" href="<?php echo esc_url( get_permalink( $proxima ) ); ?>">Próxima tirinha ›</a><?php else : ?><span></span><?php endif; ?>
</nav>
