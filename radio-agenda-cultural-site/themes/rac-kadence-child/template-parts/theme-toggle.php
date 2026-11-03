<?php
/**
 * Chave de tema claro/escuro (ThemeToggle.dc.html).
 *
 * Argumentos (via get_template_part):
 *   'rotulos' => bool  mostra os textos "Claro" / "Escuro" (padrão: true)
 *
 * A aparência (posição do botão, ícone, cor dos rótulos) é decidida só
 * pelo CSS a partir de html[data-theme]; o JS (assets/js/rac.js) troca o
 * tema e mantém o aria-checked de todas as chaves da página em sincronia.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$rotulos = isset( $args['rotulos'] ) ? (bool) $args['rotulos'] : true;
?>
<button type="button" class="rac-toggle<?php echo $rotulos ? '' : ' rac-toggle--compacto'; ?>" role="switch" aria-checked="false" aria-label="Alternar entre modo claro e modo escuro" title="Alternar entre modo claro e modo escuro">
    <?php if ( $rotulos ) : ?><span class="rac-toggle__rotulo rac-toggle__rotulo--claro">Claro</span><?php endif; ?>
    <span class="rac-toggle__trilho">
        <svg class="rac-toggle__icone rac-toggle__icone--sol" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path></svg>
        <svg class="rac-toggle__icone rac-toggle__icone--lua" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"></path></svg>
        <span class="rac-toggle__botao">
            <svg class="rac-toggle__atual rac-toggle__atual--sol" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path></svg>
            <svg class="rac-toggle__atual rac-toggle__atual--lua" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"></path></svg>
        </span>
    </span>
    <?php if ( $rotulos ) : ?><span class="rac-toggle__rotulo rac-toggle__rotulo--escuro">Escuro</span><?php endif; ?>
</button>
