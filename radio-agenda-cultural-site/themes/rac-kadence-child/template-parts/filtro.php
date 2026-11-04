<?php
/**
 * Seletor da listagem do Blog (Categoria, Autor(a), Ordenar por). É um <details>
 * com uma lista de LINKS: funciona sem JavaScript e mantém a navegação
 * persistente do player.
 *
 * Argumentos: 'rotulo', 'opcoes' (rac_opcoes_*), 'alinhar' ('direita' abre para a esquerda).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$opcoes = isset( $args['opcoes'] ) ? $args['opcoes'] : array();
?>
<details class="rac-filtro<?php echo ( isset( $args['alinhar'] ) && $args['alinhar'] === 'direita' ) ? ' rac-filtro--direita' : ''; ?>" data-rac-filtro>
    <summary>
        <span class="rac-filtro__txt">
            <span class="rac-filtro__rotulo"><?php echo esc_html( $args['rotulo'] ); ?></span>
            <span class="rac-filtro__valor"><?php echo esc_html( rac_valor_ativo( $opcoes ) ); ?></span>
        </span>
        <span class="rac-filtro__seta" aria-hidden="true">▾</span>
    </summary>
    <ul class="rac-filtro__lista">
        <?php foreach ( $opcoes as $o ) : ?>
            <li><a href="<?php echo esc_url( $o['url'] ); ?>"<?php echo $o['ativa'] ? ' class="is-ativa" aria-current="true"' : ''; ?>><span class="rac-filtro__ok" aria-hidden="true"><?php echo $o['ativa'] ? '✓' : ''; ?></span><?php echo esc_html( $o['rotulo'] ); ?></a></li>
        <?php endforeach; ?>
    </ul>
</details>
