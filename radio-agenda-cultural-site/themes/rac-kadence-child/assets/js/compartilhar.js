/* =========================================================
   Rádio Agenda Cultural — Compartilhar o post (Fase 4B)

   Os botões do WhatsApp, Facebook e X são links comuns (montados no PHP).
   Aqui ficam: abrir/fechar o menu (janelinha no desktop, lista que sobe da
   base no celular), "Copiar link" (área de transferência) e, em aparelhos
   que têm, "Mais opções do celular" (menu de compartilhar do sistema).

   Delegação de eventos no document: funciona depois de cada troca de
   conteúdo da navegação persistente, sem religar nada.
   ========================================================= */
(function () {
  'use strict';

  var avisoTempo = null;

  function raizDe(el) { return el.closest('[data-rac-compartilhar]'); }

  function abrir(raiz) {
    fecharTodos(raiz);
    var menu = raiz.querySelector('[data-rac-comp-menu]');
    var fundo = raiz.querySelector('[data-rac-comp-fundo]');
    var botao = raiz.querySelector('[data-rac-comp-abrir]');
    menu.hidden = false; fundo.hidden = false;
    botao.setAttribute('aria-expanded', 'true');
    var nativo = raiz.querySelector('[data-rac-comp-nativo]');
    if (nativo) nativo.hidden = !navigator.share;
    var primeiro = menu.querySelector('a, button');
    if (primeiro) primeiro.focus();
  }

  function fechar(raiz) {
    var menu = raiz.querySelector('[data-rac-comp-menu]');
    var fundo = raiz.querySelector('[data-rac-comp-fundo]');
    var botao = raiz.querySelector('[data-rac-comp-abrir]');
    if (menu) menu.hidden = true;
    if (fundo) fundo.hidden = true;
    if (botao) botao.setAttribute('aria-expanded', 'false');
    var aviso = raiz.querySelector('[data-rac-comp-aviso]');
    if (aviso) aviso.hidden = true;
    var txt = raiz.querySelector('[data-rac-comp-copiar-txt]');
    if (txt) txt.textContent = 'Copiar link';
  }

  function fecharTodos(exceto) {
    Array.prototype.forEach.call(document.querySelectorAll('[data-rac-compartilhar]'), function (r) {
      if (r !== exceto) fechar(r);
    });
  }

  function copiar(texto) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(texto);
    }
    return new Promise(function (ok, falha) { // alternativa para navegadores antigos / sem HTTPS
      var t = document.createElement('textarea');
      t.value = texto; t.setAttribute('readonly', ''); t.style.position = 'fixed'; t.style.opacity = '0';
      document.body.appendChild(t); t.select();
      var deu = false;
      try { deu = document.execCommand('copy'); } catch (e) {}
      document.body.removeChild(t);
      deu ? ok() : falha(new Error('copy'));
    });
  }

  document.addEventListener('click', function (e) {
    var raiz = e.target.closest('[data-rac-compartilhar]');

    if (!raiz) { fecharTodos(null); return; }

    if (e.target.closest('[data-rac-comp-abrir]')) {
      var menu = raiz.querySelector('[data-rac-comp-menu]');
      if (menu.hidden) abrir(raiz); else fechar(raiz);
      return;
    }
    if (e.target.closest('[data-rac-comp-fechar]') || e.target.closest('[data-rac-comp-fundo]')) {
      fechar(raiz);
      var b = raiz.querySelector('[data-rac-comp-abrir]'); if (b) b.focus();
      return;
    }
    if (e.target.closest('[data-rac-comp-copiar]')) {
      var aviso = raiz.querySelector('[data-rac-comp-aviso]');
      var txt = raiz.querySelector('[data-rac-comp-copiar-txt]');
      copiar(raiz.getAttribute('data-url')).then(function () {
        aviso.textContent = 'Link copiado! Agora é só colar onde quiser.';
        aviso.hidden = false; txt.textContent = 'Link copiado ✓';
        clearTimeout(avisoTempo);
        avisoTempo = setTimeout(function () { aviso.hidden = true; txt.textContent = 'Copiar link'; }, 4000);
      }).catch(function () {
        aviso.textContent = 'Não foi possível copiar. Selecione o endereço na barra do navegador.';
        aviso.hidden = false;
      });
      return;
    }
    if (e.target.closest('[data-rac-comp-nativo]') && navigator.share) {
      navigator.share({ title: raiz.getAttribute('data-titulo'), url: raiz.getAttribute('data-url') }).catch(function () {});
      return;
    }
    // clique num link de rede social: fecha o menu depois de abrir a aba
    if (e.target.closest('a.rac-comp')) { setTimeout(function () { fechar(raiz); }, 50); }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var aberto = null;
    Array.prototype.forEach.call(document.querySelectorAll('[data-rac-compartilhar]'), function (r) {
      var m = r.querySelector('[data-rac-comp-menu]');
      if (m && !m.hidden) { aberto = r; fechar(r); }
    });
    if (aberto) { var b = aberto.querySelector('[data-rac-comp-abrir]'); if (b) b.focus(); }
  });

  // depois de trocar de página, nenhum menu fica aberto (os elementos novos já nascem fechados)
  document.addEventListener('racr:conteudo-trocado', function () { fecharTodos(null); });
})();
