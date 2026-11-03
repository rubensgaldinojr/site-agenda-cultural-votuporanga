/* =========================================================
   Rádio Agenda Cultural — JS base do tema (Fase 1)

   1. Chave de tema claro/escuro (salva em localStorage["rac-tema"]).
   2. Menu mobile (☰).
   3. Item de menu ativo.
   4. Registro de inicializadores (window.RAC.aoCarregar).

   IMPORTANTE — navegação persistente: o plugin racr-player-radio troca
   só o conteúdo de "#primary" e depois dispara o evento
   "racr:conteudo-trocado" no document. O cabeçalho e o rodapé NÃO são
   recarregados, e <script> dentro do conteúdo trocado não executa.
   Por isso:
     • tudo que mexe no header/footer (menu ativo, fechar menu mobile)
       é reaplicado nesse evento;
     • componentes das próximas fases devem usar delegação de eventos no
       document ou se registrar com RAC.aoCarregar(fn), que roda no
       carregamento da página E a cada troca de conteúdo.
   ========================================================= */
(function () {
  'use strict';

  var CHAVE_TEMA = 'rac-tema';
  var html = document.documentElement;

  /* ---------- 4. Registro de inicializadores ---------- */
  var inicializadores = [];
  var RAC = window.RAC = window.RAC || {};
  RAC.aoCarregar = function (fn) {
    inicializadores.push(fn);
    if (document.readyState !== 'loading') { fn(); }
  };
  function rodarInicializadores() {
    inicializadores.forEach(function (fn) {
      try { fn(); } catch (e) { console.error('rac: erro em inicializador', e); }
    });
  }

  /* ---------- 1. Tema claro/escuro ---------- */
  function temaAtual() {
    return html.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  }

  function sincronizarChaves() {
    var escuro = temaAtual() === 'dark';
    document.querySelectorAll('.rac-toggle').forEach(function (b) {
      b.setAttribute('aria-checked', escuro ? 'true' : 'false');
    });
  }

  function aplicarTema(t, salvar) {
    html.setAttribute('data-theme', t);
    if (salvar) {
      try { localStorage.setItem(CHAVE_TEMA, t); } catch (e) {}
    }
    sincronizarChaves();
    // avisa outros scripts/chaves (mesmo nome de evento do mockup)
    window.dispatchEvent(new CustomEvent('rac-tema', { detail: t }));
  }

  document.addEventListener('click', function (e) {
    var chave = e.target.closest('.rac-toggle');
    if (chave) {
      aplicarTema(temaAtual() === 'dark' ? 'light' : 'dark', true);
    }
  });

  // mantém abas abertas sincronizadas
  window.addEventListener('storage', function (e) {
    if (e.key === CHAVE_TEMA && (e.newValue === 'dark' || e.newValue === 'light')) {
      aplicarTema(e.newValue, false);
    }
  });

  /* ---------- 2. Menu mobile ---------- */
  function painelMenu() { return document.getElementById('rac-menu-mobile'); }
  function botaoMenu() { return document.getElementById('rac-menu-btn'); }

  function definirMenu(aberto) {
    var painel = painelMenu(), botao = botaoMenu();
    if (!painel || !botao) return;
    painel.hidden = !aberto;
    botao.setAttribute('aria-expanded', aberto ? 'true' : 'false');
  }

  document.addEventListener('click', function (e) {
    var botao = e.target.closest('#rac-menu-btn');
    var painel = painelMenu();
    if (botao) {
      definirMenu(painel ? painel.hidden : false);
      return;
    }
    if (!painel || painel.hidden) return;
    // clicar num link do menu ou fora dele fecha o painel
    if (e.target.closest('#rac-menu-mobile a') || !e.target.closest('#rac-menu-mobile')) {
      definirMenu(false);
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') definirMenu(false);
  });

  /* ---------- 3. Item de menu ativo ----------
     Mesma regra de rac_marcar_item_ativo() (inc/helpers.php): entre os
     links do menu, vence o de caminho mais longo que case por igualdade
     ou prefixo; link com "?query" só vale se a query for igual. */
  function semBarraFinal(p) { return p.replace(/\/+$/, ''); }

  function marcarMenuAtivo() {
    var caminho = semBarraFinal(window.location.pathname);
    var consulta = window.location.search;

    document.querySelectorAll('.rac-nav, #rac-menu-mobile nav').forEach(function (menu) {
      var links = Array.prototype.slice.call(menu.querySelectorAll('a'));
      var melhor = -1;
      var pontos = links.map(function (a) {
        var u;
        try { u = new URL(a.href, window.location.href); } catch (err) { return -1; }
        if (u.origin !== window.location.origin) return -1;
        var c = semBarraFinal(u.pathname);
        if (c === '') return -1;
        if (u.search !== '' && u.search !== consulta) return -1;
        if (caminho === c || caminho.indexOf(c + '/') === 0) {
          var p = c.length * 10 + (u.search !== '' ? 5 : 0);
          if (p > melhor) melhor = p;
          return p;
        }
        return -1;
      });
      links.forEach(function (a, i) {
        var ativo = melhor >= 0 && pontos[i] === melhor;
        a.classList.toggle('is-ativo', ativo);
        if (ativo) { a.setAttribute('aria-current', 'page'); } else { a.removeAttribute('aria-current'); }
      });
    });
  }

  /* ---------- Ciclo de vida ---------- */
  function aoTrocarConteudo() {
    definirMenu(false);
    marcarMenuAtivo();
    sincronizarChaves();
    rodarInicializadores();
  }

  document.addEventListener('racr:conteudo-trocado', aoTrocarConteudo);

  function iniciar() {
    sincronizarChaves();
    marcarMenuAtivo();
    rodarInicializadores();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciar);
  } else {
    iniciar();
  }
})();
