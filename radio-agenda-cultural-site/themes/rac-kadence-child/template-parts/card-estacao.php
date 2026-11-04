<?php
/**
 * CardEstacao (CardEstacao.dc.html).
 *
 * Argumentos (get_template_part):
 *   'estacao'        array  ['id', 'nome', 'url'] (veja rac_listar_estacoes())
 *   'compact'        bool   versão compacta: o card inteiro toca a estação
 *   'mostrar_genero' bool   mostra a linha de gênero, se vier em 'genero'
 *   'sem_link'       bool   esconde o link "Ver estação" (usado na Home, "Clique para ouvir")
 *   'genero'         string opcional. Cada estação já é um gênero, então
 *                           normalmente não é usado.
 *
 * Quem toca é o player: assets/js/player.js liga o card ao player do
 * plugin e marca .is-tocando no card da estação que está tocando.
 *
 * O título NÃO é link: clicar em qualquer ponto do card toca/pausa a
 * estação, para ninguém sair da página sem querer ao tentar dar play. Quem
 * quer a página da estação usa o link "Ver estação" (só no card completo).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$e = isset( $args['estacao'] ) ? $args['estacao'] : null;
if ( empty( $e ) ) return;

$compacto = ! empty( $args['compact'] );
$genero   = ( ! empty( $args['mostrar_genero'] ) && ! empty( $args['genero'] ) ) ? $args['genero'] : '';
$classes  = 'rac-card-est' . ( $compacto ? ' rac-card-est--compact' : '' );
?>
<div class="<?php echo esc_attr( $classes ); ?>" data-estacao-id="<?php echo esc_attr( $e['id'] ); ?>"<?php echo $compacto ? ' role="button" tabindex="0" aria-label="' . esc_attr( 'Ouvir ' . $e['nome'] ) . '"' : ''; ?>>
    <div class="rac-card-est__txt">
        <span class="rac-card-est__nome"><?php echo esc_html( $e['nome'] ); ?></span>
        <?php if ( $genero ) : ?>
            <span class="rac-card-est__genero"><?php echo esc_html( $genero ); ?></span>
        <?php endif; ?>
        <div class="rac-card-est__live">
            <span class="rac-card-est__dot" aria-hidden="true"><i></i><i></i></span>
            <span class="rac-card-est__rotulo">Ao vivo</span>
            <?php if ( ! $compacto && ! empty( $e['url'] ) && empty( $args['sem_link'] ) ) : ?>
                <a class="rac-card-est__ver" href="<?php echo esc_url( $e['url'] ); ?>" aria-label="<?php echo esc_attr( 'Ver a página da estação ' . $e['nome'] ); ?>">Ver estação</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ( ! $compacto ) : ?>
        <button type="button" class="rac-card-est__play" data-rac-tocar aria-pressed="false" aria-label="<?php echo esc_attr( 'Ouvir ' . $e['nome'] ); ?>">
            <span class="rac-ico-play" aria-hidden="true"></span>
            <span class="rac-ico-pause" aria-hidden="true"><i></i><i></i></span>
        </button>
    <?php endif; ?>
</div>
