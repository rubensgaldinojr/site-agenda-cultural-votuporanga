<?php
/**
 * Plugin Name: Player de Rádio - Agenda Cultural
 * Description: Player persistente para as rádios do site, tocando os streams do Zeno.fm.
 * Version: 0.4
 * Atualizado em: 10/08/2026 20:14
 * Author: Rubens
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   FASE 3 — player persistente entre páginas

   Duas mudanças grandes nesta fase:

   1. O player deixou de ser inserido por shortcode dentro do
      conteúdo da página — isso o colocava dentro da área que a
      navegação via AJAX troca a cada clique, então ele seria
      destruído e recriado a cada navegação. Agora ele é injetado
      direto no rodapé técnico do site (hook wp_footer), fora
      dessa área, então sobrevive às trocas de conteúdo. Fica
      recolhido por padrão (só a barra com play/pause e nome da
      estação); a lista de estações abre ao clicar.

   2. Camada de navegação via AJAX: intercepta cliques em links
      internos, busca a próxima página via fetch() e troca só o
      conteúdo de "#primary" — confirmado por inspeção como a
      área de conteúdo principal do tema Kadence neste site —
      mantendo cabeçalho, rodapé e o player intactos.

   FASE 4 — metadado de música/artista (ICY)

   Endpoint AJAX que conecta no stream do Zeno.fm com o cabeçalho
   Icy-MetaData, lê só o pedaço necessário até o bloco de
   metadado, extrai o "StreamTitle" e fecha a conexão. Recebe
   apenas o ÍNDICE da estação (nunca a URL vinda do navegador),
   pra evitar que o endpoint seja usado pra buscar URLs
   arbitrárias. Resultado fica em cache por 15s por estação, pra
   não abrir uma conexão nova no Zeno.fm a cada visitante que
   estiver com o player aberto.
   ============================================================ */

/**
 * Busca as estações publicadas do CPT "estacao" com os campos ACF
 * que o player precisa. Ordena por "ordem_de_exibicao" quando
 * preenchido; senão, cai pra ordem alfabética pelo título.
 *
 * @return array Lista de estações: id, nome, streamUrl, cor.
 */
