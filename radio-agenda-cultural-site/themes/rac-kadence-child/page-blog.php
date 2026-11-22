<?php
/**
 * Template Name: Blog (listagem)
 *
 * Usado quando o Blog é uma página comum (slug "blog") e não a "Página de posts"
 * de Configurações → Leitura. Faz a mesma listagem, com uma consulta própria.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="rac-main">
    <?php get_template_part( 'template-parts/listagem' ); ?>
</main>
<?php
get_footer();
