<?php
/**
 * Post individual (Post.dc.html) com os formatos Texto, Poesia, Vídeo, Música,
 * Tirinha, Imagem e Citação (inc/formatos.php; um arquivo por formato em
 * template-parts/formatos/). Em todos: trilha, selo do formato, título, resumo,
 * autoria, data, tempo de leitura, comentários, tags, compartilhar, caixa do autor,
 * comentários e posts relacionados.
 *
 * Este modelo atende posts. Outros tipos (livros, estações) usam os seus próprios
 * modelos ou o index.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="rac-main">
<?php
while ( have_posts() ) :
    the_post();

    $id      = get_the_ID();
    $formato = rac_formato_do_post( $id );
    $info    = rac_formatos_post();
    $badge   = isset( $info[ $formato ]['badge'] ) ? $info[ $formato ]['badge'] : '';
    $cats    = get_the_category( $id );
    $cat     = $cats ? $cats[0] : null;
    $autor   = rac_dados_autor( (int) get_the_author_meta( 'ID' ) );
    $tags    = get_the_tags( $id ) ? get_the_tags( $id ) : array();
    $n_com   = (int) get_comments_number( $id );
    $arq_formato = locate_template( 'template-parts/formatos/' . $formato . '.php' ) ? $formato : 'texto';
    ?>
    <article class="rac-post rac-post--<?php echo esc_attr( $formato ); ?>">
        <header class="rac-post__cab">
            <div class="rac-post__trilha">
                <a href="<?php echo esc_url( rac_url_blog() ); ?>">Blog</a><span aria-hidden="true">/</span>
                <?php if ( $cat ) : ?><a class="rac-post__cat" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a><?php endif; ?>
                <?php if ( $badge ) : ?><span class="rac-post__selo"><?php echo esc_html( $badge ); ?></span><?php endif; ?>
            </div>
            <h1 class="rac-post__titulo"><?php the_title(); ?></h1>
            <?php if ( has_excerpt() ) : ?><p class="rac-post__resumo"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
            <div class="rac-post__meta">
                <?php echo rac_avatar_html( $autor, 'rac-avatar--pequeno' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                <span>por <a class="rac-post__autor" href="<?php echo esc_url( $autor['url'] ); ?>"><?php echo esc_html( $autor['nome'] ); ?></a></span>
                <i aria-hidden="true"></i><span class="rac-post__data"><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
                <i aria-hidden="true"></i><span class="rac-post__leitura"><?php echo esc_html( rac_tempo_leitura( $id ) ); ?></span>
                <a class="rac-post__com" href="#comentarios" aria-label="<?php echo esc_attr( rac_titulo_comentarios( $n_com ) ); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/></svg><?php echo (int) $n_com; ?>
                </a>
            </div>
        </header>

        <?php get_template_part( 'template-parts/formatos/' . $arq_formato, null, array( 'fase' => 'topo', 'id' => $id ) ); ?>

        <div class="rac-post__miolo">
            <div class="rac-conteudo-post"><?php the_content(); ?></div>

            <?php get_template_part( 'template-parts/formatos/' . $arq_formato, null, array( 'fase' => 'rodape', 'id' => $id ) ); ?>

            <div class="rac-post__fim">
                <?php foreach ( $tags as $t ) : ?>
                    <a class="rac-tag" href="<?php echo esc_url( get_tag_link( $t ) ); ?>">#<?php echo esc_html( $t->name ); ?></a>
                <?php endforeach; ?>
                <span class="rac-post__espaco"></span>
                <?php get_template_part( 'template-parts/compartilhar', null, array( 'id' => $id ) ); ?>
            </div>
        </div>

        <?php get_template_part( 'template-parts/caixa-autor', null, array( 'autor' => $autor ) ); ?>
    </article>

    <?php if ( comments_open() || $n_com > 0 ) comments_template(); ?>

    <?php $relacionados = rac_posts_relacionados( $id, 3 ); ?>
    <?php if ( $relacionados ) : ?>
    <section class="rac-relacionados">
        <div class="rac-secao__inner">
            <div class="rac-secao__topo">
                <h2>Posts relacionados</h2>
                <a href="<?php echo esc_url( rac_url_blog() ); ?>">Ver todos →</a>
            </div>
            <div class="rac-grade-posts">
                <?php foreach ( $relacionados as $rid ) : get_template_part( 'template-parts/card-post', null, array( 'post' => $rid ) ); endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
<?php endwhile; ?>
</main>
<?php
get_footer();
