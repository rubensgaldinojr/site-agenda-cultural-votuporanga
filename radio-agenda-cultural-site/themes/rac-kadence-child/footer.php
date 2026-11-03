<?php
/**
 * Rodapé do site (SiteFooter.dc.html).
 *
 * Os textos vêm da Personalização do WordPress (inc/customizer.php) e a
 * grade "Todas as estações" vem do CPT. Substitui o footer do Kadence.
 *
 * wp_footer() fica depois de </div.rac-site>: é ali que o plugin
 * racr-player-radio injeta o player, fora do "#primary".
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$itens_menu = rac_itens_menu_completo();
$estacoes   = rac_listar_estacoes();
$email      = rac_opcao( 'rac_rodape_email' );
$sociais    = array(
    array( 'sigla' => 'IG', 'nome' => 'Instagram', 'url' => rac_opcao( 'rac_rodape_instagram' ) ),
    array( 'sigla' => 'FB', 'nome' => 'Facebook',  'url' => rac_opcao( 'rac_rodape_facebook' ) ),
    array( 'sigla' => 'YT', 'nome' => 'YouTube',   'url' => rac_opcao( 'rac_rodape_youtube' ) ),
);
$sociais = array_filter( $sociais, function ( $s ) { return ! empty( $s['url'] ); } );
?>
<footer class="rac-footer">
    <div class="rac-footer__grade">
        <div class="rac-footer__marca">
            <a class="rac-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — página inicial"><?php echo rac_logo_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
            <?php if ( rac_opcao( 'rac_rodape_texto' ) ) : ?>
                <p class="rac-footer__texto"><?php echo esc_html( rac_opcao( 'rac_rodape_texto' ) ); ?></p>
            <?php endif; ?>
            <?php if ( $email ) : ?>
                <a class="rac-footer__email" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( $email ); ?></a>
            <?php endif; ?>
        </div>

        <?php if ( $sociais ) : ?>
        <div class="rac-footer__sociais">
            <?php foreach ( $sociais as $s ) : ?>
                <a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $s['nome'] ); ?>"><?php echo esc_html( $s['sigla'] ); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <nav class="rac-footer__menu" aria-label="Menu do rodapé">
            <span class="rac-footer__titulo">Menu</span>
            <?php foreach ( $itens_menu as $item ) : ?>
                <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ( $estacoes ) : ?>
        <nav class="rac-footer__estacoes" aria-label="Todas as estações">
            <span class="rac-footer__titulo">Todas as estações</span>
            <div class="rac-footer__estacoes-grade">
                <?php foreach ( $estacoes as $e ) : ?>
                    <a href="<?php echo esc_url( $e['url'] ); ?>"><?php echo esc_html( $e['nome'] ); ?></a>
                <?php endforeach; ?>
            </div>
        </nav>
        <?php endif; ?>

        <p class="rac-footer__copy">© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( rac_opcao( 'rac_rodape_copy' ) ); ?></p>
    </div>
</footer>
</div><!-- .rac-site -->

<?php wp_footer(); ?>
</body>
</html>
