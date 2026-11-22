<?php
/**
 * Formato Música/Artista: cartão com a bandeira do país, nome, gênero e o player
 * do Spotify (ou YouTube/SoundCloud) mais, se houver, a estação em que toca e o
 * Instagram. O texto do post (editor) vem logo abaixo.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( $args['fase'] !== 'topo' ) return;

$id      = $args['id'];
$nome    = trim( (string) rac_campo( 'artista_nome', $id, '' ) );
$iso     = strtolower( (string) rac_campo( 'artista_pais', $id, '' ) );
$paises  = rac_paises();
$pais    = isset( $paises[ $iso ] ) ? $paises[ $iso ] : '';
$genero  = trim( (string) rac_campo( 'artista_genero', $id, '' ) );
$embed   = rac_embed_artista_html( rac_campo( 'artista_link', $id, '' ) );
$insta   = (string) rac_campo( 'artista_instagram', $id, '' );
$estacao = (int) rac_campo( 'artista_estacao', $id, 0 );
$est     = $estacao ? rac_estacao_por_id( $estacao ) : null;
if ( $nome === '' && ! $embed ) return;
?>
<section class="rac-artista" aria-label="Artista em destaque">
    <div class="rac-artista__cab">
        <?php if ( $iso && $pais ) : ?>
            <span class="rac-artista__pais"><?php echo rac_bandeira_html( $iso ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $pais ); ?></span></span>
        <?php endif; ?>
        <span class="rac-eyebrow">Artista em destaque</span>
        <?php if ( $nome ) : ?><h2 class="rac-artista__nome"><?php echo esc_html( $nome ); ?></h2><?php endif; ?>
        <?php if ( $genero ) : ?><span class="rac-artista__genero"><?php echo esc_html( $genero ); ?></span><?php endif; ?>
    </div>
    <?php if ( $embed ) : ?><div class="rac-artista__player"><?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?>
    <?php get_template_part( 'template-parts/aviso-radio' ); ?>
    <?php if ( $est || $insta ) : ?>
        <div class="rac-artista__acoes">
            <?php if ( $est ) : ?><a class="rac-botao-cheio" href="<?php echo esc_url( $est['url'] ); ?>"><span class="rac-ico-play" aria-hidden="true"></span>Toca na estação <?php echo esc_html( $est['nome'] ); ?></a><?php endif; ?>
            <?php if ( $insta ) : ?><a class="rac-botao-claro" href="<?php echo esc_url( $insta ); ?>" target="_blank" rel="noopener">Instagram do artista</a><?php endif; ?>
        </div>
    <?php endif; ?>
</section>
