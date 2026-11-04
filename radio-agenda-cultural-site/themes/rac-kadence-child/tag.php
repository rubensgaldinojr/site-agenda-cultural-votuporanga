<?php
/**
 * Listagem de posts (tag). Veja template-parts/listagem.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="rac-main">
    <?php get_template_part( 'template-parts/listagem' ); ?>
</main>
<?php
get_footer();
