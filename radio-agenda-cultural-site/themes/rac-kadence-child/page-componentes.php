<?php
/**
 * Template Name: Componentes (apenas para teste)
 *
 * Prévia dos componentes compartilhados com os dados reais do site:
 * CardEstacao, CardEstacao compact e CardPost. Serve para conferir o visual
 * e o comportamento (play pelo card, destaque "tocando") antes de as
 * páginas definitivas existirem.
 *
 * Como usar: crie uma página com o slug "componentes" (ou escolha este
 * template) e deixe como Rascunho/Privada. A página sai dos buscadores
 * (noindex). Pode ser apagada quando as Fases 3 a 5 estiverem prontas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'wp_robots', 'wp_robots_no_robots' );

$estacoes = rac_listar_estacoes();
$posts    = get_posts( array(
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 3,
    'no_found_rows'  => true,
) );

get_header();
?>
<main id="primary" class="rac-main">
    <div class="rac-container">
        <h1>Componentes</h1>
        <p>Prévia de teste. <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a> · <a href="<?php echo esc_url( home_url( '/sobre/' ) ); ?>">Sobre</a></p>

        <h2>CardEstacao</h2>
        <?php if ( $estacoes ) : ?>
            <div id="grade-estacoes" class="rac-grade-teste">
                <?php foreach ( array_slice( $estacoes, 0, 4 ) as $e ) : get_template_part( 'template-parts/card-estacao', null, array( 'estacao' => $e ) ); endforeach; ?>
            </div>
            <h2>CardEstacao compact</h2>
            <div id="grade-compact" class="rac-grade-teste rac-grade-teste--compacta">
                <?php foreach ( array_slice( $estacoes, 4, 6 ) as $e ) : get_template_part( 'template-parts/card-estacao', null, array( 'estacao' => $e, 'compact' => true ) ); endforeach; ?>
            </div>
        <?php else : ?>
            <p>Nenhuma estação publicada com URL de stream.</p>
        <?php endif; ?>

        <?php if ( $estacoes ) : ?>
            <h2>Card de estação com imagem</h2>
            <div id="grade-img" class="rac-grade-img rac-grade-img--teste">
                <?php foreach ( array_slice( $estacoes, 0, 4 ) as $e ) : get_template_part( 'template-parts/card-estacao-imagem', null, array( 'estacao' => $e ) ); endforeach; ?>
            </div>
        <?php endif; ?>

        <h2>CardPost</h2>
        <?php if ( $posts ) : ?>
            <div id="grade-posts" class="rac-grade-teste rac-grade-teste--posts">
                <?php foreach ( $posts as $p ) : get_template_part( 'template-parts/card-post', null, array( 'post' => $p ) ); endforeach; ?>
            </div>
        <?php else : ?>
            <p>Nenhum post publicado ainda.</p>
        <?php endif; ?>
    </div>
</main>
<?php
get_footer();
