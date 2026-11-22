<?php
/**
 * Template Name: Sobre
 *
 * Página Sobre (Sobre.dc.html). Vale para a página com o slug "sobre" mesmo sem
 * escolher o modelo; escolhendo o modelo "Sobre" em Atributos da página, aparecem os
 * campos de texto (campo vazio = texto provisório do mockup).
 *
 * - Foto do topo: "Imagem destacada" da página (sem ela, o placeholder do tema).
 * - Nossa história (montada em blocos): abertura (1º parágrafo do texto principal) → Imagem 1 ao lado do resto do
 *   texto principal → continuação ao lado da Imagem 2. Imagens opcionais (sem imagem, o texto ocupa a largura toda).
 * - Quem faz: usuários marcados em "Aparecer na página Sobre" (perfil), na posição escolhida,
 *   alternando o lado da foto; cada um com até 3 itens (livro e textos mais recentes).
 * - 3 destaques: os mesmos textos da Home, cada um com um link para a página correspondente. Convite final para o Contato.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$pagina_id = get_queried_object_id();
$t         = function ( $nome ) use ( $pagina_id ) { return rac_sobre_texto( $nome, $pagina_id ); };

$hist = rac_sobre_historia( $pagina_id );

// Figura (imagem + legenda opcional); vazia quando não há imagem.
$figura = function ( $id, $legenda, $classe ) {
    if ( ! $id ) return '';
    $html  = '<figure class="rac-sobre-figura ' . esc_attr( $classe ) . '"><div class="rac-sobre-figura__img rac-foto">' . wp_get_attachment_image( $id, 'large', false, array( 'alt' => $legenda, 'loading' => 'lazy' ) ) . '</div>';
    if ( $legenda !== '' ) $html .= '<figcaption>' . esc_html( $legenda ) . '</figcaption>';
    return $html . '</figure>';
};

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
            <div class="rac-sobre-historia__cab">
                <div class="rac-sobre-historia__titulo">
                    <span class="rac-eyebrow"><?php echo esc_html( $t( 'sobre_historia_rotulo' ) ); ?></span>
                    <h2><?php echo esc_html( $t( 'sobre_historia_titulo' ) ); ?></h2>
                </div>
                <?php if ( $hist['abertura'] !== '' ) : ?><p class="rac-sobre-historia__abertura"><?php echo wp_kses_post( $hist['abertura'] ); ?></p><?php endif; ?>
            </div>

            <?php if ( $hist['img1'] || $hist['texto1'] !== '' ) : ?>
            <div class="rac-sobre-bloco<?php echo ( $hist['img1'] && $hist['texto1'] !== '' ) ? '' : ' rac-sobre-bloco--sozinho'; ?>">
                <?php echo $figura( $hist['img1'], $hist['leg1'], 'rac-sobre-figura--retrato' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                <?php if ( $hist['texto1'] !== '' ) : ?><div class="rac-sobre-bloco__texto"><?php echo wp_kses_post( $hist['texto1'] ); ?></div><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ( $hist['img2'] || $hist['texto2'] !== '' ) : ?>
            <div class="rac-sobre-bloco rac-sobre-bloco--inverso<?php echo ( $hist['img2'] && $hist['texto2'] !== '' ) ? '' : ' rac-sobre-bloco--sozinho'; ?>">
                <?php if ( $hist['texto2'] !== '' ) : ?><div class="rac-sobre-bloco__texto"><?php echo wp_kses_post( $hist['texto2'] ); ?></div><?php endif; ?>
                <?php echo $figura( $hist['img2'], $hist['leg2'], 'rac-sobre-figura--paisagem' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ( $pessoas ) : ?>
    <section class="rac-sobre-equipe">
        <div class="rac-sobre-equipe__topo">
            <div>
                <span class="rac-eyebrow">Quem faz</span>
                <h2><?php echo esc_html( $t( 'sobre_equipe_titulo' ) ); ?></h2>
            </div>
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
                    </div>
                    <?php if ( $bio ) : ?><p class="rac-sobre-autor__bio"><?php echo esc_html( implode( ' ', $bio ) ); ?></p><?php endif; ?>
                    <?php if ( $itens ) : ?>
                    <span class="rac-sobre-autor__itens-titulo">Publicações recentes</span>
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
                    <a class="rac-sobre-autor__ver" href="<?php echo esc_url( $a['url'] ); ?>">Página do autor →</a>
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
                    <a class="rac-destaque__link" href="<?php echo esc_url( $d['url'] ); ?>"><?php echo esc_html( $d['link'] ); ?> →</a>
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
