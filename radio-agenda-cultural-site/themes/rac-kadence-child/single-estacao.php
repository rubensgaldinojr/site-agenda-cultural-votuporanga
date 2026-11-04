<?php
/**
 * Estação individual (Estacao.dc.html) — modelo único para todas as estações.
 *
 * - Topo: imagem destacada da estação (sem ela, placeholder), selo
 *   "No ar agora", nome, botão "Ouvir agora" e "Tocando agora" (atualizado
 *   a cada 20 s por assets/js/estacao.js, usando o endpoint do plugin).
 * - Texto: campo ACF "Texto Sobre a Estação" (texto_sobre_a_estacao); se
 *   vazio, um texto padrão curto com o nome da estação.
 * - "Outras estações": as 4 seguintes em ordem alfabética.
 * - "Para ler enquanto ouve": posts com tag/categoria de mesmo nome da
 *   estação e, se faltar, os mais recentes.
 * - "Da biblioteca": livros com categoria de livro de mesmo nome da estação
 *   e, se faltar, os mais recentes (some se não houver nenhum livro).
 *
 * Cada estação já é um gênero, então não há linha de "gênero".
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="rac-main">
<?php
while ( have_posts() ) :
    the_post();

    $id     = get_the_ID();
    $nome   = get_the_title();
    $est    = rac_estacao_por_id( $id ); // null se a estação não tem stream
    $texto  = rac_campo( 'texto_sobre_a_estacao', $id, '' );
    $outras = rac_outras_estacoes( $id, 4 );
    $posts  = rac_posts_para_estacao( $nome, 3 );
    $livros = rac_livros_para_estacao( $nome, 6 );
    ?>
    <section class="rac-hero rac-hero--estacao"<?php echo $est && $est['indice'] !== null ? ' data-indice="' . esc_attr( $est['indice'] ) . '" data-ajax="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '"' : ''; ?>>
        <div class="rac-hero__foto rac-foto"><?php echo rac_html_imagem_topo( $id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        <div class="rac-hero__fade"></div>
        <div class="rac-hero__miolo">
            <div class="rac-hero__conteudo">
                <a class="rac-hero__voltar" href="<?php echo esc_url( rac_url_pagina( 'estacoes' ) ); ?>">← Todas as estações</a>
                <span class="rac-selo-live rac-selo-live--no-ar">
                    <span class="rac-live-dot" aria-hidden="true"><i></i><i></i></span>
                    <span class="rac-selo-live__txt">No ar agora</span>
                </span>
                <h1 class="rac-hero__titulo rac-hero__titulo--estacao"><?php echo esc_html( $nome ); ?></h1>
                <p class="rac-hero__texto rac-so-desktop-bloco">Transmissão contínua, 24 horas por dia, direto de Votuporanga.</p>
                <?php if ( $est ) : ?>
                    <div class="rac-hero__acoes">
                        <button type="button" class="rac-cta-estacao" data-rac-tocar data-estacao-id="<?php echo esc_attr( $id ); ?>" aria-pressed="false">
                            <span class="rac-cta-estacao__ouvir"><span class="rac-ico-play" aria-hidden="true"></span>Ouvir agora</span>
                            <span class="rac-cta-estacao__tocando"><span class="rac-ico-pause" aria-hidden="true"><i></i><i></i></span>Tocando agora</span>
                        </button>
                        <div class="rac-agora-card">
                            <span class="rac-agora-card__rotulo">Tocando agora</span>
                            <span class="rac-agora-card__texto" data-rac-agora>Carregando…</span>
                        </div>
                    </div>
                    <span class="rac-agora-m" data-rac-agora-m>Carregando…</span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="rac-estacao-texto">
        <div class="rac-estacao-texto__inner">
            <?php if ( $texto ) : ?>
                <?php echo wp_kses_post( $texto ); ?>
            <?php else : ?>
                <p><?php echo esc_html( 'A ' . $nome . ' é uma das estações da Agenda Cultural, com uma curadoria feita à mão e tocando sem parar.' ); ?></p>
                <p>Cada faixa é escolhida por quem ouve de verdade: clássicos que atravessaram gerações ao lado de gravações novas e de artistas independentes do interior paulista.</p>
                <p>A transmissão é contínua, 24 horas por dia. Deixe tocando enquanto trabalha, lê ou cozinha.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php if ( $outras ) : ?>
    <section class="rac-outras">
        <div class="rac-secao__inner">
            <div class="rac-secao__topo">
                <h2>Outras estações</h2>
                <a href="<?php echo esc_url( rac_url_pagina( 'estacoes' ) ); ?>">Ver todas →</a>
            </div>
            <div class="rac-grade-tiles">
                <?php foreach ( $outras as $o ) : get_template_part( 'template-parts/tile-estacao', null, array( 'estacao' => $o ) ); endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ( $posts ) : ?>
    <section class="rac-ler">
        <div class="rac-secao__inner">
            <div class="rac-secao__topo rac-secao__topo--titulo">
                <div>
                    <span class="rac-eyebrow">Do blog</span>
                    <h2>Para ler enquanto ouve</h2>
                </div>
            </div>
            <div class="rac-grade-posts">
                <?php
                foreach ( $posts as $post_id ) :
                    // selinho com o nome da estação quando o post tem a tag/categoria dela (e não é só um "mais recente" de preenchimento)
                    $liga = has_term( $nome, 'post_tag', $post_id ) || has_term( $nome, 'category', $post_id );
                    get_template_part( 'template-parts/card-post', null, array( 'post' => $post_id, 'etiqueta' => $liga ? $nome : '' ) );
                endforeach;
                ?>
            </div>
            <div class="rac-secao__rodape">
                <a class="rac-botao-contorno" href="<?php echo esc_url( rac_url_pagina( 'blog' ) ); ?>">Ver todos os posts →</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <?php if ( $livros ) : ?>
    <section class="rac-biblioteca">
        <div class="rac-secao__inner">
            <div class="rac-secao__topo rac-secao__topo--titulo">
                <div>
                    <span class="rac-eyebrow">Literatura</span>
                    <h2>Da biblioteca</h2>
                </div>
                <a href="<?php echo esc_url( rac_url_pagina( 'literatura' ) ); ?>">Ver a biblioteca →</a>
            </div>
            <div class="rac-biblioteca__lista">
                <?php foreach ( $livros as $livro_id ) : ?>
                    <div class="rac-biblioteca__item"><?php get_template_part( 'template-parts/card-livro', null, array( 'livro' => $livro_id ) ); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
<?php endwhile; ?>
</main>
<?php
get_footer();
