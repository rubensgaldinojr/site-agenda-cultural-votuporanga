<?php
/** Página não encontrada. */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="rac-main">
    <div class="rac-container">
        <h1>Página não encontrada</h1>
        <p>O endereço que você tentou abrir não existe ou foi movido. <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Voltar para a página inicial</a>.</p>
    </div>
</main>
<?php
get_footer();
