/* =========================================================
   Rádio Agenda Cultural — carrosséis da Home (Fase 3B-2)

   Desktop (≥ 900px), com itens suficientes: carrossel INFINITO, com setas e
   avanço automático a cada 4,5 s. Os itens são triplicados (clones antes e
   depois, ocultos para leitores de tela e sem foco) e, ao fim de cada
   animação, a posição volta para o bloco do meio sem animar.
   - Pausa com o mouse ou o foco dentro, com a aba escondida e em
     "reduzir movimento" (nesse caso sem animação e sem avanço automático;
     as setas continuam funcionando).
   - Com poucos itens (não passam da largura visível), vira uma grade
     simples, sem setas nem avanço.
   Mobile (< 900px): sem JS; é rolagem lateral com encaixe, feita no CSS.

   Roda no carregamento e a cada troca de conteúdo (RAC.aoCarregar); as
   instâncias da página anterior são sempre desmontadas.
   ========================================================= */
(function () {
  'use strict';

  var INTERVALO = 4500;
  var TRANSICAO = 'transform .55s cubic-bezier(.4, 0, .2, 1)';
  var larga = window.matchMedia('(min-width: 900px)');
  var reduz = window.matchMedia('(prefers-reduced-motion: reduce)');
  var instancias = [];

  function mod(a, n) { return ((a % n) + n) % n; }

  function Carrossel(raiz) {
    var janela = raiz.querySelector('.rac-carrossel__janela');
    var trilho = raiz.querySelector('.rac-carrossel__trilho');
    var anterior = raiz.querySelector('[data-rac-prev]');
    var proximo = raiz.querySelector('[data-rac-next]');
    var originais = Array.prototype.slice.call(trilho.children);
    var n = originais.length;
    var indice = 0, passo = 0, ativo = false, ocupado = false;
    var temporizador = null, travaFim = null, pausado = false;

    function aplicar(animar) {
      trilho.style.transition = animar ? TRANSICAO : 'none';
      trilho.style.transform = 'translateX(' + (-indice * passo) + 'px)';
      trilho.setAttribute('data-pos', String(mod(indice - n, n)));
    }

    function clonar(el) {
      var c = el.cloneNode(true);
      c.setAttribute('aria-hidden', 'true');
      c.setAttribute('inert', '');
      c.setAttribute('data-rac-clone', '');
      return c;
    }

    function pararAuto() { if (temporizador) { clearInterval(temporizador); temporizador = null; } }

    function normalizar() {
      var nova = n + mod(indice - n, n);
      if (nova !== indice) { indice = nova; aplicar(false); }
      ocupado = false;
      if (travaFim) { clearTimeout(travaFim); travaFim = null; }
    }

    function mover(d) {
      if (!ativo || ocupado) return;
      ocupado = true;
      indice += d;
      var animar = !reduz.matches;
      aplicar(animar);
      if (!animar) { normalizar(); return; }
      // trava de segurança: se o navegador não avisar o fim da transição, destrava
      travaFim = setTimeout(normalizar, 800);
    }

    function iniciarAuto() {
      pararAuto();
      if (reduz.matches) return;
      temporizador = setInterval(function () {
        if (!pausado && !document.hidden) mover(1);
      }, INTERVALO);
    }

    function desmontar() {
      pararAuto();
      if (travaFim) { clearTimeout(travaFim); travaFim = null; }
      Array.prototype.slice.call(trilho.querySelectorAll('[data-rac-clone]')).forEach(function (c) { c.parentNode.removeChild(c); });
      trilho.style.transform = '';
      trilho.style.transition = '';
      trilho.removeAttribute('data-pos');
      raiz.classList.remove('is-infinito');
      ativo = false; ocupado = false; indice = 0;
    }

    function montar() {
      desmontar();
      if (!larga.matches || n < 2) return;
      passo = originais[1].offsetLeft - originais[0].offsetLeft;
      if (passo <= 0) return;
      var cs = getComputedStyle(janela);
      var larguraUtil = janela.clientWidth - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight);
      if (n <= Math.ceil(larguraUtil / passo)) return; // poucos itens: grade simples

      var antes = document.createDocumentFragment();
      originais.forEach(function (el) { antes.appendChild(clonar(el)); });
      trilho.insertBefore(antes, originais[0]);
      originais.forEach(function (el) { trilho.appendChild(clonar(el)); });

      indice = n;
      aplicar(false);
      raiz.classList.add('is-infinito');
      ativo = true;
      iniciarAuto();
    }

    function aoClicar(d) { return function () { mover(d); iniciarAuto(); }; }
    var cliqueAnt = aoClicar(-1), cliqueProx = aoClicar(1);
    function pausar() { pausado = true; }
    function retomar() { pausado = false; }

    if (anterior) anterior.addEventListener('click', cliqueAnt);
    if (proximo) proximo.addEventListener('click', cliqueProx);
    raiz.addEventListener('mouseenter', pausar);
    raiz.addEventListener('mouseleave', retomar);
    raiz.addEventListener('focusin', pausar);
    raiz.addEventListener('focusout', retomar);
    trilho.addEventListener('transitionend', function (e) { if (e.target === trilho) normalizar(); });

    montar();

    return {
      raiz: raiz, remontar: montar,
      destruir: function () {
        desmontar();
        if (anterior) anterior.removeEventListener('click', cliqueAnt);
        if (proximo) proximo.removeEventListener('click', cliqueProx);
        raiz.removeEventListener('mouseenter', pausar);
        raiz.removeEventListener('mouseleave', retomar);
        raiz.removeEventListener('focusin', pausar);
        raiz.removeEventListener('focusout', retomar);
      },
    };
  }

  function iniciar() {
    instancias.forEach(function (i) { i.destruir(); });
    instancias = Array.prototype.slice.call(document.querySelectorAll('[data-rac-carrossel]')).map(Carrossel);
  }

  var espera = null;
  function aoRedimensionar() {
    clearTimeout(espera);
    espera = setTimeout(function () { instancias.forEach(function (i) { i.remontar(); }); }, 200);
  }
  window.addEventListener('resize', aoRedimensionar);
  if (larga.addEventListener) larga.addEventListener('change', aoRedimensionar);

  if (window.RAC && typeof window.RAC.aoCarregar === 'function') {
    window.RAC.aoCarregar(iniciar);
  } else {
    document.addEventListener('DOMContentLoaded', iniciar);
    document.addEventListener('racr:conteudo-trocado', iniciar);
  }
})();
