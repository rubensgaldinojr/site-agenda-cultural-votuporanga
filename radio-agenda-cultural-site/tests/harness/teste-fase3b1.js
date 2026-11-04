// Teste de navegador da Fase 3B-1 (página inicial). Veja README.md desta pasta.
const { chromium } = require('playwright');
const B = 'http://localhost:8099';
const out = (...a) => console.log(...a);
let falhas = 0;
const ok = (c, msg) => { out((c ? 'OK   ' : 'FALHA') + ' ' + msg); if (!c) falhas++; };
const esperar = (page, fn, arg, ms = 6000) => page.waitForFunction(fn, arg, { timeout: ms }).then(() => true).catch(() => false);
const norm = s => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
const NOMES = ['Blues & Jazz Clube','Bossa & Violão','Clássicos Contemporâneos','Vozes da Cidade','Sons do Brasil','Poesia Falada','Noites de Ópera','Rock Progressivo BR','MPB de Ouro','Viola Caipira','Samba & Choro','Trilhas de Cinema'];
const ALFA = [...NOMES].sort((a, b) => (norm(a) < norm(b) ? -1 : norm(a) > norm(b) ? 1 : 0));

const lerHome = (page) => page.evaluate(() => {
  const q = s => document.querySelector(s); const qa = s => [...document.querySelectorAll(s)];
  const cards = qa('.rac-home-destaques-estacoes .rac-card-img');
  return {
    h1: q('.rac-home-hero__titulo').textContent.trim(), selo: q('.rac-home-selo').textContent.trim(), par: q('.rac-home-hero__paragrafo').textContent.trim(),
    nH1: qa('h1').length, nPrimary: qa('#primary').length,
    heroImg: q('.rac-home-hero__foto img').src,
    ctaId: q('.rac-home-hero .rac-cta-estacao')?.getAttribute('data-estacao-id'),
    fNome: q('.rac-fundador__nome').textContent.replace(/\s+/g, ' ').trim(), fBio: q('.rac-fundador__bio').textContent.replace(/\s+/g, ' ').trim(),
    fImg: q('.rac-fundador__avatar img')?.src || null, fInicial: q('.rac-fundador__avatar b')?.textContent || null,
    dest: qa('.rac-destaque').map(d => ({ k: d.querySelector('.rac-destaque__kicker').textContent, t: d.querySelector('.rac-destaque__titulo').textContent.trim(), x: d.querySelector('.rac-destaque__texto').textContent.trim() })),
    sub: q('.rac-home-estacoes__topo span').textContent.trim(),
    cards: cards.map(c => ({ nome: c.querySelector('.rac-card-img__nome').textContent.trim(), badge: c.querySelector('.rac-card-img__badge')?.textContent.trim(), id: c.getAttribute('data-estacao-id'), desc: c.classList.contains('rac-card-img--descoberta') })),
    todas: qa('.rac-todas__grade--d .rac-card-est__nome').map(n => n.textContent.trim()),
    todasLinks: qa('.rac-todas__grade--d a.rac-card-est__ver').length,
    todasHrefs: qa('.rac-todas__grade--d a.rac-card-est__ver').map(a => a.getAttribute('href')),
    todasLinksM: qa('.rac-todas__grade--m a').length,
    todasTitulo: q('.rac-todas__titulo').textContent.replace(/\s+/g, ' ').trim(),
    sobreTitulo: q('.rac-home-sobre h2').textContent.trim(), sobreTexto: q('.rac-home-sobre p').textContent.trim(), sobreImg: q('.rac-home-sobre__foto img').src,
    sobreLink: q('.rac-home-sobre .rac-botao-contorno').getAttribute('href'), verTodas: q('.rac-botao-claro').getAttribute('href'), conheca: q('.rac-fundador__bio a').getAttribute('href'),
  };
});

