<?php
/**
 * Template Name: Estações
 *
 * Listagem de estações (Estacoes.dc.html). Vale para a página com o slug
 * "estacoes" mesmo sem escolher o modelo; escolhendo o modelo "Estações"
 * em Atributos da página, aparecem os campos do topo (título e texto).
 *
 * - Imagem do topo: "Imagem destacada" da página (sem ela, placeholder).
 * - Estações em ordem alfabética; sem filtro por grupo.
 * - Cards com imagem (template-parts/card-estacao-imagem.php): 4 colunas no
 *   desktop e 2 no mobile. Estação sem imagem destacada usa o placeholder.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$pagina_id = get_queried_object_id();
$estacoes  = rac_listar_estacoes_alfabetica();
$total     = count( $estacoes );

$titulo = rac_campo( 'hero_titulo', $pagina_id, 'Uma trilha para cada momento do dia' );
$texto  = rac_campo( 'hero_texto', $pagina_id, 'Todas as estações da Agenda Cultural tocando 24 horas, direto de Votuporanga. Aperte o play para ouvir agora ou abra uma estação para conhecer a programação.' );
$selo   = $total === 1 ? '1 estação ao vivo agora' : $total . ' estações ao vivo agora';

get_header();
?>
<main id="primary" class="rac-main">
    <section class="rac-hero rac-hero--lista">
        <div class="rac-hero__foto rac-foto"><?php echo rac_html_imagem_topo( $pagina_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        <div class="rac-hero__fade"></div>
        <div class="rac-hero__fade-base"></div>
        <div class="rac-hero__miolo">
            <div class="rac-hero__conteudo">
                <span class="rac-selo-live">
                    <span class="rac-live-dot" aria-hidden="true"><i></i><i></i></span>
                    <span class="rac-selo-live__txt" data-rac="total-estacoes"><?php echo esc_html( $selo ); ?></span>
                </span>
                <h1 class="rac-hero__titulo"><?php echo esc_html( $titulo ); ?></h1>
                <p class="rac-hero__texto"><?php echo esc_html( $texto ); ?></p>
            </div>
        </div>
    </section>

    <section class="rac-secao-lista">
        <?php if ( $estacoes ) : ?>
            <div class="rac-grade-img">
                <?php foreach ( $estacoes as $e ) : get_template_part( 'template-parts/card-estacao-imagem', null, array( 'estacao' => $e ) ); endforeach; ?>
            </div>
        <?php else : ?>
            <p class="rac-vazio">Nenhuma estação publicada ainda.</p>
        <?php endif; ?>
    </section>
</main>
<?php
get_footer();
