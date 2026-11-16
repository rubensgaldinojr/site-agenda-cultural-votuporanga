<?php
/**
 * Formato Podcast/Episódio: cartão com a identificação do episódio e o player
 * (Spotify, YouTube ou SoundCloud). O texto do post (editor) vira as notas do
 * episódio, logo abaixo. A categoria "Podcasts" continua sendo o que agrupa os
 * episódios na listagem; o formato só muda o layout do post.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( $args['fase'] !== 'topo' ) return;

$episodio = trim( (string) rac_campo( 'podcast_episodio', $args['id'], '' ) );
$embed    = rac_embed_artista_html( rac_campo( 'podcast_link', $args['id'], '' ) );
if ( ! $embed ) return;
?>
<section class="rac-artista rac-artista--podcast" aria-label="Episódio do podcast">
    <div class="rac-artista__cab">
        <span class="rac-eyebrow">Podcast</span>
        <?php if ( $episodio ) : ?><h2 class="rac-artista__nome"><?php echo esc_html( $episodio ); ?></h2><?php endif; ?>
    </div>
    <div class="rac-artista__player"><?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <?php get_template_part( 'template-parts/aviso-radio' ); ?>
</section>
