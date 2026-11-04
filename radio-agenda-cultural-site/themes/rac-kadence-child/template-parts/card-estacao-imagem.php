<?php
/**
 * Card de estação com imagem (usado na página de Estações).
 *
 * Imagem destacada da estação ao fundo (sem ela, o placeholder do tema),
 * selo "Ao vivo", nome, trecho de 1–2 linhas do "Texto Sobre a Estação",
 * link "Ver estação" e botão de play. Texto branco sobre um escurecimento
 * (igual nos dois temas). Clicar em qualquer ponto do card toca/pausa a
 * estação pelo player (assets/js/player.js), menos no link "Ver estação".
 *
 * Argumentos:
 *   'estacao' => ['id', 'nome', 'url']
 *   'variante' => 'destaque' (Home: mais baixo, sem trecho, selo de destaque no lugar de "Ao vivo")
 *   'badge' => texto do selo de destaque (ex.: "Mais ouvida #1")
 *   'badge_tipo' => 'descoberta' para o selo laranja e o contorno da "Descoberta do editor"
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$e = isset( $args['estacao'] ) ? $args['estacao'] : null;
if ( empty( $e ) ) return;

$destaque = isset( $args['variante'] ) && $args['variante'] === 'destaque';
$badge    = isset( $args['badge'] ) ? $args['badge'] : '';
$descob   = isset( $args['badge_tipo'] ) && $args['badge_tipo'] === 'descoberta';
$trecho   = $destaque ? '' : rac_trecho_estacao( $e['id'], 18 );
$classes  = 'rac-card-img' . ( $destaque ? ' rac-card-img--destaque' : '' ) . ( $descob ? ' rac-card-img--descoberta' : '' );
?>
<article class="<?php echo esc_attr( $classes ); ?>" data-estacao-id="<?php echo esc_attr( $e['id'] ); ?>">
    <div class="rac-card-img__foto rac-foto"><?php echo rac_html_imagem_estacao( $e['id'], 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <div class="rac-card-img__scrim" aria-hidden="true"></div>

    <?php if ( $badge ) : ?>
        <span class="rac-card-img__badge"><?php echo esc_html( $badge ); ?></span>
    <?php else : ?>
        <span class="rac-card-img__live">
            <span class="rac-card-img__dot" aria-hidden="true"><i></i><i></i></span>
            <span class="rac-card-img__rotulo">Ao vivo</span>
        </span>
    <?php endif; ?>

    <div class="rac-card-img__corpo">
        <h3 class="rac-card-img__nome"><?php echo esc_html( $e['nome'] ); ?></h3>
        <?php if ( $trecho ) : ?>
            <p class="rac-card-img__trecho"><?php echo esc_html( $trecho ); ?></p>
        <?php endif; ?>
        <div class="rac-card-img__rodape">
            <a class="rac-card-img__ver" href="<?php echo esc_url( $e['url'] ); ?>" aria-label="<?php echo esc_attr( 'Ver a página da estação ' . $e['nome'] ); ?>">Ver estação</a>
            <button type="button" class="rac-card-img__play" data-rac-tocar aria-pressed="false" aria-label="<?php echo esc_attr( 'Ouvir ' . $e['nome'] ); ?>">
                <span class="rac-ico-play" aria-hidden="true"></span>
                <span class="rac-ico-pause" aria-hidden="true"><i></i><i></i></span>
            </button>
        </div>
    </div>
</article>
