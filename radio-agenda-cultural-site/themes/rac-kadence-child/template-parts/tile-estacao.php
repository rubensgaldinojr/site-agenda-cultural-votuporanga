<?php
/**
 * Tile de estação (usado em "Outras estações" da página da estação).
 *
 * Argumentos: 'estacao' => ['id', 'nome', 'url'].
 * A imagem e o nome levam à página da estação; o botão redondo toca a
 * estação pelo player (assets/js/player.js). No mobile só o link aparece.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$e = isset( $args['estacao'] ) ? $args['estacao'] : null;
if ( empty( $e ) ) return;

$imagem = get_the_post_thumbnail( $e['id'], 'medium', array( 'alt' => '', 'loading' => 'lazy' ) );
?>
<div class="rac-tile-est" data-estacao-id="<?php echo esc_attr( $e['id'] ); ?>">
    <div class="rac-tile-est__imagem rac-foto stripe-cover">
        <a class="rac-tile-est__link" href="<?php echo esc_url( $e['url'] ); ?>" aria-label="<?php echo esc_attr( 'Abrir a página da estação ' . $e['nome'] ); ?>"><?php echo $imagem; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
        <button type="button" class="rac-tile-est__play" data-rac-tocar aria-pressed="false" aria-label="<?php echo esc_attr( 'Ouvir ' . $e['nome'] ); ?>">
            <span class="rac-ico-play" aria-hidden="true"></span>
            <span class="rac-ico-pause" aria-hidden="true"><i></i><i></i></span>
        </button>
    </div>
    <a class="rac-tile-est__nome" href="<?php echo esc_url( $e['url'] ); ?>"><?php echo esc_html( $e['nome'] ); ?></a>
</div>
