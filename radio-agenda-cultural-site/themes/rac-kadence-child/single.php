<?php
/**
 * Template genérico de post/CPT individual. O template definitivo de
 * Post (com formatos, comentários e relacionados) vem na Fase 4.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="rac-main">
    <div class="rac-container">
        <?php while ( have_posts() ) : the_post(); ?>
            <article <?php post_class( 'rac-conteudo' ); ?>>
                <h1><?php the_title(); ?></h1>
                <div class="rac-meta"><?php echo esc_html( get_the_date() ); ?> · <?php the_author_posts_link(); ?></div>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php
get_footer();
