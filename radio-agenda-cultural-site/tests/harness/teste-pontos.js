// Teste de navegador das bolinhas dos carrosséis no mobile (assets/js/pontos.js) e do slider do topo da Home.
const { chromium } = require('playwright');
const B = 'http://localhost:8099';
const out = (...a) => console.log(...a);
let falhas = 0;
const ok = (c, msg) => { out((c ? 'OK   ' : 'FALHA') + ' ' + msg); if (!c) falhas++; };
const esperar = (page, fn, arg, ms = 6000) => page.waitForFunction(fn, arg, { timeout: ms }).then(() => true).catch(() => false);

// lê as bolinhas que vêm logo depois de uma faixa
const lerPontos = (page, sel) => page.evaluate((s) => {
  const f = document.querySelector(s); const p = f && f.nextElementSibling;
  if (!p || !p.classList.contains('rac-pontos')) return null;
  const bs = [...p.querySelectorAll('.rac-pontos__ponto')];
  const vis = !p.hidden && getComputedStyle(p).display !== 'none';
  const a = bs.findIndex(b => b.getAttribute('aria-current') === 'true');
  const r = bs[0] ? bs[0].getBoundingClientRect() : { width: 0, height: 0 };
  const ativoW = a >= 0 ? Math.round(getComputedStyle(bs[a], '::before').width.replace('px', '')) : 0;
  const inatW = bs[1 - (a > 0 ? 1 : 0)] ? Math.round(getComputedStyle(bs[a === 0 ? 1 : 0], '::before').width.replace('px', '')) : 0;
  return { n: bs.length, ativo: a, vis, toque: [Math.round(r.width), Math.round(r.height)], ativoW, inatW, atual: bs.filter(b => b.getAttribute('aria-current') === 'true').length };
}, sel);

