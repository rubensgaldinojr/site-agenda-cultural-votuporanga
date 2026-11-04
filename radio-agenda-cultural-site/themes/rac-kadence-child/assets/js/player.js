/* =========================================================
   Rádio Agenda Cultural — PlayerFixo e cards de estação (Fase 2)

   Este script NÃO toca áudio e não altera a lógica do plugin
   racr-player-radio. Ele só:
     1. divide o texto "Artista - Música" (escrito pelo plugin em
        #racr-status) em duas linhas, "faixa" e "artista";
     2. mantém a capa da estação atual no player;
     3. controla o volume (audio.volume) e lembra o valor;
     4. liga os cards de estação (.rac-card-est), os tiles (.rac-tile-est) e o
        botão "Ouvir agora" da página da estação (.rac-cta-estacao) ao player:
        clicar aciona o botão correspondente do próprio plugin, e o elemento da
        estação que está tocando recebe .is-tocando. No card, qualquer ponto
        toca (menos o link "Ver estação"); no tile e no botão, só o botão;
     5. fecha o painel de estações (✕, clicar fora, Esc).

   O que o plugin controla, e este script apenas observa/aciona:
     #racr-player (classes racr-tocando / racr-carregando / racr-erro /
     racr-expandido), #racr-status, #racr-nome-estacao, #racr-botao-play,
     #racr-botao-expandir e os botões .racr-estacao.
   ========================================================= */
