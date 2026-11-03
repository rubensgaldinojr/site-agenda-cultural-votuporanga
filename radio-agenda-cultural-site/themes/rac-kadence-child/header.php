<?php
/**
 * Cabeçalho do site (SiteHeader.dc.html).
 *
 * Substitui o header do Kadence. Fica FORA do "#primary", então não é
 * trocado pela navegação AJAX do plugin racr-player-radio; por isso o
 * item de menu ativo é atualizado por JS (assets/js/rac.js).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$itens_desktop = rac_marcar_item_ativo( rac_itens_menu() );
$itens_mobile  = rac_marcar_item_ativo( rac_itens_menu_completo() );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="rac-skip" href="#primary">Ir para o conteúdo</a>

<div class="rac-site">
<header class="rac-header" id="rac-header">
    <div class="rac-header__barra">
        <a class="rac-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — página inicial">
            <?php echo rac_logo_html( true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        </a>

        <!-- Desktop: menu + divisor + chave de tema com rótulos -->
        <div class="rac-header__desktop">
            <nav class="rac-nav" aria-label="Menu principal">
                <?php foreach ( $itens_desktop as $item ) : ?>
                    <a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['ativo'] ? ' class="is-ativo" aria-current="page"' : ''; ?>><?php echo esc_html( $item['label'] ); ?></a>
                <?php endforeach; ?>
            </nav>
            <span class="rac-header__divisor" aria-hidden="true"></span>
            <?php get_template_part( 'template-parts/theme-toggle', null, array( 'rotulos' => true ) ); ?>
        </div>

        <!-- Mobile: chave compacta (só ícones) + botão ☰ -->
        <div class="rac-header__mobile">
            <?php get_template_part( 'template-parts/theme-toggle', null, array( 'rotulos' => false ) ); ?>
            <button type="button" class="rac-menu-btn" id="rac-menu-btn" aria-label="Menu" aria-expanded="false" aria-controls="rac-menu-mobile">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <!-- Mobile: painel do menu (inclui Contato e "Aparência do site") -->
    <div class="rac-menu-mobile" id="rac-menu-mobile" hidden>
        <nav aria-label="Menu principal (mobile)">
            <?php foreach ( $itens_mobile as $item ) : ?>
                <a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['ativo'] ? ' class="is-ativo" aria-current="page"' : ''; ?>><?php echo esc_html( $item['label'] ); ?><span aria-hidden="true">→</span></a>
            <?php endforeach; ?>
        </nav>
        <div class="rac-menu-mobile__aparencia">
            <span>Aparência do site</span>
            <?php get_template_part( 'template-parts/theme-toggle', null, array( 'rotulos' => true ) ); ?>
        </div>
    </div>
</header>