(async () => {
  const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });
  const errosJs = [];
  const ctx = await browser.newContext({ viewport: { width: 375, height: 800 } });
  const page = await ctx.newPage();
  page.on('pageerror', e => errosJs.push(e.message));
  page.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource/.test(m.text())) errosJs.push(m.text()); });

  /* ---------- Topo da Home: slider de largura total ---------- */
  await page.goto(B + '/?posts=8');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; });
  await page.waitForTimeout(500);
  const h = await lerPontos(page, '.rac-hero-cards');
  ok(h && h.n === 4 && h.vis && h.ativo === 0 && h.atual === 1, 'topo da Home: 4 bolinhas, a 1ª ativa');
  ok(h.toque[0] === 28 && h.toque[1] === 28 && h.ativoW === 22 && h.inatW === 8, `bolinhas: área de toque de ${h.toque.join('x')}px, a ativa alongada (22px) e as outras redondas (8px)`);
  const geo = await page.evaluate(() => { const c = document.querySelector('.rac-hero-cards'); const it = c.querySelector('.rac-hero-cards__item').getBoundingClientRect(); return { larg: Math.round(it.width), cont: Math.round(c.getBoundingClientRect().width), snap: getComputedStyle(c).scrollSnapType }; });
  const pico = await page.evaluate(() => { const it = [...document.querySelectorAll('.rac-hero-cards__item')]; return { prox: Math.round(it[1].getBoundingClientRect().left), larg: window.innerWidth }; });
  ok(Math.abs(geo.larg - 291) <= 2 && geo.snap.includes('x') && pico.prox < pico.larg - 20, `cada card tem ${geo.larg}px (86%) e a ponta do próximo aparece (começa em ${pico.prox}px de ${pico.larg}px)`);
  // rolar com o dedo (rolagem programática) acompanha as bolinhas
  await page.evaluate(() => { const c = document.querySelector('.rac-hero-cards'); c.scrollTo({ left: c.clientWidth * 2 + 24, behavior: 'instant' }); });
  ok(await esperar(page, () => document.querySelectorAll('.rac-hero-cards + .rac-pontos .rac-pontos__ponto')[2].getAttribute('aria-current') === 'true'), 'ao rolar até o 3º card, a 3ª bolinha fica ativa');
  // tocar na bolinha leva ao card
  await page.click('.rac-hero-cards + .rac-pontos .rac-pontos__ponto:nth-child(4)');
  ok(await esperar(page, () => { const c = document.querySelector('.rac-hero-cards'); return document.querySelectorAll('.rac-hero-cards + .rac-pontos .rac-pontos__ponto')[3].getAttribute('aria-current') === 'true' && c.scrollLeft >= c.scrollWidth - c.clientWidth - 2; }), 'tocar na 4ª bolinha leva ao último card (Blog)');
  const ultimo = await page.evaluate(() => { const it = [...document.querySelectorAll('.rac-hero-cards__item')][3]; const r = it.getBoundingClientRect(); return { esq: Math.round(r.right), kicker: it.querySelector('.rac-destaque__kicker').textContent }; });
  ok(Math.abs(ultimo.esq - 357) <= 2 && ultimo.kicker === 'Blog', `o último card termina na margem direita de 18px (${ultimo.kicker}, borda direita em ${ultimo.esq}px)`);
  await page.click('.rac-hero-cards + .rac-pontos .rac-pontos__ponto:nth-child(1)');
  ok(await esperar(page, () => document.querySelector('.rac-hero-cards').scrollLeft < 3 && document.querySelectorAll('.rac-hero-cards + .rac-pontos .rac-pontos__ponto')[0].getAttribute('aria-current') === 'true'), 'tocar na 1ª bolinha volta ao card do fundador');
  await page.screenshot({ path: 'capturas/pontos-home-topo-m.png' });

  /* ---------- Auto-avanço do topo ---------- */
  const idxHero = (p) => p.evaluate(() => [...document.querySelectorAll('.rac-hero-cards + .rac-pontos .rac-pontos__ponto')].findIndex(b => b.getAttribute('aria-current') === 'true'));
  const ctxA = await browser.newContext({ viewport: { width: 375, height: 800 } });
  const pa = await ctxA.newPage();
  await pa.goto(B + '/');
  ok(await pa.evaluate(() => document.querySelector('.rac-hero-cards').getAttribute('data-rac-auto') === '5000'), 'topo da Home: auto-avanço de 5 s ligado');
  await pa.waitForTimeout(5800);
  ok((await idxHero(pa)) === 1, 'sem tocar em nada, depois de ~5 s o slider passa sozinho para o 2º card');
  await pa.waitForTimeout(5200);
  ok((await idxHero(pa)) === 2, 'e depois de mais 5 s, para o 3º (as bolinhas acompanham)');
  await pa.evaluate(() => { const c = document.querySelector('.rac-hero-cards'); c.scrollTo({ left: c.scrollWidth, behavior: 'instant' }); });
  await pa.waitForTimeout(5800);
  ok((await idxHero(pa)) === 0, 'depois do último card, volta ao primeiro (rolagem do próprio script não conta como interação)');
  await pa.dispatchEvent('.rac-hero-cards', 'pointerdown');
  await pa.waitForTimeout(5800);
  ok((await idxHero(pa)) === 0, 'depois de uma interação (toque/arrastar), o auto-avanço para de vez');
  await ctxA.close();
  const ctxH = await browser.newContext({ viewport: { width: 375, height: 800 } });
  const ph = await ctxH.newPage();
  await ph.goto(B + '/');
  await ph.hover('.rac-hero-cards');
  await ph.waitForTimeout(5800);
  ok((await idxHero(ph)) === 0, 'com o mouse em cima do slider, o auto-avanço fica em pausa');
  await ctxH.close();
  const ctxR = await browser.newContext({ viewport: { width: 375, height: 800 }, reducedMotion: 'reduce' });
  const pr = await ctxR.newPage();
  await pr.goto(B + '/');
  await pr.waitForTimeout(5800);
  ok((await idxHero(pr)) === 0, '"reduzir movimento" ligado: o slider não passa sozinho');
  await ctxR.close();

  /* ---------- Outras faixas da Home ---------- */
  const e = await lerPontos(page, '.rac-home-destaques-estacoes');
  ok(e && e.n === 3 && e.vis && e.ativo === 0, `Estações em destaque: ${e && e.n} bolinhas (3 cards), a 1ª ativa`);
  await page.click('.rac-home-destaques-estacoes + .rac-pontos .rac-pontos__ponto:nth-child(3)');
  ok(await esperar(page, () => document.querySelectorAll('.rac-home-destaques-estacoes + .rac-pontos .rac-pontos__ponto')[2].getAttribute('aria-current') === 'true' && (() => { const l = document.querySelector('.rac-home-destaques-estacoes'); return l.scrollLeft >= l.scrollWidth - l.clientWidth - 2; })()), 'Estações em destaque: a 3ª bolinha leva ao último card');
  const blog = await lerPontos(page, '.rac-carrossel--posts .rac-carrossel__janela');
  const liv = await lerPontos(page, '.rac-carrossel--livros .rac-carrossel__janela');
  ok(blog && blog.n === 6 && blog.vis && blog.ativo === 0, `Blog: ${blog && blog.n} bolinhas (6 posts)`);
  ok(liv && liv.n >= 5 && liv.n <= 8 && liv.vis, `Literatura: ${liv && liv.n} bolinhas (8 livros; os últimos, que não chegam ao início da faixa, dividem a última)`);
  await page.evaluate(() => document.querySelector('.rac-carrossel--posts').scrollIntoView());
  await page.click('.rac-carrossel--posts .rac-pontos__ponto:last-child');
  ok(await esperar(page, () => { const j = document.querySelector('.rac-carrossel--posts .rac-carrossel__janela'); const bs = document.querySelectorAll('.rac-carrossel--posts .rac-pontos__ponto'); return j.scrollLeft >= j.scrollWidth - j.clientWidth - 2 && bs[bs.length - 1].getAttribute('aria-current') === 'true'; }), 'Blog: a última bolinha leva ao último post');
  await page.evaluate(() => { const j = document.querySelector('.rac-carrossel--posts .rac-carrossel__janela'); j.scrollTo({ left: j.scrollWidth * 0.4, behavior: 'instant' }); });
  ok(await esperar(page, () => { const a = [...document.querySelectorAll('.rac-carrossel--posts .rac-pontos__ponto')].findIndex(b => b.getAttribute('aria-current') === 'true'); return a > 0; }), 'Blog: rolar pelo meio acende uma bolinha do meio');
  ok(await page.evaluate(() => document.querySelectorAll('.rac-pontos').length === 4 && [...document.querySelectorAll('.rac-pontos')].every(p => p.previousElementSibling && p.previousElementSibling.matches('.rac-hero-cards, .rac-home-destaques-estacoes, .rac-carrossel__janela'))), 'a Home tem 4 fileiras de bolinhas (topo, estações, blog, literatura), cada uma logo depois da sua faixa');
  await page.screenshot({ path: 'capturas/pontos-home-m.png', fullPage: true });

  // acessibilidade
  ok(await page.evaluate(() => { const b = document.querySelector('.rac-pontos__ponto'); return b.tagName === 'BUTTON' && /^Ir para o item 1 de \d+$/.test(b.getAttribute('aria-label')) && document.querySelector('.rac-pontos').getAttribute('role') === 'group'; }), 'bolinhas são botões com texto alternativo ("Ir para o item 1 de N")');
  ok(await page.evaluate(() => { const b = document.querySelector('.rac-pontos__ponto'); b.focus(); const cs = getComputedStyle(b); return cs.boxShadow === 'none' && cs.backgroundColor === 'rgba(0, 0, 0, 0)'; }), 'bolinha com foco: sem fundo nem sombra do Kadence');

  /* ---------- Navegação persistente: sem bolinhas duplicadas ---------- */
  await page.click('#racr-botao-play'); await page.waitForTimeout(300);
  await page.evaluate(() => { document.querySelector('a[href$="/estacoes/"]').click(); });
  ok(await esperar(page, () => location.pathname === '/estacoes/'), 'Home → Estações pela navegação persistente');
  ok((await page.evaluate(() => document.querySelectorAll('.rac-pontos').length)) === 0, 'na página de Estações não sobra nenhuma bolinha da Home');
  await page.goBack(); await esperar(page, () => location.pathname === '/'); await page.waitForTimeout(500);
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && document.querySelectorAll('.rac-pontos').length === 4), 'voltando à Home, as 4 fileiras de bolinhas são refeitas, sem duplicar (sem recarregar a página)');

  /* ---------- "Da biblioteca" na página da estação ---------- */
  await page.goto(B + '/estacao/100/');
  const bib = await lerPontos(page, '.rac-biblioteca__lista');
  ok(bib && bib.n >= 4 && bib.n <= 6 && bib.vis, `página da estação, "Da biblioteca": ${bib && bib.n} bolinhas`);
  await page.screenshot({ path: 'capturas/pontos-estacao-m.png', fullPage: true });

  /* ---------- Desktop: sem bolinhas ---------- */
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.goto(B + '/?posts=8');
  ok(await page.evaluate(() => [...document.querySelectorAll('.rac-pontos')].every(p => getComputedStyle(p).display === 'none')), 'desktop: as bolinhas ficam escondidas');
  await page.setViewportSize({ width: 375, height: 800 });
  ok(await esperar(page, () => [...document.querySelectorAll('.rac-pontos')].some(p => getComputedStyle(p).display !== 'none')), 'voltando ao mobile (sem recarregar), as bolinhas reaparecem');
  ok(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), 'mobile: sem rolagem horizontal da página');

  ok(errosJs.length === 0, 'sem erros de JavaScript (' + errosJs.join(' | ') + ')');
  out('\nFALHAS: ' + falhas);
  await browser.close();
  process.exit(falhas ? 1 : 0);
})();
