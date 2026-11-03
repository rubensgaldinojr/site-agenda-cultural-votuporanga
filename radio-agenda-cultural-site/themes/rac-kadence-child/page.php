<?php
/**
 * Template genérico de página. Substituído por templates dedicados
 * (Home, Estações, Sobre, Contato...) nas fases seguintes.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="rac-main">
    <div class="rac-container">
        <?php while ( have_posts() ) : the_post(); ?>
            <article <?php post_class( 'rac-conteudo' ); ?>>
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php
get_footer();
