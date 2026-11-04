/* =========================================================
   Rádio Agenda Cultural — "Tocando agora" da página da estação (3A)

   Na página de uma estação, mostra a música que está tocando NAQUELA
   estação, mesmo que o player esteja em outra. Reaproveita o endpoint do
   plugin racr-player-radio (action "racr_obter_metadado"), que já guarda
   a resposta em cache por 15 s por estação — então não abre conexões
   novas no Zeno.fm. Atualiza a cada 20 s, só com a aba visível.

   O texto vem como "Artista - Música" e é mostrado como "Música — Artista".

   Roda no carregamento e a cada troca de conteúdo da navegação
   persistente (RAC.aoCarregar); o temporizador anterior é sempre cancelado.
   ========================================================= */
(function () {
  'use strict';

  var INTERVALO = 20000;
  var temporizador = null;
  var ouvindoVisibilidade = false;
  var atual = null; // { buscar: function } da página aberta

  function montar(el, faixa, artista) {
    el.textContent = '';
    el.appendChild(document.createTextNode(faixa));
    if (artista) {
      var mudo = document.createElement('span');
      mudo.className = 'rac-mudo';
      mudo.textContent = ' — ' + artista;
      el.appendChild(mudo);
    }
  }

  function iniciar() {
    if (temporizador) { clearInterval(temporizador); temporizador = null; }
    atual = null;

    var hero = document.querySelector('.rac-hero--estacao[data-indice][data-ajax]');
    if (!hero) return;

    var indice = hero.getAttribute('data-indice');
    var url = hero.getAttribute('data-ajax');
    var cartao = hero.querySelector('[data-rac-agora]');
    var linhaM = hero.querySelector('[data-rac-agora-m]');

    function mostrar(titulo) {
      var faixa = 'Transmissão ao vivo', artista = '';
      if (titulo) {
        var i = titulo.indexOf(' - ');
        if (i > 0) { artista = titulo.slice(0, i).trim(); faixa = titulo.slice(i + 3).trim(); }
        else { faixa = titulo; }
      }
      if (cartao) montar(cartao, faixa, artista);
      if (linhaM) montar(linhaM, (titulo ? 'Tocando: ' : '') + faixa, artista);
    }

    function buscar() {
      if (document.hidden) return;
      var dados = new URLSearchParams();
      dados.append('action', 'racr_obter_metadado');
      dados.append('indice', indice);
      fetch(url, { method: 'POST', body: dados })
        .then(function (r) { return r.json(); })
        .then(function (r) {
          if (document.querySelector('.rac-hero--estacao[data-indice="' + indice + '"]') !== hero) return; // página mudou
          mostrar(r && r.success && r.data ? r.data.titulo : null);
        })
        .catch(function () { /* silencioso: mantém o texto atual */ });
    }

    atual = { buscar: buscar };
    buscar();
    temporizador = setInterval(buscar, INTERVALO);
  }

  if (!ouvindoVisibilidade) {
    ouvindoVisibilidade = true;
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden && atual) atual.buscar();
    });
  }

  if (window.RAC && typeof window.RAC.aoCarregar === 'function') {
    window.RAC.aoCarregar(iniciar);
  } else {
    document.addEventListener('DOMContentLoaded', iniciar);
    document.addEventListener('racr:conteudo-trocado', iniciar);
  }
})();