(async () => {
  const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });
  const errosJs = [];
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await ctx.newPage();
  page.on('pageerror', e => errosJs.push(e.message));
  page.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource/.test(m.text())) errosJs.push(m.text()); });

  /* ---------- Conteúdo padrão (sem nada preenchido) ---------- */
  await page.goto(B + '/?dia=2026-11-04');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  const h = await lerHome(page);
  const html = await page.content();
  ok(h.nPrimary === 1 && h.nH1 === 1 && html.indexOf('id="racr-player"') > html.indexOf('</main>'), 'um #primary, um <h1> e o player fora do #primary');
  ok(h.h1 === 'Música, cultura e boas conversas, 24 horas por dia' && h.selo === 'Direto de Votuporanga, SP — Brasil' && h.par.startsWith('A Agenda Cultural é uma rádio web'), 'topo com os textos provisórios do mockup');
  ok(h.heroImg.includes('placeholder-topo.jpg'), 'sem imagem destacada na página, o topo usa o placeholder');
  ok(h.fNome === 'Nome do Fundador · fundador e curador' && h.fInicial === 'N' && h.fImg === null, `card do fundador provisório, com a inicial no lugar da foto (${h.fNome})`);
  ok(h.fBio.includes('Radialista há 20 anos') && h.conheca.endsWith('/sobre/'), 'texto do fundador e link "Conheça →" para /sobre/');
  ok(h.dest.length === 3 && h.dest[0].k === 'Estações' && h.dest[0].t === '12 estações no ar, 24h por dia' && h.dest[1].k === 'Literatura' && h.dest[2].k === 'Blog', `3 destaques; o primeiro com a contagem real ("${h.dest[0].t}")`);
  ok(h.dest[1].t === 'Conteúdo literário gratuito' && h.dest[2].x.startsWith('Histórias da cena cultural'), 'textos provisórios dos destaques de Literatura e Blog');
  ok(h.cards.length === 3, 'três cards em "Estações em destaque"');
  ok(h.cards[0].badge === 'Mais ouvida #1' && h.cards[0].nome === 'Bossa & Violão' && h.cards[1].badge === 'Mais ouvida #2' && h.cards[1].nome === 'MPB de Ouro', `mais ouvidas pelo campo "Ouvintes": #1 ${h.cards[0].nome}, #2 ${h.cards[1].nome}`);
  ok(h.cards[2].badge === 'Descoberta do editor' && h.cards[2].desc && !['Bossa & Violão', 'MPB de Ouro'].includes(h.cards[2].nome), `descoberta do editor fora do top 2 (${h.cards[2].nome})`);
  ok(h.ctaId === '101', 'botão "Ouvir agora" do topo aponta para a estação mais ouvida');
  ok(JSON.stringify(h.todas) === JSON.stringify(ALFA), `"Todas as estações": 12 cards em ordem alfabética`);
  ok(h.todasLinks === 12 && h.todasHrefs.every(u => /^\/estacao\/\d+\/$/.test(u)) && h.todasLinksM === 0, `cada card (desktop) tem o link "Ver estação ›" para a página da estação; os compactos do mobile não têm link`);
  ok(h.todasTitulo === 'Todas as estações · 12 estações', `título da lista (${h.todasTitulo})`);
  ok(h.sobreTitulo === 'Uma rádio que nasceu de uma coleção de discos' && h.sobreImg.includes('placeholder-topo.jpg') && h.sobreLink.endsWith('/sobre/') && h.verTodas.endsWith('/estacoes/'), '"Sobre nós" provisório com placeholder e links para /sobre/ e /estacoes/');
  ok(h.sub === 'As duas mais ouvidas e uma descoberta escolhida pelo editor.', 'subtítulo padrão da seção de estações');

  /* ---------- Layout desktop ---------- */
  const d = await page.evaluate(() => {
    const q = s => document.querySelector(s); const r = s => q(s).getBoundingClientRect();
    const hero = r('.rac-home-hero'), foto = r('.rac-home-hero__foto'), card = r('.rac-fundador');
    return {
      fotoPct: Math.round(100 * foto.width / hero.width), fundador: Math.round(card.width),
      colsHero: getComputedStyle(q('.rac-home-hero__grade')).gridTemplateColumns.split(' ').length, col1: Math.round(r('.rac-home-hero__texto').width),
      h1: getComputedStyle(q('.rac-home-hero__titulo')).fontSize,
      colsDest: getComputedStyle(q('.rac-destaques__grade')).gridTemplateColumns.split(' ').length,
      bordas: [...document.querySelectorAll('.rac-destaque')].map(e => getComputedStyle(e).borderLeftWidth),
      colsEst: getComputedStyle(q('.rac-home-destaques-estacoes')).gridTemplateColumns.split(' ').length,
      altCard: Math.round(r('.rac-home-destaques-estacoes .rac-card-img').height),
      colsTodas: getComputedStyle(q('.rac-todas__grade--d')).gridTemplateColumns.split(' ').length,
      mDisp: getComputedStyle(q('.rac-todas__grade--m')).display,
      sobreFoto: [Math.round(r('.rac-home-sobre__foto').width), Math.round(r('.rac-home-sobre__foto').height)],
      claro: getComputedStyle(q('.rac-botao-claro')).display,
      ring: getComputedStyle(q('.rac-card-img--descoberta')).boxShadow !== 'none',
      overflow: document.documentElement.scrollWidth > window.innerWidth,
    };
  });
  ok(d.fotoPct === 62, `foto do topo ocupa 62% (${d.fotoPct}%)`);
  ok(d.colsHero === 2 && d.col1 === 600 && d.fundador === 380 && d.h1 === '46px', `topo em 2 colunas (texto ${d.col1}px, card do fundador ${d.fundador}px, título ${d.h1})`);
  ok(d.colsDest === 3 && d.bordas[0] === '0px' && d.bordas[1] === '1px' && d.bordas[2] === '1px', 'destaques em 3 colunas separadas por linha');
  ok(d.colsEst === 3 && d.altCard === 230, `estações em destaque: 3 colunas, cards de ${d.altCard}px`);
  ok(d.colsTodas === 4 && d.mDisp === 'none', '"Todas as estações": 4 colunas no desktop');
  ok(d.sobreFoto[0] === 420 && d.sobreFoto[1] === 280, `"Sobre nós": foto ${d.sobreFoto.join('x')}`);
  ok(d.ring && d.claro !== 'none' && !d.overflow, 'descoberta com contorno laranja; botão "Ver todas as estações" visível; sem rolagem horizontal');
  await page.screenshot({ path: 'capturas/f3b1-home-d.png', fullPage: true });

  /* ---------- Interações e sincronia com o player ---------- */
  await page.click('.rac-home-hero .rac-cta-estacao');
  ok(await esperar(page, () => !window.__audio.paused && window.__audio.currentTime > 0.2 && document.getElementById('racr-nome-estacao').textContent.trim() === 'Bossa & Violão'), '"Ouvir agora" toca a estação mais ouvida (Bossa & Violão)');
  const s1 = await page.evaluate(() => ({
    cta: document.querySelector('.rac-home-hero .rac-cta-estacao').classList.contains('is-tocando'),
    destaque: document.querySelector('.rac-home-destaques-estacoes .rac-card-img').classList.contains('is-tocando'),
    rotulo: document.querySelector('.rac-home-destaques-estacoes .rac-card-img .rac-card-img__rotulo')?.textContent || null,
    todas: [...document.querySelectorAll('.rac-todas__grade--d .rac-card-est.is-tocando .rac-card-est__nome')].map(n => n.textContent.trim()),
    outros: document.querySelectorAll('.rac-home-destaques-estacoes .rac-card-img.is-tocando').length,
  }));
  ok(s1.cta && s1.destaque && s1.outros === 1 && JSON.stringify(s1.todas) === '["Bossa & Violão"]', 'o mesmo play acende o botão do topo, o card de destaque e o card da lista "Todas"');
  await page.click('.rac-home-hero .rac-cta-estacao');
  ok(await esperar(page, () => window.__audio.paused && document.querySelectorAll('.is-tocando').length === 0), 'o botão do topo também pausa, e tudo se apaga');
  const desc = await page.evaluate(() => document.querySelector('.rac-card-img--descoberta .rac-card-img__nome').textContent.trim());
  await page.click('.rac-card-img--descoberta .rac-card-img__nome');
  ok(await esperar(page, n => !window.__audio.paused && document.getElementById('racr-nome-estacao').textContent.trim() === n, desc), `clicar no card da descoberta toca a estação (${desc})`);
  await page.click('.rac-todas__grade--d .rac-card-est:nth-child(1)');
  ok(await esperar(page, () => document.getElementById('racr-nome-estacao').textContent.trim() === 'Blues & Jazz Clube'), 'clicar num card de "Todas as estações" toca aquela estação');
  const linkAnte = await page.evaluate(() => window.__audio.currentTime);
  await page.click('.rac-todas__grade--d .rac-card-est:nth-child(2) a.rac-card-est__ver');
  ok(await esperar(page, () => location.pathname === '/estacao/101/'), '"Ver estação" de um card da lista abre a página daquela estação (Bossa & Violão)');
  ok(await page.evaluate(a => window.__marcador === 'mesma-pagina' && !window.__audio.paused && document.getElementById('racr-nome-estacao').textContent.trim() === 'Blues & Jazz Clube', linkAnte), 'o link navega sem recarregar e sem trocar a estação que está tocando');
  await page.goBack(); await esperar(page, () => location.pathname === '/'); await page.waitForTimeout(400);
  const antes = await page.evaluate(() => window.__audio.currentTime);
  await page.click('.rac-botao-claro');
  ok(await esperar(page, () => location.pathname === '/estacoes/'), '"Ver todas as estações" abre a página de Estações');
  const n1 = await page.evaluate(() => ({ m: window.__marcador, p: window.__audio.paused, t: window.__audio.currentTime, ativo: document.querySelector('.rac-nav a.is-ativo')?.textContent.trim() }));
  ok(n1.m === 'mesma-pagina' && !n1.p && n1.t >= antes && n1.ativo === 'Estações', 'navegação da Home para Estações sem recarregar, com o áudio e o menu atualizado');
  await page.goBack(); await esperar(page, () => location.pathname === '/'); await page.waitForTimeout(500);
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && !window.__audio.paused && document.querySelectorAll('.rac-nav a.is-ativo').length === 0 && !!document.querySelector('.rac-home-hero')), 'voltar para a Home: sem recarregar, áudio tocando e nenhum item de menu ativo');
  ok(await esperar(page, () => document.querySelectorAll('.rac-card-est.is-tocando, .rac-card-img.is-tocando').length >= 1), 'ao voltar, os cards da Home reaparecem já sincronizados com o player');

  /* ---------- Conteúdo preenchido no painel ---------- */
  await page.goto(B + '/?home=1');
  const e = await lerHome(page);
  ok(e.h1 === 'Título da Home editado' && e.selo === 'Selo editado' && e.par === 'Texto do topo editado.', 'topo: usa os textos do painel');
  ok(e.fNome === 'Maria Souza · curadora' && e.fBio.startsWith('Texto do fundador editado.') && /capa-1\.svg/.test(e.fImg || ''), `fundador: nome, papel, texto e foto do painel (${e.fNome})`);
  ok(e.dest[1].t === 'Livros livres' && e.sub === 'Subtítulo editado', 'destaque e subtítulo editados');
  ok(e.sobreTitulo === 'Sobre editado' && e.sobreTexto === 'Texto sobre editado.' && /foto2\.svg/.test(e.sobreImg), '"Sobre nós": título, texto e foto do painel');

  /* ---------- Mais ouvidas e descoberta ---------- */
  await page.goto(B + '/?semouv=1&dia=2026-11-04');
  const sem = await lerHome(page);
  ok(sem.cards[0].nome === 'Blues & Jazz Clube' && sem.cards[1].nome === 'Bossa & Violão' && sem.ctaId === '100', 'sem ouvintes preenchidos: as duas primeiras na ordem do plugin e "Ouvir agora" na primeira');
  await page.goto(B + '/?modo=fixar&fixa=105');
  const fx = await lerHome(page);
  ok(fx.cards[2].nome === 'Poesia Falada' && fx.cards[2].badge === 'Descoberta do editor', 'descoberta fixada pelo editor (Poesia Falada)');
  await page.goto(B + '/?modo=fixar&fixa=101&dia=2026-11-04');
  const fx2 = await lerHome(page);
  ok(fx2.cards[2].nome !== 'Bossa & Violão' && fx2.cards[2].nome !== 'MPB de Ouro', `fixar uma das mais ouvidas: usa o sorteio (${fx2.cards[2].nome})`);
  const dias = [];
  for (const dia of ['2026-11-04', '2026-11-05', '2026-11-06', '2026-11-07', '2026-11-08']) { await page.goto(`${B}/?dia=${dia}`); dias.push((await lerHome(page)).cards[2].nome); }
  ok(new Set(dias).size >= 4, `a descoberta muda de um dia para o outro (${dias.join(' → ')})`);
  await page.goto(`${B}/?dia=2026-11-04`); const mesmo = (await lerHome(page)).cards[2].nome;
  ok(mesmo === dias[0], 'no mesmo dia a escolha é sempre a mesma');

  /* ---------- Mobile ---------- */
  const ctx3 = await browser.newContext({ viewport: { width: 375, height: 800 }, deviceScaleFactor: 2 });
  const m = await ctx3.newPage();
  m.on('pageerror', er => errosJs.push(er.message));
  await m.goto(B + '/?dia=2026-11-04');
  const mm = await m.evaluate(() => {
    const q = s => document.querySelector(s); const r = s => q(s).getBoundingClientRect(); const cs = (s, p) => getComputedStyle(q(s))[p];
    const lista = q('.rac-home-destaques-estacoes'); const c0 = lista.querySelector('.rac-card-img').getBoundingClientRect();
    return {
      fotoCobre: Math.round(r('.rac-home-hero__foto').width) === window.innerWidth, h1: cs('.rac-home-hero__titulo', 'fontSize'),
      cta: [Math.round(r('.rac-home-hero .rac-cta-estacao').width), Math.round(r('.rac-home-hero .rac-cta-estacao').height)], claro: cs('.rac-botao-claro', 'display'),
      fundador: Math.round(r('.rac-fundador').width), fundBg: cs('.rac-fundador', 'backdropFilter'),
      destCols: cs('.rac-destaques__grade', 'flexDirection'), destTxt: cs('.rac-destaque__texto', 'display'),
      rola: lista.scrollWidth > lista.clientWidth + 10, cardL: Math.round(c0.width), cardAlt: Math.round(c0.height), snap: getComputedStyle(lista).scrollSnapType,
      sub: cs('.rac-home-estacoes__topo span', 'display'), todasD: cs('.rac-todas__grade--d', 'display'),
      todasCols: cs('.rac-todas__grade--m', 'gridTemplateColumns').split(' ').length, todasN: document.querySelectorAll('.rac-todas__grade--m .rac-card-est--compact').length,
      sobre: cs('.rac-home-sobre__inner', 'flexDirection'), sobreAlt: Math.round(r('.rac-home-sobre__foto').height),
      overflow: document.documentElement.scrollWidth > window.innerWidth,
      cardEsq: Math.round(c0.left), botaoSobre: [Math.round(r('.rac-home-sobre .rac-botao-contorno').width), Math.round(r('.rac-home-sobre__texto').width)],
    };
  });
  ok(mm.cardEsq >= 16 && mm.cardEsq <= 20, `mobile: o primeiro card de destaque respeita a margem de 18px (começa em ${mm.cardEsq}px)`);
  ok(mm.botaoSobre[0] === mm.botaoSobre[1], `mobile: botão do "Sobre nós" em largura total (${mm.botaoSobre[0]}px)`);
  ok(mm.fotoCobre && mm.h1 === '30px', `mobile: foto de fundo cobre o topo e o título tem ${mm.h1}`);
  ok(mm.cta[0] >= 330 && mm.cta[1] === 48 && mm.claro === 'none', `mobile: "Ouvir agora" em largura total (${mm.cta.join('x')}) e o botão secundário escondido`);
  ok(mm.fundador >= 330 && mm.destCols === 'column' && mm.destTxt === 'none', 'mobile: card do fundador em largura total; destaques empilhados, só kicker e título');
  ok(mm.rola && mm.cardL === 240 && mm.snap.includes('x'), `mobile: estações em destaque rolam na horizontal (cards de ${mm.cardL}px com encaixe)`);
  ok(mm.sub === 'none' && mm.todasD === 'none' && mm.todasCols === 2 && mm.todasN === 12, 'mobile: "Todas as estações" em 2 colunas com cards compactos');
  ok(mm.sobre === 'column' && mm.sobreAlt === 200 && !mm.overflow, 'mobile: "Sobre nós" empilhado e sem rolagem horizontal');
  await m.click('.rac-home-destaques-estacoes .rac-card-img:nth-child(2) .rac-card-img__nome');
  ok(await esperar(m, () => !document.getElementById('racr-audio').paused && document.querySelectorAll('.rac-home-destaques-estacoes .rac-card-img.is-tocando').length === 1), 'mobile: tocar pelo card de destaque');
  await m.screenshot({ path: 'capturas/f3b1-home-m.png', fullPage: true });
  for (const w of [320, 360, 412, 899]) { await m.setViewportSize({ width: w, height: 800 }); await m.goto(B + '/'); ok(await m.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), `${w}px: sem rolagem horizontal`); }

  /* ---------- Tema escuro ---------- */
  const cx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const pg = await cx.newPage(); await pg.addInitScript(() => localStorage.setItem('rac-tema', 'dark'));
  await pg.goto(B + '/?home=1&dia=2026-11-04'); await pg.waitForTimeout(600);
  await pg.screenshot({ path: 'capturas/f3b1-home-escuro.png', fullPage: true });

  ok(errosJs.length === 0, 'sem erros de JavaScript (' + errosJs.join(' | ') + ')');
  out('\nFALHAS:', falhas);
  await browser.close();
  process.exit(falhas ? 1 : 0);
})();
