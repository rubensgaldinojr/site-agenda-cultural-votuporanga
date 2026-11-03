<?php
/**
 * Template genérico (listagens). Será substituído pelos templates
 * definitivos nas Fases 3 a 5. Existe agora para o tema funcionar de
 * ponta a ponta e permitir testar a navegação persistente.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="rac-main">
    <div class="rac-container">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <article <?php post_class( 'rac-item-lista' ); ?>>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="rac-meta"><?php echo esc_html( get_the_date() ); ?></div>
                    <?php the_excerpt(); ?>
                </article>
            <?php endwhile; ?>
            <?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
        <?php else : ?>
            <h1>Nada por aqui</h1>
            <p>Não encontramos conteúdo para esta página.</p>
        <?php endif; ?>
    </div>
</main>
<?php
get_footer();