(function () {
  'use strict';

  var player = document.getElementById('racr-player');
  var audio = document.getElementById('racr-audio');
  var status = document.getElementById('racr-status');
  var nomeEl = document.getElementById('racr-nome-estacao');
  var lista = document.getElementById('racr-lista-estacoes');
  var botaoPlay = document.getElementById('racr-botao-play');
  var botaoExpandir = document.getElementById('racr-botao-expandir');
  if (!player || !audio || !status || !lista || !botaoPlay || !botaoExpandir) return;

  // Textos de estado que o plugin escreve em #racr-status (não são música).
  var ESTADOS = ['parado', 'carregando...', 'ao vivo', 'não foi possível tocar', 'erro no stream'];

  function todos(seletor) { return document.querySelectorAll(seletor); }
  function escrever(seletor, texto) {
    todos(seletor).forEach(function (el) { el.textContent = texto; });
  }

  /* ---------- 1. "Tocando agora": faixa + artista ---------- */
  function atualizarFaixa() {
    var texto = (status.textContent || '').trim();
    var rotulo, faixa, artista = '';

    if (texto === '' || ESTADOS.indexOf(texto.toLowerCase()) !== -1) {
      rotulo = 'Status';
      faixa = texto || 'Parado';
    } else {
      rotulo = 'Tocando agora';
      // padrão dos streams: "Artista - Música" (divide no PRIMEIRO " - ")
      var i = texto.indexOf(' - ');
      if (i > 0) {
        artista = texto.slice(0, i).trim();
        faixa = texto.slice(i + 3).trim();
      } else {
        faixa = texto;
      }
    }

    var estacao = (nomeEl.textContent || '').trim();
    escrever('[data-rac="rotulo"]', rotulo);
    escrever('[data-rac="faixa"]', faixa);
    escrever('[data-rac="artista"]', artista);
    escrever('[data-rac="linha2-m"]', artista ? artista + ' · ' + estacao : estacao);
  }

  /* ---------- 2. Capa da estação atual ---------- */
  function estacaoAtiva() { return lista.querySelector('.racr-estacao-ativa'); }

  function atualizarCapa() {
    var ativa = estacaoAtiva();
    var url = ativa ? ativa.getAttribute('data-capa') : '';
    todos('[data-rac="capa"]').forEach(function (el) {
      el.style.backgroundImage = url ? 'url("' + url.replace(/"/g, '%22') + '")' : '';
    });
  }

  /* ---------- 3. Volume ---------- */
  function iniciarVolume() {
    var campo = document.getElementById('rac-volume');
    if (!campo) return;
    var caixa = campo.closest('.rac-player__volume');

    // iOS/Safari ignora audio.volume: nesse caso o controle não aparece.
    var suporta = false;
    try {
      var anterior = audio.volume;
      audio.volume = 0.5;
      suporta = Math.abs(audio.volume - 0.5) < 0.01;
      audio.volume = anterior;
    } catch (e) {}
    if (!suporta) { if (caixa) caixa.hidden = true; return; }

    var salvo = null;
    try { salvo = localStorage.getItem('rac-volume'); } catch (e) {}
    var inicial = (salvo !== null && salvo !== '' && !isNaN(Number(salvo))) ? Math.min(100, Math.max(0, Number(salvo))) : 100;
    campo.value = inicial;
    campo.style.setProperty('--vol', inicial + '%');   // parte preenchida da barra
    audio.volume = inicial / 100;

    campo.addEventListener('input', function () {
      var v = Number(campo.value);
      campo.style.setProperty('--vol', v + '%');
      audio.volume = v / 100;
      try { localStorage.setItem('rac-volume', String(v)); } catch (e) {}
    });
  }

  /* ---------- 4. Cards de estação ---------- */
  function tocandoOuCarregando() {
    return player.classList.contains('racr-tocando') || player.classList.contains('racr-carregando');
  }

  // Elementos de estação que acompanham o estado do player.
  var SELETOR_ESTACOES = '.rac-card-est, .rac-tile-est, .rac-cta-estacao';

  function sincronizarCards() {
    var ativa = estacaoAtiva();
    var idAtual = ativa ? ativa.getAttribute('data-estacao-id') : null;
    var tocando = tocandoOuCarregando();

    todos(SELETOR_ESTACOES).forEach(function (el) {
      var ligado = tocando && idAtual !== null && el.getAttribute('data-estacao-id') === idAtual;
      el.classList.toggle('is-tocando', ligado);
      var botao = el.hasAttribute('data-rac-tocar') ? el : el.querySelector('[data-rac-tocar]');
      if (botao) botao.setAttribute('aria-pressed', ligado ? 'true' : 'false');
      if (el.classList.contains('rac-card-est--compact')) {
        var rotulo = el.querySelector('.rac-card-est__rotulo');
        if (rotulo) rotulo.textContent = ligado ? 'Tocando' : 'Ao vivo';
      }
    });
  }

  function tocarEstacaoPorId(id) {
    var ativa = estacaoAtiva();
    if (ativa && ativa.getAttribute('data-estacao-id') === String(id)) {
      botaoPlay.click(); // mesma estação: o botão play/pause do plugin decide
      return;
    }
    var botao = lista.querySelector('.racr-estacao[data-estacao-id="' + String(id).replace(/"/g, '') + '"]');
    if (botao) botao.click(); // outra estação: reaproveita o clique da lista do plugin
  }

  document.addEventListener('click', function (e) {
    // botão de tocar (tile, "Ouvir agora")
    var botao = e.target.closest('[data-rac-tocar]');
    if (botao) {
      var dono = botao.closest('[data-estacao-id]');
      if (dono) tocarEstacaoPorId(dono.getAttribute('data-estacao-id'));
      return;
    }
    // card: qualquer ponto toca, menos links ("Ver estação" navega normalmente)
    var card = e.target.closest('.rac-card-est');
    if (!card || e.target.closest('a')) return;
    tocarEstacaoPorId(card.getAttribute('data-estacao-id'));
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    var card = e.target.closest ? e.target.closest('.rac-card-est--compact') : null;
    if (!card || e.target !== card) return;
    e.preventDefault();
    tocarEstacaoPorId(card.getAttribute('data-estacao-id'));
  });

  /* ---------- 5. Fechar o painel de estações ---------- */
  function painelAberto() { return player.classList.contains('racr-expandido'); }

  document.addEventListener('click', function (e) {
    if (!painelAberto()) return;
    if (e.target.closest('#racr-botao-expandir')) return; // o plugin já alterna
    if (e.target.closest('#rac-player-fechar')) { botaoExpandir.click(); return; }
    if (!e.target.closest('.rac-player__painel')) botaoExpandir.click(); // clique fora
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && painelAberto()) botaoExpandir.click();
  });

  /* ---------- Observadores (o plugin muda classes e textos) ---------- */
  new MutationObserver(atualizarFaixa).observe(status, { childList: true, characterData: true, subtree: true });
  new MutationObserver(atualizarFaixa).observe(nomeEl, { childList: true, characterData: true, subtree: true });
  new MutationObserver(function () { atualizarCapa(); sincronizarCards(); })
    .observe(lista, { attributes: true, attributeFilter: ['class'], subtree: true });
  new MutationObserver(function () {
    sincronizarCards();
    // ao abrir a lista, leva a estação atual para a área visível (só dentro da lista)
    if (painelAberto()) {
      var ativa = estacaoAtiva();
      if (ativa) {
        var topo = ativa.offsetTop - lista.offsetTop;
        if (topo < lista.scrollTop || topo + ativa.offsetHeight > lista.scrollTop + lista.clientHeight) {
          lista.scrollTop = Math.max(0, topo - (lista.clientHeight - ativa.offsetHeight) / 2);
        }
      }
    }
  }).observe(player, { attributes: true, attributeFilter: ['class'] });

  /* ---------- Início e navegação persistente ---------- */
  iniciarVolume();
  atualizarFaixa();
  atualizarCapa();

  // Cards novos chegam a cada troca de conteúdo (#primary): resincroniza.
  function aoCarregar() {
    sincronizarCards();
    if (painelAberto()) botaoExpandir.click(); // navegou: fecha a lista aberta
  }
  if (window.RAC && typeof window.RAC.aoCarregar === 'function') {
    window.RAC.aoCarregar(aoCarregar);
  } else {
    sincronizarCards();
    document.addEventListener('racr:conteudo-trocado', aoCarregar);
  }
})();
