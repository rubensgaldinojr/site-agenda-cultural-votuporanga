/* =========================================================
   Rádio Agenda Cultural — bolinhas dos carrosséis no mobile

   No celular os carrosséis são faixas de rolagem lateral (sem JS). Para deixar claro que há mais cards,
   este script desenha, embaixo de cada faixa, uma fileira de bolinhas: a da posição atual fica alongada e
   laranja, e tocar numa bolinha leva até aquele ponto. Há uma bolinha por posição de encaixe alcançável
   (normalmente, uma por card; os últimos cards, que não conseguem chegar ao início da faixa, dividem a última).

   Faixas com data-rac-auto="ms" (topo da Home) também avançam sozinhas, como um banner: a cada N ms, voltando ao
   primeiro card no fim. Param de vez quando a pessoa interage (toque, roda do mouse, teclado, bolinhas), pausam com o
   mouse ou o foco em cima, com a aba escondida e com a faixa fora da tela, e não rodam com "reduzir movimento".

   Faixas atendidas: topo da Home (4 cards), Estações em destaque, Blog, Literatura e "Da biblioteca".
   No desktop as bolinhas ficam escondidas (o carrossel infinito tem as próprias setas).
   Roda no carregamento e a cada troca de conteúdo (RAC.aoCarregar); o que é ligado fora do #primary
   (janela, observadores) é desligado antes de religar.
   ========================================================= */
(function () {
  'use strict';

  var SELETOR = '.rac-hero-cards, .rac-home-destaques-estacoes, .rac-carrossel__janela, .rac-biblioteca__lista';
  var mq = window.matchMedia('(max-width: 899px)');
  var reduz = window.matchMedia('(prefers-reduced-motion: reduce)');
  var limpezas = [];

  function iniciar() {
    limpezas.forEach(function (f) { try { f(); } catch (e) {} });
    limpezas = [];
    Array.prototype.forEach.call(document.querySelectorAll(SELETOR), montar);
  }

  function montar(faixa) {
    var pontos = document.createElement('div');
    pontos.className = 'rac-pontos';
    pontos.setAttribute('role', 'group');
    pontos.setAttribute('aria-label', 'Posição no carrossel');
    pontos.hidden = true;
    faixa.insertAdjacentElement('afterend', pontos);

    var total = 0, ativo = -1, quadro = 0, posicoes = [];

    function maximo() { return Math.max(0, faixa.scrollWidth - faixa.clientWidth); }

    // Posições (scrollLeft) em que cada card encaixa no início da faixa, limitadas ao fim da rolagem, sem repetir.
    function calcularPosicoes() {
      var raiz = faixa;
      if (faixa.children.length === 1 && getComputedStyle(faixa.firstElementChild).display === 'flex') raiz = faixa.firstElementChild;
      var itens = Array.prototype.filter.call(raiz.children, function (el) {
        return !el.hasAttribute('data-rac-clone') && el.offsetWidth > 0;
      });
      var m = maximo();
      var base = faixa.getBoundingClientRect().left - faixa.scrollLeft;
      var recuo = parseFloat(getComputedStyle(faixa).scrollPaddingLeft) || 0;
      var lista = [];
      itens.forEach(function (el) {
        var p = Math.max(0, Math.min(m, el.getBoundingClientRect().left - base - recuo));
        if (!lista.length || p - lista[lista.length - 1] > 4) lista.push(p);
      });
      return m < 8 ? [] : lista;
    }
    function indice() {
      var x = faixa.scrollLeft, melhor = 0, d = Infinity;
      posicoes.forEach(function (p, i) { var dd = Math.abs(p - x); if (dd < d) { d = dd; melhor = i; } });
      return melhor;
    }
    function marcar() {
      var i = indice();
      if (i === ativo) return;
      ativo = i;
      Array.prototype.forEach.call(pontos.children, function (b, k) {
        if (k === i) b.setAttribute('aria-current', 'true'); else b.removeAttribute('aria-current');
      });
    }
    function desenhar() {
      posicoes = calcularPosicoes();
      var n = posicoes.length;
      if (n !== total) {
        total = n; ativo = -1;
        pontos.textContent = '';
        for (var k = 0; k < n && n > 1; k++) {
          var b = document.createElement('button');
          b.type = 'button';
          b.className = 'rac-pontos__ponto';
          b.setAttribute('data-rac-ponto', String(k));
          b.setAttribute('aria-label', 'Ir para o item ' + (k + 1) + ' de ' + n);
          pontos.appendChild(b);
        }
      }
      pontos.hidden = !(total > 1 && mq.matches);
      marcar();
    }

    function aoRolar() {
      if (quadro) return;
      quadro = requestAnimationFrame(function () { quadro = 0; marcar(); });
    }
    function aoClicar(e) {
      var b = e.target.closest('[data-rac-ponto]');
      if (!b || total < 2 || posicoes[parseInt(b.getAttribute('data-rac-ponto'), 10)] === undefined) return;
      var k = parseInt(b.getAttribute('data-rac-ponto'), 10);
      faixa.scrollTo({ left: posicoes[k], behavior: reduz.matches ? 'auto' : 'smooth' });
    }

    faixa.addEventListener('scroll', aoRolar, { passive: true });
    pontos.addEventListener('click', aoClicar);
    window.addEventListener('resize', desenhar);
    mq.addEventListener('change', desenhar);
    var obs = null;
    if (typeof ResizeObserver === 'function') {
      obs = new ResizeObserver(desenhar);
      obs.observe(faixa);
      if (faixa.firstElementChild) obs.observe(faixa.firstElementChild);
    }
    // imagens que terminam de carregar mudam a largura do conteúdo
    window.addEventListener('load', desenhar);

    /* ---- auto-avanço (só nas faixas com data-rac-auto) ---- */
    var intervalo = parseInt(faixa.getAttribute('data-rac-auto'), 10);
    var relogio = 0, parado = false, sobre = false;
    if (intervalo > 0 && !reduz.matches) {
      var parar = function () { parado = true; };
      var entrar = function () { sobre = true; };
      var sair = function () { sobre = false; };
      var noEcra = function () { var r = faixa.getBoundingClientRect(); return r.bottom > 0 && r.top < window.innerHeight; };
      var passar = function () {
        if (parado || sobre || document.hidden || !mq.matches || total < 2 || !noEcra()) return;
        faixa.scrollTo({ left: posicoes[(Math.max(0, ativo) + 1) % total], behavior: 'smooth' });
      };
      ['pointerdown', 'touchstart', 'wheel', 'keydown'].forEach(function (ev) { faixa.addEventListener(ev, parar, { passive: true }); });
      pontos.addEventListener('click', parar);
      faixa.addEventListener('mouseenter', entrar); faixa.addEventListener('mouseleave', sair);
      faixa.addEventListener('focusin', entrar); faixa.addEventListener('focusout', sair);
      relogio = setInterval(passar, intervalo);
    }

    limpezas.push(function () {
      if (relogio) clearInterval(relogio);
      faixa.removeEventListener('scroll', aoRolar);
      window.removeEventListener('resize', desenhar);
      window.removeEventListener('load', desenhar);
      mq.removeEventListener('change', desenhar);
      if (obs) obs.disconnect();
      if (quadro) cancelAnimationFrame(quadro);
      if (pontos.parentNode) pontos.parentNode.removeChild(pontos);
    });
    desenhar();
  }

  if (window.RAC && typeof window.RAC.aoCarregar === 'function') {
    window.RAC.aoCarregar(iniciar);
  } else {
    document.addEventListener('DOMContentLoaded', iniciar);
    document.addEventListener('racr:conteudo-trocado', iniciar);
  }
})();
