<?php
/**
 * Página inicial (Home v4 Claro.dc.html).
 *
 * Vale para a página definida como "Página inicial" em Configurações → Leitura.
 * Textos e fotos: campos ACF "Textos da página inicial" (vazios = texto
 * provisório do mockup); imagem do topo = Imagem destacada da página.
 *
 * Seções: topo, destaques, estações em destaque, todas as estações, Blog
 * (carrossel), Literatura (carrossel), Sobre nós, Podcasts (só se houver
 * séries) e Recados dos ouvintes (comentários desta própria página).
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

$posts_ids  = rac_posts_recentes( 6 );
$livros_ids = rac_livros_recentes( 8 );
$series     = rac_series_podcast();
$recados    = rac_recados( $pagina_id );
$recados_ok = comments_open( $pagina_id );

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
                <?php foreach ( $estacoes as $e ) : get_template_part( 'template-parts/card-estacao', null, array( 'estacao' => $e ) ); endforeach; ?>
            </div>
            <div class="rac-todas__grade rac-todas__grade--m">
                <?php foreach ( $estacoes as $e ) : get_template_part( 'template-parts/card-estacao', null, array( 'estacao' => $e, 'compact' => true ) ); endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Blog -->
    <?php if ( $posts_ids ) : ?>
    <section class="rac-faixa">
        <div class="rac-faixa__inner rac-carrossel rac-carrossel--posts" data-rac-carrossel role="region" aria-roledescription="carrossel" aria-label="Posts do blog">
            <div class="rac-faixa__topo">
                <div class="rac-faixa__titulo">
                    <h2>Do Blog</h2>
                    <span>Crônicas, bastidores e conversas da cena cultural.</span>
                </div>
                <div class="rac-carrossel__setas">
                    <button type="button" data-rac-prev aria-label="Posts anteriores">‹</button>
                    <button type="button" data-rac-next aria-label="Próximos posts">›</button>
                </div>
            </div>
            <div class="rac-carrossel__janela">
                <div class="rac-carrossel__trilho">
                    <?php foreach ( $posts_ids as $post_id ) : ?>
                        <div class="rac-carrossel__item"><?php get_template_part( 'template-parts/card-post', null, array( 'post' => $post_id ) ); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="rac-faixa__rodape">
                <a class="rac-botao-contorno" href="<?php echo esc_url( rac_url_pagina( 'blog' ) ); ?>">Ver todos os posts →</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Literatura -->
    <?php if ( $livros_ids ) : ?>
    <section class="rac-faixa rac-faixa--suave">
        <div class="rac-faixa__inner rac-carrossel rac-carrossel--livros" data-rac-carrossel role="region" aria-roledescription="carrossel" aria-label="Livros da biblioteca">
            <div class="rac-faixa__topo">
                <div class="rac-faixa__titulo">
                    <h2>Literatura</h2>
                    <span class="rac-so-desktop">Uma pequena biblioteca gratuita, curada por quem também escolhe as músicas da rádio. Baixe, leia e volte quando quiser.</span>
                    <span class="rac-so-mobile">Uma pequena biblioteca gratuita, curada com carinho.</span>
                </div>
                <div class="rac-carrossel__setas">
                    <button type="button" data-rac-prev aria-label="Livros anteriores">‹</button>
                    <button type="button" data-rac-next aria-label="Próximos livros">›</button>
                </div>
            </div>
            <div class="rac-carrossel__janela">
                <div class="rac-carrossel__trilho">
                    <?php foreach ( $livros_ids as $livro_id ) : ?>
                        <div class="rac-carrossel__item"><?php get_template_part( 'template-parts/card-livro', null, array( 'livro' => $livro_id ) ); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="rac-faixa__rodape">
                <a class="rac-botao-cheio" href="<?php echo esc_url( rac_url_pagina( 'literatura' ) ); ?>"><span class="rac-so-desktop">Explorar a biblioteca completa →</span><span class="rac-so-mobile">Explorar a biblioteca →</span></a>
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

    <!-- Podcasts (só aparece quando existem séries) -->
    <?php if ( $series ) : ?>
    <section class="rac-podcasts">
        <div class="rac-podcasts__inner">
            <h2>Podcasts</h2>
            <div class="rac-podcasts__grade">
                <?php foreach ( $series as $serie ) : ?>
                    <div class="rac-podcast">
                        <span class="rac-podcast__capa stripe-cover" aria-hidden="true"></span>
                        <div class="rac-podcast__texto">
                            <a class="rac-podcast__titulo" href="<?php echo esc_url( $serie['url'] ); ?>"><?php echo esc_html( $serie['nome'] ); ?></a>
                            <?php if ( $serie['descricao'] ) : ?><span class="rac-podcast__desc"><?php echo esc_html( $serie['descricao'] ); ?></span><?php endif; ?>
                            <span class="rac-podcast__eps"><?php echo esc_html( $serie['episodios'] === 1 ? '1 episódio' : $serie['episodios'] . ' episódios' ); ?></span>
                        </div>
                        <a class="rac-podcast__ir" href="<?php echo esc_url( $serie['url'] ); ?>" aria-label="<?php echo esc_attr( 'Ver os episódios de ' . $serie['nome'] ); ?>"><span class="rac-ico-play" aria-hidden="true"></span></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Recados dos ouvintes (comentários da própria Home) -->
    <?php if ( $recados || $recados_ok ) : ?>
    <section class="rac-recados" data-rac-recados data-estado="fechado">
        <div class="rac-recados__inner">
            <div class="rac-recados__lado">
                <div class="rac-recados__cab">
                    <h2>Recados dos ouvintes</h2>
                    <span class="rac-recados__contagem rac-recados__contagem--m"><?php echo esc_html( $recados ? rac_rotulo_recados( count( $recados ) ) : '' ); ?></span>
                </div>
                <p class="rac-recados__texto">Lembranças, elogios e boas conversas. Os recados passam por moderação antes de aparecer. Quer pedir uma música? <a href="<?php echo esc_url( rac_url_pagina( 'contato' ) ); ?>">Fale com a gente</a>.</p>
                <span class="rac-recados__contagem rac-recados__contagem--d"><?php echo esc_html( $recados ? rac_rotulo_recados( count( $recados ) ) : 'Seja o primeiro a deixar um recado.' ); ?></span>

                <?php if ( $recados_ok ) : ?>
                <div class="rac-recados__acao">
                    <button type="button" class="rac-botao-cheio rac-recados__abrir" data-rac-abrir>Deixe seu recado</button>

                    <form class="rac-recados__form" data-rac-form method="post" action="<?php echo esc_url( site_url( '/wp-comments-post.php' ) ); ?>" hidden>
                        <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( $pagina_id ); ?>">
                        <input type="hidden" name="comment_parent" value="0">
                        <input type="hidden" name="rac_ms" value="0">
                        <div class="rac-recados__armadilha" aria-hidden="true"><label>Não preencha este campo <input type="text" name="rac_site" tabindex="-1" autocomplete="off"></label></div>
                        <input type="text" name="author" placeholder="Seu nome" required maxlength="245" autocomplete="name" aria-label="Seu nome">
                        <input type="email" name="email" placeholder="Seu e-mail (não será publicado)" required maxlength="100" autocomplete="email" aria-label="Seu e-mail">
                        <textarea name="comment" rows="3" placeholder="Escreva seu recado…" required maxlength="1500" aria-label="Seu recado"></textarea>
                        <div class="rac-recados__botoes">
                            <button type="submit" class="rac-botao-cheio" data-rac-enviar>Enviar recado</button>
                            <button type="button" class="rac-botao-texto" data-rac-cancelar>Cancelar</button>
                        </div>
                        <p class="rac-recados__erro" role="alert" data-rac-erro hidden></p>
                    </form>

                    <p class="rac-recados__ok" role="status" data-rac-ok hidden>Obrigado! Seu recado aparece após a moderação.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="rac-recados__corpo">
                <?php if ( $recados ) : ?>
                <div class="rac-recados__lista">
                    <?php foreach ( $recados as $c ) : ?>
                        <article class="rac-recado">
                            <span class="rac-recado__avatar" aria-hidden="true"><?php echo esc_html( rac_inicial( $c->comment_author ) ); ?></span>
                            <div class="rac-recado__corpo">
                                <div class="rac-recado__cab">
                                    <span class="rac-recado__nome"><?php echo esc_html( $c->comment_author ? $c->comment_author : 'Ouvinte' ); ?></span>
                                    <span class="rac-recado__data"><?php echo esc_html( rac_tempo_relativo( $c ) ); ?></span>
                                </div>
                                <span class="rac-recado__texto"><?php echo esc_html( wp_strip_all_tags( $c->comment_content ) ); ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <nav class="rac-recados__paginas" data-rac-paginas aria-label="Páginas de recados" hidden>
                    <div class="rac-pag rac-pag--d">
                        <button type="button" data-rac-pag-prev aria-label="Recados anteriores">‹</button>
                        <span data-rac-pag-numeros></span>
                        <button type="button" data-rac-pag-next aria-label="Próximos recados">›</button>
                    </div>
                    <div class="rac-pag rac-pag--m">
                        <button type="button" data-rac-pag-prev>‹ Anteriores</button>
                        <span data-rac-pag-rotulo></span>
                        <button type="button" data-rac-pag-next>Próximos ›</button>
                    </div>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

</main>
<?php
get_footer();
