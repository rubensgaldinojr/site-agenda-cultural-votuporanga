<?php
/**
 * Listagem de posts (Blog.dc.html): serve a página Blog, as categorias (inclusive
 * Podcasts) e as tags. 6 posts por página; filtros por links (Categoria, Autor(a),
 * Ordenar por), etiquetas removíveis e paginação numerada.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$consulta = rac_consulta_listagem();
$cab      = rac_cabecalho_listagem();
$f        = rac_filtros_ativos();
$pag      = rac_paginacao( $consulta );
$n_filtros = count( $f['etiquetas'] );
?>
<section class="rac-blog-topo">
    <div class="rac-blog-topo__inner">
        <span class="rac-eyebrow"><?php echo esc_html( $cab['eyebrow'] ); ?></span>
        <h1><?php echo esc_html( $cab['titulo'] ); ?></h1>
        <p><?php echo esc_html( $cab['texto'] ); ?></p>
    </div>
</section>

<div class="rac-filtros-faixa">
    <div class="rac-filtros" data-rac-filtros>
        <button type="button" class="rac-filtros__abrir" data-rac-filtros-abrir aria-expanded="false">
            Filtros<?php if ( $n_filtros ) : ?> <span class="rac-filtros__n"><?php echo (int) $n_filtros; ?></span><?php endif; ?>
        </button>
        <div class="rac-filtros__painel">
            <?php get_template_part( 'template-parts/filtro', null, array( 'rotulo' => 'Categoria', 'opcoes' => rac_opcoes_categoria() ) ); ?>
            <?php get_template_part( 'template-parts/filtro', null, array( 'rotulo' => 'Autor(a)', 'opcoes' => rac_opcoes_autor() ) ); ?>
            <?php if ( $f['tem'] ) : ?><a class="rac-filtros__limpar" href="<?php echo esc_url( $f['limpar'] ); ?>">Limpar filtros</a><?php endif; ?>
        </div>
        <span class="rac-filtros__espaco"></span>
        <?php get_template_part( 'template-parts/filtro', null, array( 'rotulo' => 'Ordenar por', 'opcoes' => rac_opcoes_ordem(), 'alinhar' => 'direita' ) ); ?>
    </div>
</div>

<div class="rac-blog-info">
    <span class="rac-blog-info__contagem" data-rac="contagem-posts"><?php echo esc_html( rac_rotulo_posts( (int) $consulta->found_posts ) ); ?></span>
    <?php foreach ( $f['etiquetas'] as $e ) : ?>
        <a class="rac-etiqueta-filtro" href="<?php echo esc_url( $e['url'] ); ?>" aria-label="<?php echo esc_attr( 'Remover o filtro ' . $e['rotulo'] ); ?>"><?php echo esc_html( $e['rotulo'] ); ?> <span aria-hidden="true">✕</span></a>
    <?php endforeach; ?>
</div>

<section class="rac-blog-lista">
    <?php if ( $consulta->have_posts() ) : ?>
        <div class="rac-blog-grade">
            <?php while ( $consulta->have_posts() ) : $consulta->the_post(); get_template_part( 'template-parts/card-post', null, array( 'post' => get_the_ID() ) ); endwhile; ?>
        </div>
        <?php wp_reset_postdata(); ?>

        <?php if ( $pag['total'] > 1 ) : ?>
        <nav class="rac-paginacao" aria-label="Páginas">
            <?php if ( $pag['anterior'] ) : ?><a class="rac-paginacao__seta" href="<?php echo esc_url( $pag['anterior'] ); ?>" aria-label="Página anterior">‹</a><?php else : ?><span class="rac-paginacao__seta is-off" aria-hidden="true">‹</span><?php endif; ?>
            <?php foreach ( $pag['paginas'] as $p ) : ?>
                <a class="rac-paginacao__n<?php echo $p['ativa'] ? ' is-ativa' : ''; ?>" href="<?php echo esc_url( $p['url'] ); ?>"<?php echo $p['ativa'] ? ' aria-current="page"' : ''; ?> aria-label="<?php echo esc_attr( 'Página ' . $p['n'] ); ?>"><?php echo (int) $p['n']; ?></a>
            <?php endforeach; ?>
            <?php if ( $pag['proxima'] ) : ?><a class="rac-paginacao__seta" href="<?php echo esc_url( $pag['proxima'] ); ?>" aria-label="Próxima página">›</a><?php else : ?><span class="rac-paginacao__seta is-off" aria-hidden="true">›</span><?php endif; ?>
        </nav>
        <?php endif; ?>
    <?php else : ?>
        <div class="rac-blog-vazio">
            <p>Nenhum post encontrado<?php echo $f['tem'] ? ' com esses filtros' : ''; ?>.</p>
            <?php if ( $f['tem'] ) : ?><a class="rac-botao-contorno" href="<?php echo esc_url( $f['limpar'] ); ?>">Limpar filtros</a><?php endif; ?>
        </div>
    <?php endif; ?>
</section>
