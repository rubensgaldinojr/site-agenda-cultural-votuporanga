<?php
/**
 * Página inicial (Home v4 Claro.dc.html).
 *
 * Vale para a página definida como "Página inicial" em Configurações → Leitura.
 * Textos e fotos: campos ACF "Textos da página inicial" (vazios = texto
 * provisório do mockup); imagem do topo = Imagem destacada da página.
 *
 * Fase 3B-1: topo, destaques, estações em destaque, todas as estações e
 * "Sobre nós". Blog, Literatura, Podcasts e Recados vêm na 3B-2.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$pagina_id = get_queried_object_id();
$estacoes  = rac_listar_estacoes_alfabetica();
$total     = count( $estacoes );
$top       = rac_estacoes_mais_ouvidas( 2 );
$pick      = rac_estacao_descoberta( wp_list_pluck( $top, 'id' ), $pagina_id );
$primeira  = $top ? $top[0] : null; // "Ouvir agora" toca a mais ouvida

$t = function ( $nome ) use ( $pagina_id ) { return rac_home_texto( $nome, $pagina_id ); };

$fundador_nome = $t( 'home_fundador_nome' );
$foto_fundador = (int) rac_campo( 'home_fundador_foto', $pagina_id, 0 );
$foto_sobre    = (int) rac_campo( 'home_sobre_foto', $pagina_id, 0 );
$rotulo_total  = $total === 1 ? '1 estação' : $total . ' estações';

get_header();
?>
<main id="primary" class="rac-main">

    <!-- Topo -->
    <section class="rac-home-hero">
        <div class="rac-home-hero__foto rac-foto"><?php echo rac_html_imagem_topo( $pagina_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        <div class="rac-home-hero__fade"></div>
        <div class="rac-home-hero__grade">
            <div class="rac-home-hero__texto">
                <span class="rac-home-selo"><i aria-hidden="true"></i><?php echo esc_html( $t( 'home_hero_selo' ) ); ?></span>
                <h1 class="rac-home-hero__titulo"><?php echo esc_html( $t( 'home_hero_titulo' ) ); ?></h1>
                <p class="rac-home-hero__paragrafo"><?php echo esc_html( $t( 'home_hero_texto' ) ); ?></p>
                <div class="rac-home-hero__botoes">
                    <?php if ( $primeira ) : ?>
                        <button type="button" class="rac-cta-estacao" data-rac-tocar data-estacao-id="<?php echo esc_attr( $primeira['id'] ); ?>" aria-pressed="false">
                            <span class="rac-cta-estacao__ouvir"><span class="rac-ico-play" aria-hidden="true"></span>Ouvir agora</span>
                            <span class="rac-cta-estacao__tocando"><span class="rac-ico-pause" aria-hidden="true"><i></i><i></i></span>Tocando agora</span>
                        </button>
                    <?php endif; ?>
                    <a class="rac-botao-claro" href="<?php echo esc_url( rac_url_pagina( 'estacoes' ) ); ?>">Ver todas as estações</a>
                </div>
            </div>
            <div class="rac-home-hero__lado">
                <div class="rac-fundador">
                    <span class="rac-fundador__avatar">
                        <?php if ( $foto_fundador ) : ?>
                            <?php echo wp_get_attachment_image( $foto_fundador, 'thumbnail', false, array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                        <?php else : ?>
                            <b aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( $fundador_nome, 0, 1 ) ) ); ?></b>
                        <?php endif; ?>
                    </span>
                    <span class="rac-fundador__texto">
                        <span class="rac-fundador__nome"><?php echo esc_html( $fundador_nome ); ?> <span>· <?php echo esc_html( $t( 'home_fundador_papel' ) ); ?></span></span>
                        <span class="rac-fundador__bio"><?php echo esc_html( $t( 'home_fundador_texto' ) ); ?> <a href="<?php echo esc_url( rac_url_pagina( 'sobre' ) ); ?>">Conheça →</a></span>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Destaques -->
    <section class="rac-destaques">
        <div class="rac-destaques__grade">
            <div class="rac-destaque">
                <span class="rac-destaque__kicker">Estações</span>
                <span class="rac-destaque__titulo"><?php echo esc_html( $rotulo_total . ' no ar, 24h por dia' ); ?></span>
                <span class="rac-destaque__texto"><?php echo esc_html( $t( 'home_destaque1_texto' ) ); ?></span>
            </div>
            <div class="rac-destaque">
                <span class="rac-destaque__kicker">Literatura</span>
                <span class="rac-destaque__titulo"><?php echo esc_html( $t( 'home_destaque2_titulo' ) ); ?></span>
                <span class="rac-destaque__texto"><?php echo esc_html( $t( 'home_destaque2_texto' ) ); ?></span>
            </div>
            <div class="rac-destaque">
                <span class="rac-destaque__kicker">Blog</span>
                <span class="rac-destaque__titulo"><?php echo esc_html( $t( 'home_destaque3_titulo' ) ); ?></span>
                <span class="rac-destaque__texto"><?php echo esc_html( $t( 'home_destaque3_texto' ) ); ?></span>
            </div>
        </div>
    </section>

    <!-- Estações -->
    <?php if ( $estacoes ) : ?>
    <section class="rac-home-estacoes">
        <div class="rac-home-estacoes__topo">
            <h2>Estações em destaque</h2>
            <span><?php echo esc_html( $t( 'home_estacoes_subtitulo' ) ); ?></span>
        </div>

        <div class="rac-home-destaques-estacoes">
            <?php foreach ( $top as $i => $e ) : get_template_part( 'template-parts/card-estacao-imagem', null, array( 'estacao' => $e, 'variante' => 'destaque', 'badge' => 'Mais ouvida #' . ( $i + 1 ) ) ); endforeach; ?>
            <?php if ( $pick ) : get_template_part( 'template-parts/card-estacao-imagem', null, array( 'estacao' => $pick, 'variante' => 'destaque', 'badge' => 'Descoberta do editor', 'badge_tipo' => 'descoberta' ) ); endif; ?>
        </div>

        <div class="rac-todas">
            <div class="rac-todas__topo">
                <span class="rac-todas__titulo">Todas as estações <span>· <?php echo esc_html( $rotulo_total ); ?></span></span>
                <span class="rac-todas__dica">Clique para ouvir</span>
            </div>
            <div class="rac-todas__grade rac-todas__grade--d">
                <?php foreach ( $estacoes as $e ) : get_template_part( 'template-parts/card-estacao', null, array( 'estacao' => $e, 'sem_link' => true ) ); endforeach; ?>
            </div>
            <div class="rac-todas__grade rac-todas__grade--m">
                <?php foreach ( $estacoes as $e ) : get_template_part( 'template-parts/card-estacao', null, array( 'estacao' => $e, 'compact' => true ) ); endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Sobre nós -->
    <section class="rac-home-sobre">
      <div class="rac-home-sobre__inner">
        <div class="rac-home-sobre__foto rac-foto">
            <?php
            if ( $foto_sobre ) {
                echo wp_get_attachment_image( $foto_sobre, 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
            } else {
                echo '<img src="' . esc_url( rac_url_placeholder() ) . '" alt="" width="1200" height="675" loading="lazy" decoding="async">';
            }
            ?>
        </div>
        <div class="rac-home-sobre__texto">
            <span class="rac-eyebrow">Sobre nós</span>
            <h2><?php echo esc_html( $t( 'home_sobre_titulo' ) ); ?></h2>
            <p><?php echo esc_html( $t( 'home_sobre_texto' ) ); ?></p>
            <a class="rac-botao-contorno" href="<?php echo esc_url( rac_url_pagina( 'sobre' ) ); ?>">Conheça nossa história →</a>
        </div>
      </div>
    </section>

</main>
<?php
get_footer();
