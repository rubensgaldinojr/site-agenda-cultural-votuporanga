<?php
/**
 * PlayerFixo (PlayerFixo.dc.html).
 *
 * Os IDs e classes "racr-*" abaixo são o contrato com o JS do plugin
 * racr-player-radio (veja inc/player.php). NÃO renomear. Todo o visual é
 * CSS (assets/css/componentes.css) e o comportamento extra é
 * assets/js/player.js.
 *
 * O mesmo markup serve desktop (barra de 72px + dropdown) e mobile (barra
 * de 56px + bottom sheet); o CSS escolhe o que mostrar.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$estacoes = rac_listar_estacoes();
if ( empty( $estacoes ) ) return;

$primeira = $estacoes[0];
$estilo_capa = $primeira['capa'] ? ' style="background-image:url(\'' . esc_url( $primeira['capa'] ) . '\')"' : '';
?>
<div class="racr-player rac-player" id="racr-player" role="region" aria-label="Player da rádio">
    <div class="rac-player__painel" id="rac-player-painel">
        <div class="rac-player__painel-topo">
            <span class="rac-player__painel-titulo"><span class="rac-so-desktop">Estações</span><span class="rac-so-mobile">Escolher estação</span></span>
            <button type="button" class="rac-player__fechar" id="rac-player-fechar" aria-label="Fechar lista de estações">✕</button>
        </div>
        <ul class="racr-lista-estacoes" id="racr-lista-estacoes">
            <?php foreach ( $estacoes as $i => $e ) : ?>
                <li>
                    <button
                        type="button"
                        class="racr-estacao<?php echo $i === 0 ? ' racr-estacao-ativa' : ''; ?>"
                        data-indice="<?php echo esc_attr( $i ); ?>"
                        data-estacao-id="<?php echo esc_attr( $e['id'] ); ?>"
                        data-capa="<?php echo esc_url( $e['capa'] ); ?>"
                    >
                        <span class="rac-est__capa stripe-cover"<?php echo $e['capa'] ? ' style="background-image:url(\'' . esc_url( $e['capa'] ) . '\')"' : ''; ?>></span>
                        <span class="rac-est__nome"><?php echo esc_html( $e['nome'] ); ?></span>
                        <span class="rac-est__ponto" aria-hidden="true"></span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="rac-player__barra">
        <div class="rac-player__esq">
            <div class="rac-player__capa stripe-cover" data-rac="capa"<?php echo $estilo_capa; // phpcs:ignore WordPress.Security.EscapeOutput ?>></div>

            <div class="rac-player__estacao">
                <span class="rac-player__aovivo"><i aria-hidden="true"></i>Ao vivo</span>
                <span class="racr-nome-estacao" id="racr-nome-estacao"><?php echo esc_html( $primeira['nome'] ); ?></span>
            </div>
            <span class="rac-player__sep" aria-hidden="true"></span>

            <!-- desktop: "Tocando agora" com faixa e artista em linhas separadas -->
            <div class="rac-player__agora" aria-hidden="true">
                <span class="rac-player__rotulo" data-rac="rotulo">Status</span>
                <span class="rac-player__faixa" data-rac="faixa">Parado</span>
                <span class="rac-player__artista" data-rac="artista"></span>
            </div>

            <!-- mobile: faixa + "artista · estação" -->
            <div class="rac-player__m-info" aria-hidden="true">
                <span class="rac-player__faixa" data-rac="faixa">Parado</span>
                <span class="rac-player__artista" data-rac="linha2-m"><?php echo esc_html( $primeira['nome'] ); ?></span>
            </div>

            <!-- Texto de estado/música escrito pelo plugin. Fica fora da tela e
                 é lido por leitores de tela; player.js divide em faixa/artista. -->
            <span class="racr-status rac-sr-only" id="racr-status" aria-live="polite">Parado</span>

            <!-- exigido pelo JS do plugin; sem função visual neste layout -->
            <span id="racr-botao-info" hidden></span>
        </div>

        <div class="rac-player__controles">
            <button type="button" class="racr-botao-play" id="racr-botao-play" aria-label="Tocar ou pausar a rádio">
                <span class="rac-ico-play racr-icone-play" aria-hidden="true"></span>
                <span class="rac-ico-pause racr-icone-pause" aria-hidden="true"><i></i><i></i></span>
            </button>
            <span class="rac-player__sep-pilula" aria-hidden="true"></span>
            <label class="rac-player__volume">
                <span class="rac-player__volume-icone" aria-hidden="true"><i></i><i></i><i></i></span>
                <span class="rac-sr-only">Volume</span>
                <input type="range" id="rac-volume" min="0" max="100" value="100">
            </label>
        </div>

        <div class="rac-player__dir">
            <button type="button" class="racr-botao-expandir rac-player__trocar" id="racr-botao-expandir" aria-expanded="false" aria-controls="rac-player-painel">
                <span class="rac-so-desktop">Trocar estação</span><span class="rac-so-mobile">Estações</span>
                <span class="rac-player__chev" aria-hidden="true">▾</span>
            </button>
        </div>
    </div>

    <audio id="racr-audio" preload="none"></audio>
</div>
