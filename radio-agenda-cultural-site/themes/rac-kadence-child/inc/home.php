<?php
/**
 * Lógica da página inicial: textos padrão, estações mais ouvidas,
 * "Descoberta do editor" e limpeza diária do cache.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Textos padrão (conteúdo provisório do mockup) usados quando o campo ACF está vazio. */
function rac_home_padroes() {
    return array(
        'home_hero_selo'          => 'Direto de Votuporanga, SP — Brasil',
        'home_hero_titulo'        => 'Música, cultura e boas conversas, 24 horas por dia',
        'home_hero_texto'         => 'A Agenda Cultural é uma rádio web independente, feita no interior paulista por gente que ama música e literatura. São várias estações temáticas tocando sem parar, uma biblioteca gratuita e um blog com crônicas e entrevistas. Escolha uma trilha, aperte o play e fique à vontade.',
        'home_fundador_nome'      => 'Nome do Fundador',
        'home_fundador_papel'     => 'fundador e curador',
        'home_fundador_texto'     => 'Radialista há 20 anos, dividindo discos, livros e histórias.',
        'home_destaque1_texto'    => 'Jazz, MPB, clássicos, raízes e poesia — uma trilha para cada momento.',
        'home_destaque2_titulo'   => 'Conteúdo literário gratuito',
        'home_destaque2_texto'    => 'Contos, antologias e cadernos para baixar e ler quando quiser.',
        'home_destaque3_titulo'   => 'Crônicas, bastidores e entrevistas',
        'home_destaque3_texto'    => 'Histórias da cena cultural contadas por quem faz a rádio.',
        'home_estacoes_subtitulo' => 'As duas mais ouvidas e uma descoberta escolhida pelo editor.',
        'home_sobre_titulo'       => 'Uma rádio que nasceu de uma coleção de discos',
        'home_sobre_texto'        => 'Tudo começou em Votuporanga, com uma estante cheia de vinis e a vontade de compartilhar. Hoje a Agenda Cultural reúne estações temáticas, uma biblioteca aberta e uma comunidade de ouvintes que cresce a cada dia — sem perder o jeito de rádio feita em casa.',
    );
}

/** Texto de um campo da página inicial (ou o padrão provisório). */
function rac_home_texto( $nome, $pagina_id = null ) {
    $padroes = rac_home_padroes();
    return (string) rac_campo( $nome, $pagina_id, isset( $padroes[ $nome ] ) ? $padroes[ $nome ] : '' );
}

/** Ouvintes de uma estação (campo "Ouvintes"; vazio conta como 0). */
function rac_ouvintes( $id ) {
    return max( 0, (int) rac_campo( 'ouvintes', $id, 0 ) );
}

/**
 * As N estações com mais ouvintes. Empate (ou ninguém com número
 * preenchido) segue a ordem do plugin: "ordem_de_exibicao" e depois
 * alfabética.
 *
 * @return array Estações de rac_listar_estacoes() com a chave 'ouvintes'.
 */
function rac_estacoes_mais_ouvidas( $n = 2 ) {
    $lista = rac_listar_estacoes();
    foreach ( $lista as $posicao => $e ) {
        $lista[ $posicao ]['ouvintes'] = rac_ouvintes( $e['id'] );
        $lista[ $posicao ]['_pos']     = $posicao;
    }
    usort( $lista, function ( $a, $b ) {
        $cmp = $b['ouvintes'] <=> $a['ouvintes'];
        return $cmp !== 0 ? $cmp : $a['_pos'] <=> $b['_pos'];
    } );
    return array_slice( $lista, 0, $n );
}

/**
 * "Descoberta do editor": uma estação que NÃO esteja entre as mais ouvidas.
 *
 *  - modo "fixar": a estação escolhida no painel (se ela estiver entre as
 *    mais ouvidas, cai no sorteio);
 *  - modo "sorteio" (padrão): uma estação por dia, em ordem embaralhada de
 *    forma estável. As estações do "pool" são percorridas num ciclo sem
 *    repetir; a ordem de cada ciclo é sorteada por uma semente fixa
 *    (ciclo + ID), então o resultado é igual para todos os visitantes durante
 *    o dia e muda sozinho à meia-noite do fuso do site.
 *
 * @param int[]       $ids_excluir IDs das mais ouvidas.
 * @param int|null    $pagina_id   Página inicial (onde ficam os campos).
 * @param string|null $dia         'Y-m-d' (só para testes; padrão: hoje).
 * @return array|null Estação ou null se não houver estação disponível.
 */
function rac_estacao_descoberta( $ids_excluir, $pagina_id = null, $dia = null ) {
    $ids_excluir = array_map( 'intval', (array) $ids_excluir );
    $pool        = array();
    foreach ( rac_listar_estacoes() as $e ) {
        if ( ! in_array( (int) $e['id'], $ids_excluir, true ) ) $pool[] = $e;
    }
    if ( ! $pool ) return null;

    if ( rac_campo( 'home_descoberta_modo', $pagina_id, 'sorteio' ) === 'fixar' ) {
        $fixa = (int) rac_campo( 'home_descoberta_estacao', $pagina_id, 0 );
        foreach ( $pool as $e ) {
            if ( (int) $e['id'] === $fixa ) return $e;
        }
    }

    $dia   = $dia ? $dia : wp_date( 'Y-m-d' );
    $dias  = (int) floor( ( new DateTime( $dia . ' 00:00:00', new DateTimeZone( 'UTC' ) ) )->getTimestamp() / 86400 );
    $total = count( $pool );
    $ciclo = intdiv( $dias, $total );

    usort( $pool, function ( $a, $b ) use ( $ciclo ) {
        $cmp = crc32( $ciclo . '-' . $a['id'] ) <=> crc32( $ciclo . '-' . $b['id'] );
        return $cmp !== 0 ? $cmp : (int) $a['id'] <=> (int) $b['id'];
    } );
    return $pool[ $dias % $total ];
}

/* ============================================================
   CACHE (LiteSpeed): a Home muda por dia (descoberta) e quando uma
   estação é salva (ouvintes). Limpa só a página inicial.
   ============================================================ */
function rac_purgar_home() {
    do_action( 'litespeed_purge_url', home_url( '/' ) );
}
add_action( 'rac_limpar_cache_home', 'rac_purgar_home' );
add_action( 'save_post_estacao', 'rac_purgar_home' );

/** Agenda a limpeza diária às 00:05 (fuso do site). */
function rac_agendar_limpeza_diaria() {
    if ( ! wp_next_scheduled( 'rac_limpar_cache_home' ) ) {
        $inicio = new DateTime( 'tomorrow 00:05', wp_timezone() );
        wp_schedule_event( $inicio->getTimestamp(), 'daily', 'rac_limpar_cache_home' );
    }
}
add_action( 'init', 'rac_agendar_limpeza_diaria' );

/** Remove o agendamento ao trocar de tema. */
function rac_desagendar_limpeza() {
    wp_clear_scheduled_hook( 'rac_limpar_cache_home' );
}
add_action( 'switch_theme', 'rac_desagendar_limpeza' );
