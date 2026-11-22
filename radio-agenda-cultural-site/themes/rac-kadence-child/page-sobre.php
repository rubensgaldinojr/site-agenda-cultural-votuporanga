<?php
/**
 * Template Name: Sobre
 *
 * Página Sobre (Sobre.dc.html). Vale para a página com o slug "sobre" mesmo sem
 * escolher o modelo; escolhendo o modelo "Sobre" em Atributos da página, aparecem os
 * campos de texto (campo vazio = texto provisório do mockup).
 *
 * - Foto do topo: "Imagem destacada" da página (sem ela, o placeholder do tema).
 * - Nossa história: campo "História — texto" (o 1º parágrafo vira a abertura).
 * - Quem faz: usuários marcados em "Aparecer na página Sobre" (perfil), na posição escolhida,
 *   alternando o lado da foto; cada um com até 3 itens (livro e textos mais recentes).
 * - 3 destaques: os mesmos textos da Home. Convite final para o Contato.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$pagina_id = get_queried_object_id();
$t         = function ( $nome ) use ( $pagina_id ) { return rac_sobre_texto( $nome, $pagina_id ); };

// "História": o 1º parágrafo é a abertura (maior, em Outfit leve); o resto é texto corrido.
$historia = trim( (string) $t( 'sobre_historia_texto' ) );
$abertura = '';
if ( preg_match( '#^\s*<p[^>]*>(.*?)</p>#is', $historia, $m ) ) {
    $abertura = $m[1];
    $historia = trim( substr( $historia, strlen( $m[0] ) ) );
}

$pessoas   = rac_autores_sobre();
$destaques = rac_destaques_home();

get_header();
?>
<main id="primary" class="rac-main">

    <section class="rac-sobre-hero">
        <div class="rac-sobre-hero__foto rac-foto"><?php echo rac_html_imagem_topo( $pagina_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        <div class="rac-sobre-hero__fade"></div>
        <div class="rac-sobre-hero__texto">
            <span class="rac-home-selo"><i aria-hidden="true"></i><?php echo esc_html( $t( 'sobre_selo' ) ); ?></span>
            <h1><?php echo esc_html( $t( 'sobre_titulo' ) ); ?></h1>
            <p><?php echo esc_html( $t( 'sobre_texto' ) ); ?></p>
        </div>
    </section>

    <section class="rac-sobre-historia">
        <div class="rac-sobre-historia__inner">
            <div class="rac-sobre-historia__titulo">
                <span class="rac-eyebrow"><?php echo esc_html( $t( 'sobre_historia_rotulo' ) ); ?></span>
                <h2><?php echo esc_html( $t( 'sobre_historia_titulo' ) ); ?></h2>
            </div>
            <div class="rac-sobre-historia__texto">
                <?php if ( $abertura !== '' ) : ?><p class="rac-sobre-historia__abertura"><?php echo wp_kses_post( $abertura ); ?></p><?php endif; ?>
                <div class="rac-sobre-historia__corpo"><?php echo wp_kses_post( $historia ); ?></div>
            </div>
        </div>
    </section>

    <?php if ( $pessoas ) : ?>
    <section class="rac-sobre-equipe">
        <div class="rac-sobre-equipe__topo">
            <div>
                <span class="rac-eyebrow">Quem faz</span>
                <h2><?php echo esc_html( $t( 'sobre_equipe_titulo' ) ); ?></h2>
            </div>
            <a href="<?php echo esc_url( rac_url_blog() ); ?>">Todos os textos →</a>
        </div>
        <?php foreach ( $pessoas as $i => $uid ) :
            $a     = rac_dados_autor( $uid );
            $itens = rac_itens_autor_sobre( $uid );
            $bio   = rac_paragrafos( $a['bio'] );
            ?>
            <article class="rac-sobre-autor<?php echo $i % 2 ? ' rac-sobre-autor--inverso' : ''; ?>">
                <a class="rac-sobre-autor__foto stripe-cover rac-foto" href="<?php echo esc_url( $a['url'] ); ?>" tabindex="-1" aria-hidden="true"><?php echo rac_foto_autor_html( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
                <div class="rac-sobre-autor__corpo">
                    <div class="rac-sobre-autor__cab">
                        <div class="rac-sobre-autor__nome">
                            <h3><?php echo esc_html( $a['nome'] ); ?></h3>
                            <?php if ( $a['papel'] ) : ?><span><?php echo esc_html( $a['papel'] ); ?></span><?php endif; ?>
                        </div>
                        <a class="rac-sobre-autor__ver rac-sobre-autor__ver--d" href="<?php echo esc_url( $a['url'] ); ?>">Página do autor →</a>
                    </div>
                    <?php if ( $bio ) : ?><p class="rac-sobre-autor__bio"><?php echo esc_html( implode( ' ', $bio ) ); ?></p><?php endif; ?>
                    <?php if ( $itens ) : ?>
                    <div class="rac-sobre-autor__itens">
                        <?php foreach ( $itens as $it ) : ?>
                            <a class="rac-sobre-item<?php echo $it['tipo'] === 'Livro' ? ' rac-sobre-item--livro' : ''; ?>" href="<?php echo esc_url( $it['url'] ); ?>">
                                <span class="rac-sobre-item__capa stripe-cover rac-foto"><?php echo $it['thumb']; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
                                <span class="rac-sobre-item__texto">
                                    <span class="rac-sobre-item__tipo"><?php echo esc_html( $it['tipo'] ); ?></span>
                                    <span class="rac-sobre-item__titulo"><?php echo esc_html( $it['titulo'] ); ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <a class="rac-sobre-autor__ver rac-sobre-autor__ver--m" href="<?php echo esc_url( $a['url'] ); ?>">Página do autor →</a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <section class="rac-sobre-destaques">
        <div class="rac-sobre-destaques__grade">
            <?php foreach ( $destaques as $d ) : ?>
                <div class="rac-destaque">
                    <span class="rac-destaque__kicker"><?php echo esc_html( $d['kicker'] ); ?></span>
                    <span class="rac-destaque__titulo"><?php echo esc_html( $d['titulo'] ); ?></span>
                    <span class="rac-destaque__texto"><?php echo esc_html( $d['texto'] ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rac-sobre-cta">
        <h2><?php echo esc_html( $t( 'sobre_cta_titulo' ) ); ?></h2>
        <p><?php echo esc_html( $t( 'sobre_cta_texto' ) ); ?></p>
        <div class="rac-sobre-cta__botoes">
            <a class="rac-botao-cheio" href="<?php echo esc_url( rac_url_pagina( 'contato' ) ); ?>">Fale com a gente</a>
            <a class="rac-botao-claro rac-so-desktop" href="<?php echo esc_url( rac_url_pagina( 'estacoes' ) ); ?>">Ouvir as estações</a>
        </div>
    </section>

</main>
<?php
get_footer();
