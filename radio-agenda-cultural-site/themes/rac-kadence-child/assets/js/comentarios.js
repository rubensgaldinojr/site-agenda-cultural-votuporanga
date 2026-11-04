/* =========================================================
   Rádio Agenda Cultural — Comentários do post (Fase 4B)

   • "Responder" move o formulário para perto do comentário e define o
     comentário-pai; "Cancelar resposta" devolve o formulário ao fim.
   • O envio é feito com fetch para wp-comments-post.php: a página NÃO recarrega
     (o áudio da rádio não para). Sucesso = o WordPress respondeu com
     redirecionamento. O tempo desde que a página abriu vai em "rac_ms" (o
     servidor exige ≥ 3 s) e "rac_site" é a armadilha para robôs.
   • Sem JavaScript o formulário envia do jeito normal do WordPress.

   Roda no carregamento e a cada troca de conteúdo (RAC.aoCarregar).
   ========================================================= */
(function () {
  'use strict';

  function iniciar() {
    var raiz = document.querySelector('[data-rac-comentarios]');
    if (!raiz || raiz.hasAttribute('data-rac-pronto')) return;
    raiz.setAttribute('data-rac-pronto', '');

    var caixa = raiz.querySelector('[data-rac-comentarios-form-caixa]');
    var form = raiz.querySelector('[data-rac-comentarios-form]');
    if (!caixa || !form) return;

    var inicio = Date.now();
    var campoPai = form.querySelector('[data-rac-pai]');
    var faixa = form.querySelector('[data-rac-respondendo]');
    var faixaNome = form.querySelector('[data-rac-respondendo-nome]');
    var titulo = caixa.querySelector('[data-rac-form-titulo]');
    var ok = caixa.querySelector('[data-rac-ok]');
    var erro = form.querySelector('[data-rac-erro]');
    var enviar = form.querySelector('[data-rac-enviar]');
    var enviando = false;
    var marcador = document.createElement('span'); // lembra onde o formulário mora
    marcador.hidden = true;
    caixa.parentNode.insertBefore(marcador, caixa);

    function responder(id, nome, alvo) {
      campoPai.value = id;
      faixa.hidden = false;
      faixaNome.textContent = nome;
      titulo.textContent = 'Responder';
      alvo.appendChild(caixa);
      caixa.scrollIntoView({ behavior: 'smooth', block: 'center' });
      var t = form.querySelector('textarea'); if (t) t.focus({ preventScroll: true });
    }
    function cancelarResposta() {
      campoPai.value = '0';
      faixa.hidden = true;
      titulo.textContent = 'Deixe seu comentário';
      marcador.parentNode.insertBefore(caixa, marcador.nextSibling);
    }

    raiz.addEventListener('click', function (e) {
      var r = e.target.closest('[data-rac-responder]');
      if (r) {
        var linha = r.closest('.rac-coment__corpo');
        responder(r.getAttribute('data-rac-responder'), r.getAttribute('data-rac-nome'), linha);
        return;
      }
      if (e.target.closest('[data-rac-cancelar-resposta]')) cancelarResposta();
    });

    function mensagemDoWordPress(html) {
      try {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var p = doc.querySelector('#error-page p') || doc.querySelector('body p');
        var texto = p ? p.textContent.replace(/\s+/g, ' ').trim() : '';
        return texto ? texto.slice(0, 220) : '';
      } catch (x) { return ''; }
    }

    function estado(enviandoAgora) {
      enviando = enviandoAgora;
      enviar.disabled = enviandoAgora;
      enviar.textContent = enviandoAgora ? 'Enviando…' : 'Publicar comentário';
    }

    function falhar(texto) {
      erro.textContent = texto || 'Não foi possível enviar seu comentário agora. Confira os campos e tente de novo.';
      erro.hidden = false;
      estado(false);
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (enviando) return;
      erro.hidden = true;
      form.elements['rac_ms'].value = String(Math.max(0, Date.now() - inicio));
      estado(true);

      fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
        .then(function (r) {
          if (r.redirected) { // aceito: o comentário passa por moderação
            var texto = form.elements['comment'];
            if (texto) texto.value = '';
            estado(false);
            cancelarResposta();
            form.hidden = true;
            ok.hidden = false;
            ok.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return null;
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
