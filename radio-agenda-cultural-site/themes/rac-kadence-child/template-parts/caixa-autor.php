<?php
/** Caixa "Escrito por" no fim do post. Argumentos: 'autor' (rac_dados_autor). */
if ( ! defined( 'ABSPATH' ) ) exit;
$a = $args['autor'];
?>
<aside class="rac-caixa-autor" aria-label="Sobre quem escreveu">
    <?php echo rac_avatar_html( $a, 'rac-avatar--media' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <div class="rac-caixa-autor__texto">
        <span class="rac-caixa-autor__rotulo">Escrito por</span>
        <a class="rac-caixa-autor__nome" href="<?php echo esc_url( $a['url'] ); ?>"><?php echo esc_html( $a['nome'] ); ?></a>
        <?php if ( $a['bio'] ) : ?><p><?php echo esc_html( rac_paragrafos( $a['bio'] )[0] ); ?></p><?php endif; ?>
        <a class="rac-caixa-autor__ver" href="<?php echo esc_url( $a['url'] ); ?>">Ver todos os posts →</a>
    </div>
</aside>