function racr_obter_estacoes() {
    static $estacoes = null;
    if ( $estacoes !== null ) {
        return $estacoes;
    }

    $query = new WP_Query( array(
        'post_type'      => 'estacao',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    $estacoes = array();

    foreach ( $query->posts as $post ) {
        $stream_url = get_field( 'url_do_stream', $post->ID );
        if ( empty( $stream_url ) ) {
            continue; // estação sem stream cadastrado não entra na lista
        }
        $estacoes[] = array(
            'id'        => $post->ID,
            'nome'      => get_the_title( $post ),
            'streamUrl' => esc_url_raw( $stream_url ),
            'cor'       => get_field( 'cor_de_destaque', $post->ID ) ?: '#ee8133',
            'ordem'     => get_field( 'ordem_de_exibicao', $post->ID ),
        );
    }

    usort( $estacoes, function( $a, $b ) {
        $a_tem_ordem = $a['ordem'] !== '' && $a['ordem'] !== null;
        $b_tem_ordem = $b['ordem'] !== '' && $b['ordem'] !== null;

        if ( $a_tem_ordem && $b_tem_ordem ) {
            return $a['ordem'] <=> $b['ordem'];
        }
        if ( $a_tem_ordem !== $b_tem_ordem ) {
            return $a_tem_ordem ? -1 : 1; // quem tem ordem manual vem primeiro
        }
        return strcasecmp( $a['nome'], $b['nome'] ); // fallback alfabético
    } );

    return $estacoes;
}

/**
 * Conecta no stream de rádio e extrai o "StreamTitle" do bloco de
 * metadado ICY (formato usado por Icecast/Shoutcast, incluindo o
 * Zeno.fm). Usa fopen/fread de baixo nível — não dá pra usar a API
 * HTTP padrão do WordPress (wp_remote_get) aqui porque ela espera
 * baixar o corpo inteiro da resposta, e um stream de rádio nunca
 * termina; precisamos ler só os bytes até o bloco de metadado e
 * fechar a conexão na hora.
 *
 * @param string $stream_url URL do stream (já validada pelo chamador).
 * @return string|null Título extraído, ou null se indisponível.
 */
function racr_buscar_metadado_icy( $stream_url ) {
    if ( ! ini_get( 'allow_url_fopen' ) ) {
        return null;
    }

    $contexto = stream_context_create( array(
        'http' => array(
            'method'           => 'GET',
            'header'           => "Icy-MetaData: 1\r\nUser-Agent: RACR-Player/1.0\r\n",
            'protocol_version' => 1.1, // o Zeno.fm recusa a requisição com Icy-MetaData via HTTP/2
            'timeout'          => 6,
            'follow_location'  => 1,
        ),
    ) );

    $handle = @fopen( $stream_url, 'r', false, $contexto );
    if ( ! $handle ) {
        return null;
    }

    $metaint = 0;
    if ( isset( $http_response_header ) && is_array( $http_response_header ) ) {
        foreach ( $http_response_header as $linha ) {
            if ( stripos( $linha, 'icy-metaint:' ) === 0 ) {
                $metaint = (int) trim( substr( $linha, strlen( 'icy-metaint:' ) ) );
                break;
            }
        }
    }

    if ( $metaint <= 0 ) {
        fclose( $handle );
        return null;
    }

    fread( $handle, $metaint ); // descarta o áudio até o bloco de metadado
    $tamanho_byte = fread( $handle, 1 );
    $titulo       = null;

    if ( $tamanho_byte !== false && $tamanho_byte !== '' ) {
        $tamanho = ord( $tamanho_byte ) * 16;
        if ( $tamanho > 0 ) {
            $bloco = fread( $handle, $tamanho );
            if ( preg_match( "/StreamTitle='([^']*)'/", $bloco, $m ) ) {
                $titulo = trim( $m[1] );
            }
        }
    }

    fclose( $handle );

    return ( $titulo !== null && $titulo !== '' ) ? $titulo : null;
}

/**
 * Handler AJAX que devolve o metadado (música/artista) da estação
 * pedida, com cache de 15s por estação pra não sobrecarregar o
 * stream de origem quando vários visitantes estiverem ouvindo.
 */
function racr_ajax_obter_metadado() {
    $indice   = isset( $_POST['indice'] ) ? intval( $_POST['indice'] ) : -1;
    $estacoes = racr_obter_estacoes();

    if ( $indice < 0 || ! isset( $estacoes[ $indice ] ) ) {
        wp_send_json_error();
    }

    $estacao     = $estacoes[ $indice ];
    $chave_cache = 'racr_metadado_' . $estacao['id'];
    $titulo      = get_transient( $chave_cache );

    if ( $titulo === false ) {
        $titulo = racr_buscar_metadado_icy( $estacao['streamUrl'] );
        set_transient( $chave_cache, $titulo === null ? '' : $titulo, 15 );
    }

    wp_send_json_success( array( 'titulo' => $titulo ? $titulo : null ) );
}
add_action( 'wp_ajax_racr_obter_metadado', 'racr_ajax_obter_metadado' );
add_action( 'wp_ajax_nopriv_racr_obter_metadado', 'racr_ajax_obter_metadado' );

/* ============================================================
   1. PLAYER NO RODAPÉ (fora da área trocada pela navegação AJAX)
   ============================================================ */
function racr_renderizar_player() {
    if ( is_admin() ) return;

    $estacoes = racr_obter_estacoes();
    if ( empty( $estacoes ) ) return;
    ?>
    <div class="racr-player" id="racr-player">
        <div class="racr-barra">
            <button type="button" class="racr-botao-play" id="racr-botao-play" aria-label="Tocar rádio">
                <span class="racr-icone-play">▶</span>
                <span class="racr-icone-pause">❚❚</span>
            </button>
            <div class="racr-info" id="racr-botao-info" role="button" tabindex="0">
                <span class="racr-nome-estacao" id="racr-nome-estacao"><?php echo esc_html( $estacoes[0]['nome'] ); ?></span>
                <span class="racr-status" id="racr-status">Parado</span>
            </div>
            <button type="button" class="racr-botao-expandir" id="racr-botao-expandir" aria-label="Ver estações" aria-expanded="false">▲</button>
        </div>
        <ul class="racr-lista-estacoes" id="racr-lista-estacoes">
            <?php foreach ( $estacoes as $indice => $estacao ) : ?>
                <li>
                    <button
                        type="button"
                        class="racr-estacao<?php echo $indice === 0 ? ' racr-estacao-ativa' : ''; ?>"
                        data-indice="<?php echo esc_attr( $indice ); ?>"
                    >
                        <span class="racr-estacao-cor" style="background:<?php echo esc_attr( $estacao['cor'] ); ?>"></span>
                        <?php echo esc_html( $estacao['nome'] ); ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
        <audio id="racr-audio" preload="none"></audio>
    </div>
    <?php
}
add_action( 'wp_footer', 'racr_renderizar_player' );

/* ============================================================
   2. CSS E JS DO PLAYER
   ============================================================ */
function racr_enqueue_frontend() {
    if ( is_admin() ) return;

    $estacoes = racr_obter_estacoes();
    if ( empty( $estacoes ) ) return;

    wp_enqueue_style(
        'racr-player',
        plugin_dir_url( __FILE__ ) . 'racr-player-radio.css',
        array(),
        '0.4'
    );

    wp_register_script( 'racr-player', '', array(), '0.4', true );
    wp_enqueue_script( 'racr-player' );

    // Bloco 1: play/pause, seleção de estação, Media Session, recolher/expandir lista.
    wp_add_inline_script( 'racr-player', '
        (function(){
            var player = document.getElementById("racr-player");
            if (!player) return;
            var audio = document.getElementById("racr-audio");
            var botao = document.getElementById("racr-botao-play");
            var status = document.getElementById("racr-status");
            var nomeEl = document.getElementById("racr-nome-estacao");
            var lista = document.getElementById("racr-lista-estacoes");
            var botaoInfo = document.getElementById("racr-botao-info");
            var botaoExpandir = document.getElementById("racr-botao-expandir");
            var estacoes = ' . wp_json_encode( $estacoes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';
            var ajaxurl = ' . wp_json_encode( admin_url( 'admin-ajax.php' ) ) . ';
            var indiceAtual = 0;
            var carregado = false;
            var intervaloMetadado = null;

            function definirStatus(texto, classe){
                status.textContent = texto;
                player.classList.remove("racr-tocando", "racr-carregando", "racr-erro");
                if (classe) player.classList.add(classe);
            }

            function alternarLista(){
                var expandido = player.classList.toggle("racr-expandido");
                botaoExpandir.setAttribute("aria-expanded", expandido ? "true" : "false");
            }

            function recolherLista(){
                player.classList.remove("racr-expandido");
                botaoExpandir.setAttribute("aria-expanded", "false");
            }

            function atualizarMediaSession(estacao){
                if ("mediaSession" in navigator) {
                    navigator.mediaSession.metadata = new MediaMetadata({
                        title: estacao.nome,
                        artist: "Ao vivo"
                    });
                }
            }

            function marcarEstacaoAtiva(indice){
                var botoes = lista.querySelectorAll(".racr-estacao");
                botoes.forEach(function(b){ b.classList.remove("racr-estacao-ativa"); });
                var botaoAtivo = lista.querySelector(\'.racr-estacao[data-indice="\' + indice + \'"]\');
                if (botaoAtivo) botaoAtivo.classList.add("racr-estacao-ativa");
            }

            function pararMetadado(){
                if (intervaloMetadado) {
                    clearInterval(intervaloMetadado);
                    intervaloMetadado = null;
                }
            }

            function buscarMetadado(indice){
                var dados = new URLSearchParams();
                dados.append("action", "racr_obter_metadado");
                dados.append("indice", indice);
                fetch(ajaxurl, { method: "POST", body: dados })
                    .then(function(r){ return r.json(); })
                    .then(function(resposta){
                        if (indice !== indiceAtual || audio.paused) return;
                        var titulo = resposta && resposta.success ? resposta.data.titulo : null;
                        status.textContent = titulo ? titulo : "Ao vivo";
                    })
                    .catch(function(){
                        // silencioso: mantém o texto de status como está
                    });
            }

            function iniciarMetadado(indice){
                pararMetadado();
                buscarMetadado(indice);
                intervaloMetadado = setInterval(function(){ buscarMetadado(indice); }, 20000);
            }

            function tocarEstacao(indice){
                var estacao = estacoes[indice];
                if (!estacao) return;
                indiceAtual = indice;
                nomeEl.textContent = estacao.nome;
                atualizarMediaSession(estacao);
                marcarEstacaoAtiva(indice);

                audio.src = estacao.streamUrl;
                audio.load();
                carregado = true;
                definirStatus("Carregando...", "racr-carregando");
                audio.play().catch(function(erro){
                    definirStatus("Não foi possível tocar", "racr-erro");
                    console.error("racr: erro ao tocar", erro);
                });
            }

            function continuarOuIniciar(){
                if (!carregado) {
                    tocarEstacao(indiceAtual);
                    return;
                }
                definirStatus("Carregando...", "racr-carregando");
                audio.play().catch(function(erro){
                    definirStatus("Não foi possível tocar", "racr-erro");
                    console.error("racr: erro ao tocar", erro);
                });
            }

            function pausar(){
                audio.pause();
            }

            botao.addEventListener("click", function(){
                if (audio.paused) {
                    continuarOuIniciar();
                } else {
                    pausar();
                }
            });

            botaoInfo.addEventListener("click", alternarLista);
            botaoInfo.addEventListener("keydown", function(e){
                if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    alternarLista();
                }
            });
            botaoExpandir.addEventListener("click", alternarLista);

            lista.addEventListener("click", function(e){
                var botaoEstacao = e.target.closest(".racr-estacao");
                if (!botaoEstacao) return;
                var indice = parseInt(botaoEstacao.getAttribute("data-indice"), 10);
                tocarEstacao(indice);
                recolherLista();
            });

            audio.addEventListener("playing", function(){
                definirStatus("Ao vivo", "racr-tocando");
                iniciarMetadado(indiceAtual);
            });
            audio.addEventListener("pause", function(){
                definirStatus("Parado");
                pararMetadado();
            });
            audio.addEventListener("waiting", function(){
                definirStatus("Carregando...", "racr-carregando");
            });
            audio.addEventListener("error", function(){
                definirStatus("Erro no stream", "racr-erro");
                carregado = false;
                pararMetadado();
            });

            if ("mediaSession" in navigator) {
                navigator.mediaSession.setActionHandler("play", continuarOuIniciar);
                navigator.mediaSession.setActionHandler("pause", pausar);
            }
        })();
    ' );

    // Bloco 2: navegação persistente via AJAX (troca só o "#primary" do Kadence).
    wp_add_inline_script( 'racr-player', '
        (function(){
            var seletorConteudo = "#primary";
            var emNavegacao = false;

            function ehLinkNavegavel(link){
                if (!link || !link.getAttribute("href")) return false;
                if (link.target && link.target !== "" && link.target !== "_self") return false;
                if (link.hasAttribute("download")) return false;
                if (link.dataset && "racrIgnorar" in link.dataset) return false;

                var url;
                try {
                    url = new URL(link.href, window.location.href);
                } catch (erro) {
                    return false;
                }

                if (url.protocol !== "http:" && url.protocol !== "https:") return false;
                if (url.origin !== window.location.origin) return false;
                if (url.pathname.indexOf("/wp-admin") === 0 || url.pathname.indexOf("/wp-login.php") === 0) return false;
                if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash !== "") return false;

                return true;
            }

            function trocarConteudo(url, empilhar){
                if (emNavegacao) return;
                emNavegacao = true;
                document.body.classList.add("racr-navegando");

                fetch(url, { credentials: "same-origin" })
                    .then(function(resposta){
                        if (!resposta.ok) throw new Error("HTTP " + resposta.status);
                        return resposta.text();
                    })
                    .then(function(html){
                        var parser = new DOMParser();
                        var novoDoc = parser.parseFromString(html, "text/html");
                        var novoConteudo = novoDoc.querySelector(seletorConteudo);
                        var conteudoAtual = document.querySelector(seletorConteudo);

                        if (!novoConteudo || !conteudoAtual) {
                            window.location.href = url;
                            return;
                        }

                        conteudoAtual.innerHTML = novoConteudo.innerHTML;
                        document.title = novoDoc.title;

                        if (empilhar) {
                            window.history.pushState({ racr: true }, "", url);
                        }
                        window.scrollTo(0, 0);
                        document.dispatchEvent(new CustomEvent("racr:conteudo-trocado", { detail: { url: url } }));
                    })
                    .catch(function(){
                        window.location.href = url;
                    })
                    .finally(function(){
                        emNavegacao = false;
                        document.body.classList.remove("racr-navegando");
                    });
            }

            document.addEventListener("click", function(e){
                if (e.defaultPrevented || e.button !== 0) return;
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                var link = e.target.closest("a");
                if (!ehLinkNavegavel(link)) return;

                if (link.href === window.location.href) {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();
                trocarConteudo(link.href, true);
            });

            window.addEventListener("popstate", function(){
                trocarConteudo(window.location.href, false);
            });
        })();
    ' );
}
add_action( 'wp_enqueue_scripts', 'racr_enqueue_frontend' );
