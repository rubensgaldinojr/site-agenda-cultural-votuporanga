// Teste de navegador da Fase 2 (player fixo, cards de estação e de post).
// Veja README.md desta pasta.
const { chromium } = require('playwright');
const B = 'http://localhost:8099';
const out = (...a) => console.log(...a);
let falhas = 0;
const ok = (c, msg) => { out((c ? 'OK   ' : 'FALHA') + ' ' + msg); if (!c) falhas++; };
const esperar = (page, fn, arg, ms = 6000) => page.waitForFunction(fn, arg, { timeout: ms }).then(() => true).catch(() => false);

(async () => {
  const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });
  const errosJs = [];

  /* ---------- Contrato com o plugin ---------- */
  const ctx0 = await browser.newContext();
  const html = await (await ctx0.request.get(B + '/componentes/')).text();
  for (const id of ['racr-player', 'racr-audio', 'racr-botao-play', 'racr-status', 'racr-nome-estacao', 'racr-lista-estacoes', 'racr-botao-info', 'racr-botao-expandir']) {
    ok((html.match(new RegExp('id="' + id + '"', 'g')) || []).length === 1, `contrato do plugin: #${id} existe uma vez`);
  }
  ok(!html.includes('racr-player-radio.css') && !html.includes('racr-player.css'), 'CSS antigo do plugin não é carregado');
  ok((html.match(/class="racr-estacao( |")/g) || []).length === 12, 'lista do player tem as 12 estações');

  /* ---------- Desktop ---------- */
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 }, deviceScaleFactor: 1 });
  const page = await ctx.newPage();
  page.on('pageerror', e => errosJs.push(e.message));
  page.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource/.test(m.text())) errosJs.push(m.text()); });
  await page.goto(B + '/componentes/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });

  const caixa = await page.evaluate(() => { const r = document.getElementById('racr-player').getBoundingClientRect(); const b = document.querySelector('.rac-player__barra').getBoundingClientRect(); return { fundo: Math.round(window.innerHeight - r.bottom), altura: Math.round(b.height), pos: getComputedStyle(document.getElementById('racr-player')).position }; });
  ok(caixa.pos === 'fixed' && caixa.fundo === 0, `player fixo colado na base (position=${caixa.pos}, folga=${caixa.fundo}px)`);
  ok(caixa.altura === 72, `barra com 72px no desktop (obtido ${caixa.altura}px)`);

  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  const rodape = await page.evaluate(() => ({ rodapeFundo: Math.round(document.querySelector('.rac-footer').getBoundingClientRect().bottom), topoPlayer: Math.round(document.querySelector('.rac-player__barra').getBoundingClientRect().top) }));
  ok(rodape.rodapeFundo <= rodape.topoPlayer + 1, `rodapé não fica coberto pelo player (rodapé termina em ${rodape.rodapeFundo}, player começa em ${rodape.topoPlayer})`);
  await page.evaluate(() => window.scrollTo(0, 0));

  // estado inicial dos cards
  ok(await page.evaluate(() => document.querySelectorAll('.rac-card-est.is-tocando').length === 0), 'nenhum card marcado como tocando antes do play');

  // play pelo card da 3ª estação
  const nome3 = await page.evaluate(() => document.querySelectorAll('#grade-estacoes .rac-card-est')[2].querySelector('.rac-card-est__nome').textContent.trim());
  await page.click('#grade-estacoes .rac-card-est:nth-child(3) [data-rac-tocar]');
  ok(await esperar(page, () => !window.__audio.paused && window.__audio.currentTime > 0.2), `play pelo card toca o áudio (${nome3})`);
  const e1 = await page.evaluate(() => ({
    nome: document.getElementById('racr-nome-estacao').textContent.trim(),
    cards: [...document.querySelectorAll('.rac-card-est.is-tocando')].map(c => c.querySelector('.rac-card-est__nome').textContent.trim()),
    pressed: document.querySelector('#grade-estacoes .rac-card-est:nth-child(3) [data-rac-tocar]').getAttribute('aria-pressed'),
    classe: document.getElementById('racr-player').className,
    capa: document.querySelector('.rac-player__capa').style.backgroundImage,
  }));
  ok(e1.nome === nome3, `player mostra a estação do card ("${e1.nome}")`);
  ok(e1.cards.length === 1 && e1.cards[0] === nome3, `só o card da estação atual fica .is-tocando (${JSON.stringify(e1.cards)})`);
  ok(e1.pressed === 'true', 'aria-pressed=true no botão do card tocando');
  ok(/capa-2\.svg/.test(e1.capa), `capa da estação aparece no player (${e1.capa})`);

  // "Tocando agora": faixa + artista (plugin real → ajax simulado)
  const agora = await esperar(page, () => document.querySelector('.rac-player__agora [data-rac="artista"]').textContent.trim() !== '');
  const t = await page.evaluate(() => ({ rotulo: document.querySelector('.rac-player__agora [data-rac="rotulo"]').textContent, faixa: document.querySelector('.rac-player__agora [data-rac="faixa"]').textContent, artista: document.querySelector('.rac-player__agora [data-rac="artista"]').textContent, m2: document.querySelector('[data-rac="linha2-m"]').textContent, bruto: document.getElementById('racr-status').textContent }));
  ok(agora && t.rotulo === 'Tocando agora' && t.faixa === 'Save My Child' && t.artista === 'Willie Dixon', `"Artista - Música" dividido: rótulo="${t.rotulo}", faixa="${t.faixa}", artista="${t.artista}" (bruto "${t.bruto}")`);
  ok(t.m2.includes(' · ') && t.m2.includes(nome3), `linha 2 do mobile = "artista · estação" ("${t.m2}")`);

  // pausar pelo mesmo card
  await page.click('#grade-estacoes .rac-card-est:nth-child(3) [data-rac-tocar]');
  ok(await esperar(page, () => window.__audio.paused), 'clicar de novo no card da estação tocando pausa');
  ok(await esperar(page, () => document.querySelectorAll('.rac-card-est.is-tocando').length === 0), 'card deixa de ficar destacado ao pausar');
  const stat = await page.evaluate(() => ({ rot: document.querySelector('.rac-player__agora [data-rac="rotulo"]').textContent, faixa: document.querySelector('.rac-player__agora [data-rac="faixa"]').textContent }));
  ok(stat.rot === 'Status' && stat.faixa === 'Parado', `pausado: rótulo "${stat.rot}" e texto "${stat.faixa}"`);

  // card compacto toca outra estação
  const nomeC = await page.evaluate(() => document.querySelector('#grade-compact .rac-card-est').querySelector('.rac-card-est__nome').textContent.trim());
  await page.click('#grade-compact .rac-card-est:nth-child(1)');
  ok(await esperar(page, () => !window.__audio.paused && window.__audio.currentTime > 0.1), 'card compacto toca ao clicar em qualquer ponto');
  const c = await page.evaluate(() => ({ nome: document.getElementById('racr-nome-estacao').textContent.trim(), rot: document.querySelector('#grade-compact .rac-card-est:nth-child(1) .rac-card-est__rotulo').textContent, normais: document.querySelectorAll('#grade-estacoes .is-tocando').length }));
  ok(c.nome === nomeC && c.rot === 'Tocando' && c.normais === 0, `compacto: player em "${c.nome}", rótulo "${c.rot}", cards normais desmarcados`);
  // teclado no compacto
  await page.focus('#grade-compact .rac-card-est:nth-child(2)'); await page.keyboard.press('Enter');
  const nomeC2 = await page.evaluate(() => document.querySelector('#grade-compact .rac-card-est:nth-child(2) .rac-card-est__nome').textContent.trim());
  ok(await esperar(page, n => document.getElementById('racr-nome-estacao').textContent.trim() === n, nomeC2), 'card compacto também funciona com teclado (Enter)');

  // play/pause do próprio player sincroniza os cards
  await page.click('#racr-botao-play');
  ok(await esperar(page, () => window.__audio.paused), 'botão play/pause do player pausa');
  ok(await esperar(page, () => document.querySelectorAll('.rac-card-est.is-tocando').length === 0), 'cards acompanham o pause feito no player');
  await page.click('#racr-botao-play');
  ok(await esperar(page, () => document.querySelectorAll('.rac-card-est.is-tocando').length === 1), 'cards acompanham o play feito no player');

  // título do card NÃO é link; "Ver estação" é o link para a página
  const linkInfo = await page.evaluate(() => { const c = document.querySelector('#grade-estacoes .rac-card-est'); return { tituloEhLink: !!c.querySelector('a.rac-card-est__nome') || c.querySelector('.rac-card-est__nome').closest('a') !== null, ver: c.querySelector('a.rac-card-est__ver')?.getAttribute('href'), verTexto: c.querySelector('a.rac-card-est__ver')?.textContent.trim(), compactTemLink: !!document.querySelector('#grade-compact .rac-card-est a') }; });
  ok(linkInfo.tituloEhLink === false, 'título do card completo não é mais link');
  ok(/^\/estacao\/\d+\/$/.test(linkInfo.ver) && linkInfo.verTexto === 'Ver estação', `card completo tem o link "Ver estação" (${linkInfo.ver})`);
  ok(linkInfo.compactTemLink === false, 'card compacto continua sem nenhum link');

  // clicar no TÍTULO do card toca/pausa e NÃO navega
  const urlAntes = await page.evaluate(() => location.pathname);
  await page.click('#grade-estacoes .rac-card-est:nth-child(4) .rac-card-est__nome');
  ok(await esperar(page, () => document.getElementById('racr-nome-estacao').textContent.trim() === 'Vozes da Cidade' && !window.__audio.paused), 'clicar no título do card toca aquela estação');
  ok(await page.evaluate(u => location.pathname === u && window.__marcador === 'mesma-pagina', urlAntes), 'clicar no título não navega nem recarrega a página');
  await page.click('#grade-estacoes .rac-card-est:nth-child(4)', { position: { x: 12, y: 8 } });
  ok(await esperar(page, () => window.__audio.paused), 'clicar de novo em qualquer ponto do card pausa');

  // "Ver estação" navega (via AJAX, sem recarregar) e não dispara o play
  await page.click('#grade-estacoes .rac-card-est:nth-child(1) a.rac-card-est__ver');
  ok(await esperar(page, () => location.pathname === '/estacao/100/'), '"Ver estação" leva para a página da estação');
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && window.__audio.paused), '"Ver estação" navega sem recarregar e sem tocar');
  await page.goBack();
  await esperar(page, () => location.pathname === '/componentes/');
  await page.waitForTimeout(500);

  // pílula (play + volume) com ~1/3 da barra e volume sem o estilo de campo do tema pai
  const pil = async (w) => { await page.setViewportSize({ width: w, height: 800 }); return page.evaluate(() => { const c = document.querySelector('.rac-player__controles').getBoundingClientRect(); const r = document.getElementById('rac-volume').getBoundingClientRect(); const e = document.querySelector('.rac-player__esq').getBoundingClientRect(); const d = document.querySelector('.rac-player__trocar').getBoundingClientRect(); const s = getComputedStyle(document.getElementById('rac-volume')); return { pilula: Math.round(c.width), vw: window.innerWidth, slider: Math.round(r.width), centro: Math.round(c.left + c.width / 2), meio: Math.round(window.innerWidth / 2), bg: s.backgroundColor, borda: s.borderTopWidth, pad: s.paddingLeft, app: s.appearance || s.webkitAppearance, esqFim: Math.round(e.right), pilIni: Math.round(c.left), pilFim: Math.round(c.right), dirIni: Math.round(d.left) }; }); };
  const p1280 = await pil(1280);
  ok(p1280.pilula / p1280.vw >= 0.29 && p1280.pilula / p1280.vw <= 0.36, `1280px: pílula ocupa ~1/3 da barra (${p1280.pilula}px de ${p1280.vw}px = ${(100 * p1280.pilula / p1280.vw).toFixed(0)}%)`);
  ok(Math.abs(p1280.centro - p1280.meio) <= 2, `1280px: pílula centralizada (centro ${p1280.centro}px, meio ${p1280.meio}px)`);
  ok(p1280.slider >= 180, `1280px: barra de volume longa (${p1280.slider}px)`);
  ok(p1280.bg === 'rgba(0, 0, 0, 0)' && p1280.borda === '0px' && p1280.pad === '0px' && p1280.app === 'none', `volume sem fundo/borda/padding do tema pai (bg=${p1280.bg}, borda=${p1280.borda}, padding=${p1280.pad}, appearance=${p1280.app})`);
  ok(p1280.esqFim <= p1280.pilIni && p1280.pilFim <= p1280.dirIni, '1280px: esquerda, pílula e botão não se sobrepõem');
  await page.screenshot({ path: 'capturas/f3-barra-1280.png', clip: { x: 0, y: 728, width: 1280, height: 72 } });
  const p1920 = await pil(1920);
  ok(p1920.pilula / p1920.vw >= 0.29 && p1920.pilula <= 640, `1920px: pílula proporcional, limitada a 640px (${p1920.pilula}px)`);
  await page.screenshot({ path: 'capturas/f3-barra-1920.png', clip: { x: 0, y: 728, width: 1920, height: 72 } });
  const p1000 = await pil(1000);
  ok(p1000.esqFim <= p1000.pilIni && p1000.pilFim <= p1000.dirIni && p1000.slider >= 90, `1000px: sem sobreposição e volume utilizável (${p1000.slider}px)`);
  const p900 = await pil(900);
  ok(p900.esqFim <= p900.pilIni && p900.pilFim <= p900.dirIni, '900px: sem sobreposição');
  await page.screenshot({ path: 'capturas/f3-barra-900.png', clip: { x: 0, y: 728, width: 900, height: 72 } });
  await page.setViewportSize({ width: 1280, height: 800 });
  // a parte preenchida da barra acompanha o valor
  await page.fill('#rac-volume', '40'); await page.dispatchEvent('#rac-volume', 'input');
  ok(await page.evaluate(() => document.getElementById('rac-volume').style.getPropertyValue('--vol') === '40%'), 'preenchimento da barra de volume acompanha o valor (--vol=40%)');
  await page.fill('#rac-volume', '100'); await page.dispatchEvent('#rac-volume', 'input');

  // dropdown
  ok(await page.isHidden('.rac-player__painel'), 'dropdown fechado no início');
  await page.click('#racr-botao-expandir');
  ok(await page.isVisible('.rac-player__painel') && (await page.getAttribute('#racr-botao-expandir', 'aria-expanded')) === 'true', 'botão "Trocar estação" abre o dropdown');
  const caixaPainel = await page.evaluate(() => { const p = document.querySelector('.rac-player__painel').getBoundingClientRect(); const b = document.querySelector('.rac-player__barra').getBoundingClientRect(); return { larg: Math.round(p.width), gap: Math.round(b.top - p.bottom), direita: Math.round(window.innerWidth - p.right) }; });
  ok(caixaPainel.larg === 320 && caixaPainel.direita === 28 && Math.abs(caixaPainel.gap - 12) <= 1, `dropdown: 320px, a 28px da direita, 12px acima da barra (${JSON.stringify(caixaPainel)})`);
  const ativoLista = await page.evaluate(() => document.querySelector('.racr-estacao-ativa .rac-est__nome').textContent.trim());
  ok(ativoLista === await page.evaluate(() => document.getElementById('racr-nome-estacao').textContent.trim()), `estação atual marcada na lista (${ativoLista})`);
  await page.screenshot({ path: 'capturas/f2-d-dropdown.png' });
  await page.click('#rac-player-fechar');
  ok(await page.isHidden('.rac-player__painel'), '✕ fecha o dropdown');
  await page.click('#racr-botao-expandir'); await page.mouse.click(300, 300);
  ok(await page.isHidden('.rac-player__painel'), 'clicar fora fecha o dropdown');
  await page.click('#racr-botao-expandir'); await page.keyboard.press('Escape');
  ok(await page.isHidden('.rac-player__painel'), 'Esc fecha o dropdown');
  await page.click('#racr-botao-expandir');
  await page.click('.racr-estacao[data-indice="5"]');
  ok(await esperar(page, () => document.getElementById('racr-nome-estacao').textContent.trim() === 'Poesia Falada') && await page.isHidden('.rac-player__painel'), 'escolher estação na lista troca a estação e fecha o dropdown');
  ok(await esperar(page, () => { const c = [...document.querySelectorAll('.rac-card-est.is-tocando .rac-card-est__nome')].map(n => n.textContent.trim()); return c.length === 1 && c[0] === 'Poesia Falada'; }), 'trocar pela lista do player destaca só o card dessa estação (Poesia Falada, que está nos cards compactos)');

  // volume
  await page.fill('#rac-volume', '30');
  await page.dispatchEvent('#rac-volume', 'input');
  ok(await page.evaluate(() => Math.abs(window.__audio.volume - 0.3) < 0.01 && localStorage.getItem('rac-volume') === '30'), 'slider ajusta audio.volume e salva em localStorage');
  await page.reload();
  ok(await page.evaluate(() => document.getElementById('rac-volume').value === '30' && Math.abs(document.getElementById('racr-audio').volume - 0.3) < 0.01), 'volume lembrado após recarregar');

  // navegação persistente com o player novo
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  await page.click('#grade-estacoes .rac-card-est:nth-child(2) [data-rac-tocar]');
  ok(await esperar(page, () => !window.__audio.paused && window.__audio.currentTime > 0.2), 'tocando antes de navegar');
  for (const [txt, rota] of [['Blog', '/blog/'], ['Sobre', '/sobre/']]) {
    const antes = await page.evaluate(() => window.__audio.currentTime);
    await page.click(`#primary a:has-text("${txt}")`);
    await page.waitForFunction(r => location.pathname === r, rota);
    await page.waitForTimeout(400);
    const s = await page.evaluate(() => ({ m: window.__marcador, p: window.__audio.paused, t: window.__audio.currentTime, tocandoClasse: document.getElementById('racr-player').classList.contains('racr-tocando'), nome: document.getElementById('racr-nome-estacao').textContent.trim() }));
    ok(s.m === 'mesma-pagina' && !s.p && s.t >= antes && s.tocandoClasse, `${rota}: player novo mantém o áudio sem recarregar (t ${antes.toFixed(2)} → ${s.t.toFixed(2)})`);
  }
  await page.goto(B + '/componentes/');   // volta (recarga completa) só para reusar os cards
  await page.evaluate(() => { window.__audio = document.getElementById('racr-audio'); });

  /* ---------- Cards de post ---------- */
  const posts = await page.evaluate(() => [...document.querySelectorAll('.rac-card-post')].map(c => ({
    cat: c.querySelector('.rac-card-post__categoria')?.textContent, data: c.querySelector('.rac-card-post__data')?.textContent,
    titulo: c.querySelector('.rac-card-post__titulo').textContent.trim(), autor: c.querySelector('.rac-card-post__autor a').textContent,
    autorHref: c.querySelector('.rac-card-post__autor a').getAttribute('href'), tituloHref: c.querySelector('.rac-card-post__titulo').getAttribute('href'),
    img: !!c.querySelector('.rac-card-post__imagem img'), altImg: Math.round(c.querySelector('.rac-card-post__imagem').getBoundingClientRect().height),
    linhas: Math.round(c.querySelector('.rac-card-post__titulo').getBoundingClientRect().height / parseFloat(getComputedStyle(c.querySelector('.rac-card-post__titulo')).lineHeight)),
  })));
  ok(posts.length === 3, 'três cards de post renderizados');
  ok(posts[0].cat === 'Crônicas' && posts[0].data === '28 out 2026' && posts[0].autor === 'Marina Alves' && posts[0].autorHref === '/autor/1/' && posts[0].tituloHref === '/blog/post-501/', `card 1: categoria, data, autor com link e título com link (${JSON.stringify(posts[0])})`);
  ok(posts[0].img && posts[2].img === false, 'imagem destacada aparece; sem imagem cai no placeholder listrado');
  ok(posts.every(p => p.altImg === 168), 'área da imagem com 168px');
  ok(posts[1].linhas <= 2, `título longo limitado a 2 linhas (${posts[1].linhas})`);
  ok(await page.evaluate(() => getComputedStyle(document.querySelector('.rac-card-post__imagem')).backgroundImage.includes('gradient')), 'placeholder listrado (stripe-cover) presente');

  // sem rolagem horizontal em larguras intermediárias (tablet)
  for (const w of [900, 1024]) {
    await page.setViewportSize({ width: w, height: 800 });
    ok(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), `${w}px: sem rolagem horizontal com o player desktop`);
  }
  await page.setViewportSize({ width: 1280, height: 800 });

  // abrir a lista leva a estação atual para a área visível, sem rolar a página
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.click('#racr-botao-expandir');
  await page.click('.racr-estacao[data-indice="11"]');   // escolhe a última estação (fecha a lista)
  await page.waitForTimeout(300);
  await page.click('#racr-botao-expandir');               // reabre: a atual (a última) precisa estar visível
  await page.waitForTimeout(200);
  const vis = await page.evaluate(() => { const l = document.getElementById('racr-lista-estacoes').getBoundingClientRect(); const a = document.querySelector('.racr-estacao-ativa').getBoundingClientRect(); return { dentro: a.top >= l.top - 1 && a.bottom <= l.bottom + 1, scrollY: window.scrollY }; });
  ok(vis.dentro && vis.scrollY === 0, `lista abre com a estação atual visível (dentro=${vis.dentro}, scrollY da página=${vis.scrollY})`);
  await page.keyboard.press('Escape');

  /* ---------- Mobile ---------- */
  const ctx3 = await browser.newContext({ viewport: { width: 375, height: 800 }, deviceScaleFactor: 2 });
  const m = await ctx3.newPage();
  m.on('pageerror', e => errosJs.push(e.message));
  await m.goto(B + '/componentes/');
  const mb = await m.evaluate(() => ({ alt: Math.round(document.querySelector('.rac-player__barra').getBoundingClientRect().height), vol: getComputedStyle(document.querySelector('.rac-player__volume')).display, desk: getComputedStyle(document.querySelector('.rac-player__agora')).display, minfo: getComputedStyle(document.querySelector('.rac-player__m-info')).display, scrollX: document.documentElement.scrollWidth > window.innerWidth }));
  ok(mb.alt === 56, `mobile: barra de 56px (obtido ${mb.alt})`);
  ok(mb.vol === 'none' && mb.desk === 'none' && mb.minfo === 'flex', 'mobile: volume e "Tocando agora" do desktop escondidos; info compacta visível');
  ok(!mb.scrollX, 'mobile: sem rolagem horizontal');
  await m.click('#racr-botao-expandir');
  const sheet = await m.evaluate(() => { const p = document.querySelector('.rac-player__painel').getBoundingClientRect(); const b = document.querySelector('.rac-player__barra').getBoundingClientRect(); return { larg: Math.round(p.width), colado: Math.round(b.top - p.bottom), alt: Math.round(p.height), titulo: document.querySelector('.rac-player__painel-titulo').innerText.trim() }; });
  ok(sheet.larg === 375 && sheet.colado === 0 && sheet.alt <= 420, `mobile: bottom sheet em largura total, colado à barra, até 420px (${JSON.stringify(sheet)})`);
  ok(sheet.titulo === 'Escolher estação', 'mobile: título do sheet "Escolher estação"');
  await m.screenshot({ path: 'capturas/f2-m-sheet.png' });
  await m.click('.racr-estacao[data-indice="2"]');
  ok(await esperar(m, () => !document.getElementById('racr-audio').paused, null, 6000), 'mobile: escolher estação no sheet toca');
  ok(await m.isHidden('.rac-player__painel'), 'mobile: sheet fecha ao escolher');
  await m.screenshot({ path: 'capturas/f2-m-tocando.png', fullPage: false });

  /* ---------- Capturas desktop/escuro ---------- */
  for (const [nome, tema, w] of [['f2-d-claro', 'light', 1280], ['f2-d-escuro', 'dark', 1280], ['f2-m-escuro', 'dark', 375]]) {
    const cx = await browser.newContext({ viewport: { width: w, height: w === 1280 ? 900 : 800 }, deviceScaleFactor: 2 });
    const pg = await cx.newPage();
    await pg.addInitScript(t => localStorage.setItem('rac-tema', t), tema);
    await pg.goto(B + '/componentes/');
    await pg.click('#grade-estacoes .rac-card-est:nth-child(2) [data-rac-tocar]').catch(() => pg.click('#grade-compact .rac-card-est:nth-child(2)'));
    await pg.waitForTimeout(1200);
    await pg.screenshot({ path: `capturas/${nome}.png`, fullPage: w !== 1280 });
    await cx.close();
  }

  ok(errosJs.length === 0, 'sem erros de JavaScript (' + errosJs.join(' | ') + ')');
  out('\nFALHAS:', falhas);
  await browser.close();
  process.exit(falhas ? 1 : 0);
})();
