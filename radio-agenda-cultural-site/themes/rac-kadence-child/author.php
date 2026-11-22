<?php
/**
 * Página do autor (Autor.dc.html): foto com a bio contornando, papel, "escreve
 * desde", balão de "Citação favorita" e a grade de posts com "Carregar mais".
 * Os dados vêm do perfil do usuário (campos "Página do autor").
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wp_query;
$autor_obj = get_queried_object();
$a         = rac_dados_autor( $autor_obj->ID );
$bio       = rac_paragrafos( $a['bio'] );
$total     = (int) $wp_query->found_posts;
$pagina    = max( 1, (int) get_query_var( 'paged' ) );
$maximo    = (int) $wp_query->max_num_pages;

get_header();
?>
<main id="primary" class="rac-main">
    <section class="rac-autor-topo">
        <div class="rac-autor-topo__grade">
            <div class="rac-autor-topo__texto">
                <?php echo rac_avatar_html( $a, 'rac-avatar--grande' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                <?php if ( $a['papel'] ) : ?><span class="rac-eyebrow rac-eyebrow--bloco"><?php echo esc_html( $a['papel'] ); ?></span><?php endif; ?>
                <h1><?php echo esc_html( $a['nome'] ); ?></h1>
                <?php foreach ( $bio as $p ) : ?><p><?php echo esc_html( $p ); ?></p><?php endforeach; ?>
                <div class="rac-autor-topo__meta">
                    <span><?php echo esc_html( rac_rotulo_posts( $total ) ); ?></span>
                    <?php if ( $a['desde'] ) : ?><i aria-hidden="true"></i><span><?php echo esc_html( $a['desde'] ); ?></span><?php endif; ?>
                </div>
            </div>
            <?php if ( $a['citacao'] ) : ?>
            <figure class="rac-autor-citacao">
                <span class="rac-autor-citacao__ponta" aria-hidden="true"></span>
                <span class="rac-autor-citacao__rotulo">Citação favorita</span>
                <span class="rac-autor-citacao__aspas" aria-hidden="true">“</span>
                <blockquote><?php echo esc_html( $a['citacao'] ); ?></blockquote>
                <?php if ( $a['citacao_de'] ) : ?><figcaption>— <?php echo esc_html( $a['citacao_de'] ); ?></figcaption><?php endif; ?>
            </figure>
            <?php endif; ?>
        </div>
    </section>

    <section class="rac-autor-posts">
        <div class="rac-autor-posts__inner">
            <h2>Posts de <?php echo esc_html( $a['primeiro'] ); ?></h2>
            <?php if ( have_posts() ) : ?>
                <div class="rac-blog-grade" data-rac-mais-lista>
                    <?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/card-post', null, array( 'post' => get_the_ID() ) ); endwhile; ?>
                </div>
                <?php if ( $pagina < $maximo ) : ?>
                    <div class="rac-autor-posts__mais">
                        <a class="rac-botao-contorno" href="<?php echo esc_url( get_pagenum_link( $pagina + 1 ) ); ?>" data-rac-mais data-racr-ignorar>Carregar mais posts</a>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <p class="rac-vazio">Ainda não há posts desta pessoa.</p>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
get_footer();
