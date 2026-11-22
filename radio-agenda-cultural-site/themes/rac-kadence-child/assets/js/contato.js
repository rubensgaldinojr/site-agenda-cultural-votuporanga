/* =========================================================
   Rádio Agenda Cultural — formulário de Contato (Fase 5B)

   • Assunto em botões: "Sugestão de música" e "Pedido musical" mostram os campos Música e Artista
     (Música passa a ser obrigatória) e trocam o texto de ajuda da mensagem.
   • O envio é feito com fetch para o admin-ajax do WordPress: a página NÃO recarrega (a rádio
     continua tocando). Sucesso → tela "Mensagem enviada"; erro → mensagem em cima do botão, e o que
     foi digitado é mantido.
   • "rac_ms" leva o tempo desde que o formulário abriu (o servidor exige ≥ 3 s) e "rac_site" é a armadilha.

   Roda no carregamento e a cada troca de conteúdo (RAC.aoCarregar).
   ========================================================= */
(function () {
  'use strict';

  function iniciar() {
    var raiz = document.querySelector('[data-rac-contato]');
    if (!raiz || raiz.hasAttribute('data-rac-pronto')) return;
    raiz.setAttribute('data-rac-pronto', '');

    var form = raiz.querySelector('[data-rac-contato-form]');
    var caixaMusica = raiz.querySelector('[data-rac-contato-musica]');
    var mensagem = raiz.querySelector('[data-rac-contato-mensagem]');
    var erro = raiz.querySelector('[data-rac-contato-erro]');
    var enviar = raiz.querySelector('[data-rac-contato-enviar]');
    var enviado = raiz.querySelector('[data-rac-contato-enviado]');
    var outra = raiz.querySelector('[data-rac-contato-outra]');
    if (!form) return;

    var inicio = Date.now();
    var enviando = false;

    function assuntoMusical() {
      var marcado = form.querySelector('input[name="assunto"]:checked');
      return !!marcado && marcado.getAttribute('data-musical') === '1';
    }
    function atualizarAssunto() {
      var musical = assuntoMusical();
      caixaMusica.hidden = !musical;
      form.elements['musica'].required = musical;
      mensagem.placeholder = musical ? mensagem.getAttribute('data-ph-musical') : mensagem.getAttribute('data-ph-outro');
    }
    form.addEventListener('change', function (e) {
      if (e.target.name === 'assunto') atualizarAssunto();
    });
    atualizarAssunto();

    function mostrarErro(texto) {
      erro.textContent = texto || 'Não foi possível enviar agora. Confira os campos e tente de novo.';
      erro.hidden = false;
    }
    function estado(ocupado) {
      enviando = ocupado;
      enviar.disabled = ocupado;
      enviar.textContent = ocupado ? 'Enviando…' : 'Enviar mensagem';
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (enviando) return;
      erro.hidden = true;
      form.elements['rac_ms'].value = String(Math.max(0, Date.now() - inicio));
      estado(true);

      // atenção: o campo oculto name="action" esconde a propriedade form.action; por isso lê-se o atributo
      fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
        .then(function (r) { return r.json().catch(function () { return null; }); })
        .then(function (json) {
          if (json && json.success) {
            form.reset();
            atualizarAssunto();
            form.hidden = true;
            enviado.hidden = false;
            enviado.scrollIntoView({ behavior: 'smooth', block: 'center' });
            var h = enviado.querySelector('h2'); if (h) { h.setAttribute('tabindex', '-1'); h.focus({ preventScroll: true }); }
          } else {
            mostrarErro(json && json.data && json.data.mensagem);
          }
          estado(false);
        })
        .catch(function () { mostrarErro(''); estado(false); });
    });

    if (outra) {
      outra.addEventListener('click', function () {
        enviado.hidden = true;
        form.hidden = false;
        inicio = Date.now();
        var primeiro = form.elements['nome']; if (primeiro) primeiro.focus();
      });
    }
  }

  if (window.RAC && typeof window.RAC.aoCarregar === 'function') {
    window.RAC.aoCarregar(iniciar);
  } else {
    document.addEventListener('DOMContentLoaded', iniciar);
    document.addEventListener('racr:conteudo-trocado', iniciar);
  }
})();
