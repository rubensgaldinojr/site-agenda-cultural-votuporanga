/* =========================================================
   Rádio Agenda Cultural — Blog, Autor e Post (Fase 4)

   1. Filtros do Blog: no celular, o botão "Filtros" abre/fecha o painel; os
      seletores (<details>) fecham ao clicar fora, ao escolher uma opção ou com Esc.
      As opções são LINKS (a navegação persistente da rádio cuida da troca),
      então nada recarrega a página e o áudio não para.
   2. Vídeo: clicar num capítulo recarrega o player a partir daquele ponto.
   3. Aviso "A rádio está tocando": aparece junto de vídeos e players de artista
      enquanto a rádio toca, com um botão para pausá-la (os dois sons se misturariam).
   4. Página do autor: "Carregar mais posts" busca a próxima página (fetch) e
      acrescenta os cards na grade, sem sair da página.

   Roda no carregamento e a cada troca de conteúdo (RAC.aoCarregar). Tudo que é
   ligado fora do #primary (document, <audio>) é desligado antes de religar.
   ========================================================= */
(function () {
  'use strict';

  var limpezas = [];
  function aoLimpar(fn) { limpezas.push(fn); }

  function ouvir(alvo, tipo, fn, opcoes) {
    alvo.addEventListener(tipo, fn, opcoes);
    aoLimpar(function () { alvo.removeEventListener(tipo, fn, opcoes); });
  }

  function iniciar() {
    limpezas.forEach(function (f) { try { f(); } catch (e) {} });
    limpezas = [];

    iniciarFiltros();
    iniciarVideo();
    iniciarAvisoRadio();
    iniciarMais();
  }

  /* ---------- 1. Filtros ---------- */
  function iniciarFiltros() {
    var barra = document.querySelector('[data-rac-filtros]');
    if (!barra) return;
    var abrir = barra.querySelector('[data-rac-filtros-abrir]');
    if (abrir) {
      ouvir(abrir, 'click', function () {
        var aberto = barra.classList.toggle('is-aberto');
        abrir.setAttribute('aria-expanded', aberto ? 'true' : 'false');
      });
    }
    var detalhes = Array.prototype.slice.call(barra.querySelectorAll('details[data-rac-filtro]'));
    // abrir um seletor fecha os outros
    detalhes.forEach(function (d) {
      ouvir(d, 'toggle', function () {
        if (!d.open) return;
        detalhes.forEach(function (o) { if (o !== d) o.open = false; });
      });
    });
    ouvir(document, 'click', function (e) {
      detalhes.forEach(function (d) { if (d.open && !d.contains(e.target)) d.open = false; });
    });
    ouvir(document, 'keydown', function (e) {
      if (e.key !== 'Escape') return;
      detalhes.forEach(function (d) { if (d.open) { d.open = false; var s = d.querySelector('summary'); if (s) s.focus(); } });
    });
  }

  /* ---------- 2. Capítulos do vídeo ---------- */
  function srcVideo(plataforma, id, inicio) {
    if (plataforma === 'youtube') {
      return 'https://www.youtube-nocookie.com/embed/' + id + '?rel=0&autoplay=1' + (inicio > 0 ? '&start=' + inicio : '');
    }
    return 'https://player.vimeo.com/video/' + id + '?autoplay=1' + (inicio > 0 ? '#t=' + inicio + 's' : '');
  }

  function iniciarVideo() {
    var raiz = document.querySelector('[data-rac-video]');
    if (!raiz) return;
    var quadro = raiz.querySelector('iframe');
    if (!quadro) return;
    ouvir(raiz, 'click', function (e) {
      var b = e.target.closest('[data-rac-capitulo]');
      if (!b) return;
      var seg = parseInt(b.getAttribute('data-rac-capitulo'), 10) || 0;
      quadro.src = srcVideo(raiz.getAttribute('data-plataforma'), raiz.getAttribute('data-id'), seg);
      var topo = quadro.getBoundingClientRect().top;
      if (topo < 0 || topo > window.innerHeight * 0.6) quadro.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  }

  /* ---------- 3. Aviso "a rádio está tocando" ---------- */
  function iniciarAvisoRadio() {
    var avisos = Array.prototype.slice.call(document.querySelectorAll('[data-rac-aviso-radio]'));
    if (!avisos.length) return;
    var audio = document.getElementById('racr-audio');
    if (!audio) return;

    function atualizar() {
      var tocando = !audio.paused && !audio.ended;
      avisos.forEach(function (a) { a.hidden = !tocando; });
    }
    ['playing', 'play', 'pause', 'ended', 'emptied'].forEach(function (nome) { ouvir(audio, nome, atualizar); });
    avisos.forEach(function (a) {
      var b = a.querySelector('[data-rac-pausar-radio]');
      if (b) ouvir(b, 'click', function () { audio.pause(); atualizar(); });
    });
    atualizar();
  }

  /* ---------- 4. Autor: carregar mais posts ---------- */
  function iniciarMais() {
    var botao = document.querySelector('[data-rac-mais]');
    var lista = document.querySelector('[data-rac-mais-lista]');
    if (!botao || !lista) return;
    var carregando = false;

    ouvir(botao, 'click', function (e) {
      // o clique é tratado aqui (e não pela navegação persistente), para acrescentar à lista
      e.preventDefault();
      e.stopPropagation();
      if (carregando) return;
      carregando = true;
      var texto = botao.textContent;
      botao.textContent = 'Carregando…';

      fetch(botao.href, { credentials: 'same-origin' })
        .then(function (r) { if (!r.ok) throw new Error('http ' + r.status); return r.text(); })
        .then(function (html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          var novos = doc.querySelectorAll('[data-rac-mais-lista] > *');
          Array.prototype.forEach.call(novos, function (el) { lista.appendChild(document.importNode(el, true)); });
          var proximo = doc.querySelector('[data-rac-mais]');
          if (proximo) { botao.href = proximo.href; botao.textContent = texto; }
          else { botao.parentNode.removeChild(botao); }
        })
        .catch(function () { botao.textContent = 'Não deu certo. Tentar de novo'; })
        .then(function () { carregando = false; });
    }, true);
  }

  if (window.RAC && typeof window.RAC.aoCarregar === 'function') {
    window.RAC.aoCarregar(iniciar);
  } else {
    document.addEventListener('DOMContentLoaded', iniciar);
    document.addEventListener('racr:conteudo-trocado', iniciar);
  }
})();
