/* =========================================================
   Rádio Agenda Cultural — Recados dos ouvintes (Fase 3B-2)

   1. Paginação no navegador: 3 recados por página no desktop (com
      números) e 4 no mobile ("1 de N"), dando a volta no fim da lista.
   2. Formulário: "Deixe seu recado" abre o formulário; o envio é feito
      com fetch para wp-comments-post.php (a página NÃO recarrega, então o
      áudio não para). O WordPress cuida da moderação; aqui só se mostra o
      resultado. Sucesso = o WordPress respondeu com redirecionamento.
      O tempo entre abrir e enviar vai em "rac_ms" (o servidor exige ≥ 3 s)
      e o campo "rac_site" é uma armadilha para robôs.

   Roda no carregamento e a cada troca de conteúdo (RAC.aoCarregar).
   ========================================================= */
(function () {
  'use strict';

  var largaMq = window.matchMedia('(min-width: 900px)');
  var raizAtual = null;
  var aoMudarTamanho = null;

  function porPagina() { return largaMq.matches ? 3 : 4; }
  function mod(a, n) { return ((a % n) + n) % n; }

  function iniciar() {
    var raiz = document.querySelector('[data-rac-recados]');
    if (aoMudarTamanho) { largaMq.removeEventListener('change', aoMudarTamanho); aoMudarTamanho = null; }
    raizAtual = raiz;
    if (!raiz || raiz.hasAttribute('data-rac-pronto')) return; // evita ligar os eventos duas vezes
    raiz.setAttribute('data-rac-pronto', '');

    /* ---------- Paginação ---------- */
    var itens = Array.prototype.slice.call(raiz.querySelectorAll('.rac-recado'));
    var nav = raiz.querySelector('[data-rac-paginas]');
    var pagina = 0;

    function totalPaginas() { return Math.max(1, Math.ceil(itens.length / porPagina())); }

    function desenhar() {
      var por = porPagina(), total = totalPaginas();
      pagina = mod(pagina, total);
      itens.forEach(function (el, i) { el.hidden = !(i >= pagina * por && i < pagina * por + por); });
      if (!nav) return;
      nav.hidden = total <= 1;
      var numeros = nav.querySelector('[data-rac-pag-numeros]');
      if (numeros) {
        numeros.textContent = '';
        for (var p = 0; p < total; p++) {
          var b = document.createElement('button');
          b.type = 'button';
          b.textContent = String(p + 1);
          b.setAttribute('data-rac-pag-ir', String(p));
          b.setAttribute('aria-label', 'Página ' + (p + 1));
          if (p === pagina) { b.classList.add('is-ativa'); b.setAttribute('aria-current', 'page'); }
          numeros.appendChild(b);
        }
      }
      var rotulo = nav.querySelector('[data-rac-pag-rotulo]');
      if (rotulo) rotulo.textContent = (pagina + 1) + ' de ' + total;
    }

    raiz.addEventListener('click', function (e) {
      var ir = e.target.closest('[data-rac-pag-ir]');
      if (ir) { pagina = parseInt(ir.getAttribute('data-rac-pag-ir'), 10); desenhar(); return; }
      if (e.target.closest('[data-rac-pag-prev]')) { pagina -= 1; desenhar(); return; }
      if (e.target.closest('[data-rac-pag-next]')) { pagina += 1; desenhar(); }
    });

    aoMudarTamanho = function () { pagina = 0; desenhar(); };
    largaMq.addEventListener('change', aoMudarTamanho);
    desenhar();

    /* ---------- Formulário ---------- */
    var form = raiz.querySelector('[data-rac-form]');
    var abrir = raiz.querySelector('[data-rac-abrir]');
    var ok = raiz.querySelector('[data-rac-ok]');
    var erro = raiz.querySelector('[data-rac-erro]');
    if (!form || !abrir) return;

    var abertoEm = 0, enviando = false;
    var enviar = form.querySelector('[data-rac-enviar]');

    function estado(nome) {
      raiz.setAttribute('data-estado', nome);
      form.hidden = !(nome === 'aberto' || nome === 'enviando' || nome === 'erro');
      abrir.hidden = !(nome === 'fechado');
      if (ok) ok.hidden = nome !== 'enviado';
      if (erro) erro.hidden = nome !== 'erro';
      enviar.disabled = nome === 'enviando';
      enviar.textContent = nome === 'enviando' ? 'Enviando…' : 'Enviar recado';
    }
    estado('fechado');

    abrir.addEventListener('click', function () {
      abertoEm = Date.now();
      estado('aberto');
      var primeiro = form.querySelector('input[name="author"]');
      if (primeiro) primeiro.focus();
    });
    form.querySelector('[data-rac-cancelar]').addEventListener('click', function () { estado('fechado'); });

    function mensagemDoWordPress(html) {
      try {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var p = doc.querySelector('#error-page p') || doc.querySelector('body p');
        var texto = p ? p.textContent.replace(/\s+/g, ' ').trim() : '';
        return texto ? texto.slice(0, 220) : '';
      } catch (e) { return ''; }
    }

    function falhar(texto) {
      erro.textContent = texto || 'Não foi possível enviar seu recado agora. Confira os campos e tente de novo.';
      estado('erro');
      enviando = false;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (enviando) return;
      enviando = true;
      form.elements['rac_ms'].value = String(Math.max(0, Date.now() - abertoEm));
      estado('enviando');

      fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
        .then(function (r) {
          if (r.redirected) { // o WordPress aceitou o comentário e redirecionou para a página
            form.reset(); estado('enviado'); enviando = false; return null;
          }
          return r.text().then(function (html) { falhar(mensagemDoWordPress(html)); });
        })
        .catch(function () { falhar(''); });
    });
  }

  if (window.RAC && typeof window.RAC.aoCarregar === 'function') {
    window.RAC.aoCarregar(iniciar);
  } else {
    document.addEventListener('DOMContentLoaded', iniciar);
    document.addEventListener('racr:conteudo-trocado', iniciar);
  }
})();
