<?php
/**
 * Formato Vídeo: player nativo do YouTube/Vimeo (modo de privacidade) e capítulos
 * clicáveis. Clicar num capítulo recarrega o player a partir daquele ponto (assets/js/blog.js).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( $args['fase'] !== 'topo' ) return;

$dados = rac_dados_video( rac_campo( 'video_url', $args['id'], '' ) );
if ( ! $dados ) return;
$capitulos = rac_capitulos( rac_campo( 'video_capitulos', $args['id'], '' ) );
?>
<div class="rac-post__video" data-rac-video data-plataforma="<?php echo esc_attr( $dados['plataforma'] ); ?>" data-id="<?php echo esc_attr( $dados['id'] ); ?>">
    <div class="rac-post__video-quadro">
        <iframe src="<?php echo esc_url( rac_src_video( $dados ) ); ?>" title="Vídeo do post" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </div>
    <?php if ( $capitulos ) : ?>
        <div class="rac-post__capitulos" role="list" aria-label="Capítulos do vídeo">
            <?php foreach ( $capitulos as $c ) : ?>
                <button type="button" class="rac-capitulo" role="listitem" data-rac-capitulo="<?php echo (int) $c['seg']; ?>"><b><?php echo esc_html( $c['tempo'] ); ?></b><?php echo esc_html( $c['nome'] ); ?></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php get_template_part( 'template-parts/aviso-radio' ); ?>
</div>
