// Teste de navegador da Fase 3A (página de Estações e Estação individual).
// Veja README.md desta pasta.
const { chromium } = require('playwright');
const B = 'http://localhost:8099';
const out = (...a) => console.log(...a);
let falhas = 0;
const ok = (c, msg) => { out((c ? 'OK   ' : 'FALHA') + ' ' + msg); if (!c) falhas++; };
const esperar = (page, fn, arg, ms = 6000) => page.waitForFunction(fn, arg, { timeout: ms }).then(() => true).catch(() => false);
const norm = s => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
const NOMES = ['Blues & Jazz Clube','Bossa & Violão','Clássicos Contemporâneos','Vozes da Cidade','Sons do Brasil','Poesia Falada','Noites de Ópera','Rock Progressivo BR','MPB de Ouro','Viola Caipira','Samba & Choro','Trilhas de Cinema'];
const ALFA = [...NOMES].sort((a, b) => (norm(a) < norm(b) ? -1 : norm(a) > norm(b) ? 1 : 0));

(async () => {
  const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });
  const errosJs = [];

  /* ---------- HTML: página de Estações ---------- */
  const rq = await (await browser.newContext()).request;
  const htmlE = await (await rq.get(B + '/estacoes/')).text();
  ok((htmlE.match(/id="primary"/g) || []).length === 1, '/estacoes/: exatamente 1 #primary');
  ok(htmlE.indexOf('id="racr-player"') > htmlE.indexOf('</main>'), '/estacoes/: player fora do #primary');
  ok((htmlE.match(/<h1/g) || []).length === 1, '/estacoes/: um único <h1>');
  ok(/placeholder-topo\.jpg/.test(htmlE), '/estacoes/: sem imagem destacada, usa a imagem placeholder do tema');
  ok(!/data-grupo|rac-filtro|rac-chip/.test(htmlE), '/estacoes/: sem filtro por grupo');
  const htmlH = await (await rq.get(B + '/estacoes/?hero=1')).text();
  ok(htmlH.includes('Título editado no painel') && htmlH.includes('Texto editado no painel.'), '/estacoes/: título e texto do topo vêm dos campos ACF quando preenchidos');
  ok(htmlE.includes('Uma trilha para cada momento do dia'), '/estacoes/: sem campos, usa o título padrão do mockup');

  /* ---------- HTML: Estação individual ---------- */
  const htmlS = await (await rq.get(B + '/estacao/100/')).text();
  ok((htmlS.match(/id="primary"/g) || []).length === 1 && htmlS.indexOf('id="racr-player"') > htmlS.indexOf('</main>'), '/estacao/100/: 1 #primary e player fora dele');
  const heroS = (htmlS.match(/<section class="rac-hero rac-hero--estacao"[\s\S]*?<\/section>/) || [''])[0];
  ok(/src="\/h\/capa-0\.svg"/.test(heroS) && !/placeholder-topo/.test(heroS), '/estacao/100/: topo usa a imagem destacada da estação');
  const htmlS2 = await (await rq.get(B + '/estacao/105/')).text();
  ok(/placeholder-topo\.jpg/.test(htmlS2), '/estacao/105/: estação sem imagem destacada usa o placeholder');

  /* ---------- Desktop: Estações ---------- */
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await ctx.newPage();
  page.on('pageerror', e => errosJs.push(e.message));
  page.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource/.test(m.text())) errosJs.push(m.text()); });
  await page.goto(B + '/estacoes/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });

  const d = await page.evaluate(() => {
    const hero = document.querySelector('.rac-hero--lista').getBoundingClientRect();
    const foto = document.querySelector('.rac-hero__foto').getBoundingClientRect();
    const grade = document.querySelector('.rac-grade-img');
    const cards = [...grade.querySelectorAll('.rac-card-img')];
    const r0 = cards[0].getBoundingClientRect();
    return {
      selo: document.querySelector('[data-rac="total-estacoes"]').textContent.trim(),
      h1: document.querySelector('.rac-hero__titulo').textContent.trim(),
      fotoPct: Math.round(100 * foto.width / hero.width),
      colunas: getComputedStyle(grade).gridTemplateColumns.split(' ').length,
      nomes: cards.map(c => c.querySelector('.rac-card-img__nome').textContent.trim()),
      imgs: cards.map(c => (c.querySelector('.rac-card-img__foto img') || {}).src || ''),
      trechos: cards.map(c => c.querySelector('.rac-card-img__trecho')?.textContent || null),
      links: grade.querySelectorAll('a.rac-card-img__ver').length,
      ativo: document.querySelector('.rac-nav a.is-ativo')?.textContent.trim(),
      outraGrade: !!document.querySelector('.rac-grade-estacoes--d, .rac-grade-estacoes--m'),
      prop: Math.round(100 * r0.height / r0.width) / 100,
      nomeCor: getComputedStyle(cards[0].querySelector('.rac-card-img__nome')).color,
      imgCobre: Math.round(cards[0].querySelector('.rac-card-img__foto img').getBoundingClientRect().width) === Math.round(r0.width),
      lh: getComputedStyle(cards[0].querySelector('.rac-card-img__trecho') || cards[0]).display,
    };
  });
  ok(d.selo === '12 estações ao vivo agora', `selo com a contagem real ("${d.selo}")`);
  ok(d.fotoPct === 64, `foto ocupa 64% do topo no desktop (${d.fotoPct}%)`);
  ok(d.colunas === 4, `grade com 4 colunas no desktop (${d.colunas})`);
  ok(d.nomes.length === 12 && JSON.stringify(d.nomes) === JSON.stringify(ALFA), `12 cards com imagem em ordem alfabética (${d.nomes.slice(0, 4).join(', ')}…)`);
  ok(!d.outraGrade, 'só uma grade (os cards antigos saíram da página)');
  const COM_IMAGEM = { 'Blues & Jazz Clube': 'capa-0', 'Bossa & Violão': 'capa-1', 'Clássicos Contemporâneos': 'capa-2', 'Vozes da Cidade': 'capa-3' };
  ok(d.nomes.every((n, i) => COM_IMAGEM[n] ? d.imgs[i].endsWith('/h/' + COM_IMAGEM[n] + '.svg') : d.imgs[i].includes('placeholder-topo.jpg')), `estações com imagem destacada usam ela; as demais usam o placeholder (${d.imgs.filter(u => u.includes('placeholder-topo')).length} placeholders, ${d.imgs.filter(u => u.includes('/h/capa-')).length} imagens)`);
  ok(d.imgCobre, 'a imagem cobre o card inteiro');
  ok(d.trechos[0] !== null && d.trechos[0].startsWith('Primeiro parágrafo da estação Blues') && !/\.Segundo|abertura\.Segundo/.test(d.trechos[0]) && d.trechos[0].split(' ').length <= 19, `trecho do texto da estação (≤18 palavras, parágrafos separados): "${d.trechos[0]}"`);
  ok(d.trechos.slice(1).every(x => x === null), 'estações sem texto cadastrado não mostram trecho');
  ok(d.links === 12, 'cada card tem o link "Ver estação"');
  ok(d.nomeCor === 'rgb(255, 255, 255)', `nome em branco sobre a foto (${d.nomeCor})`);
  const alt = await page.evaluate(() => { const cards = [...document.querySelectorAll('.rac-grade-img .rac-card-img')]; return cards.map(c => { const r = c.getBoundingClientRect(); const live = c.querySelector('.rac-card-img__live').getBoundingClientRect(); const corpo = c.querySelector('.rac-card-img__corpo').getBoundingClientRect(); return { h: Math.round(r.height), w: Math.round(r.width), cortado: c.scrollHeight > c.clientHeight + 1, sobrepoe: live.bottom > corpo.top - 2 }; }); });
  ok(alt.every(x => x.h >= 205 && x.h <= 225), `altura do card ≈210px, 33% menor que antes (${[...new Set(alt.map(x => x.h))].join('/')}px com ${alt[0].w}px de largura)`);
  ok(alt.every(x => !x.cortado && !x.sobrepoe), 'conteúdo do card cabe sem corte e sem encostar no selo "Ao vivo"');
  ok(d.ativo === 'Estações', 'menu: "Estações" fica ativo na página');
  await page.screenshot({ path: 'capturas/f3a-estacoes-d.png', fullPage: true });

  // tocar pelo card; "Ver estação" navega pela navegação persistente
  await page.click('.rac-grade-img .rac-card-img:nth-child(3) .rac-card-img__nome');
  ok(await esperar(page, () => !window.__audio.paused && window.__audio.currentTime > 0.2), 'clicar num card da página toca a estação');
  const tocando = await page.evaluate(() => [...document.querySelectorAll('.rac-grade-img .rac-card-img.is-tocando .rac-card-img__nome')].map(n => n.textContent.trim()));
  ok(tocando.length === 1 && tocando[0] === ALFA[2], `só o card tocando fica destacado (${tocando[0]})`);
  ok(await page.evaluate(() => document.querySelector('.rac-grade-img .rac-card-img.is-tocando .rac-card-img__rotulo').textContent === 'Tocando' && document.querySelector('.rac-grade-img .rac-card-img.is-tocando .rac-card-img__play').getAttribute('aria-pressed') === 'true'), 'card tocando: selo vira "Tocando" e o botão fica aria-pressed');
  await page.click('.rac-grade-img .rac-card-img:nth-child(3) .rac-card-img__play');
  ok(await esperar(page, () => window.__audio.paused && document.querySelectorAll('.rac-grade-img .is-tocando').length === 0), 'o botão de play do card pausa a estação');
  await page.click('.rac-grade-img .rac-card-img:nth-child(3)', { position: { x: 30, y: 60 } });
  ok(await esperar(page, () => !window.__audio.paused), 'clicar na imagem do card volta a tocar');
  const antes = await page.evaluate(() => window.__audio.currentTime);
  await page.click('.rac-grade-img .rac-card-img:nth-child(1) a.rac-card-img__ver');
  ok(await esperar(page, () => /^\/estacao\/\d+\/$/.test(location.pathname)), '"Ver estação" abre a página da estação');
  const nav1 = await page.evaluate(() => ({ m: window.__marcador, p: window.__audio.paused, t: window.__audio.currentTime, h1: document.querySelector('.rac-hero__titulo')?.textContent.trim() }));
  ok(nav1.m === 'mesma-pagina' && !nav1.p && nav1.t >= antes, `navegação sem recarregar e com o áudio tocando (t ${antes.toFixed(2)} → ${nav1.t.toFixed(2)}) → "${nav1.h1}"`);
  ok(nav1.h1 === ALFA[0], `abriu a estação do card clicado (${nav1.h1})`);

  /* ---------- Desktop: Estação individual ---------- */
  await page.goto(B + '/estacao/100/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  const e = await page.evaluate(() => {
    const q = s => document.querySelector(s);
    const ps = [...document.querySelectorAll('.rac-estacao-texto__inner > p')];
    return {
      h1: q('.rac-hero__titulo').textContent.trim(), selo: q('.rac-selo-live--no-ar .rac-selo-live__txt').textContent.trim(),
      voltar: q('.rac-hero__voltar').getAttribute('href'), voltarTxt: q('.rac-hero__voltar').textContent.trim(),
      cta: q('.rac-cta-estacao').textContent.replace(/\s+/g, ' ').trim(),
      lead: ps.length ? getComputedStyle(ps[0]).fontSize : null, resto: ps[1] ? getComputedStyle(ps[1]).fontSize : null, nPs: ps.length, primeiro: ps[0]?.textContent,
      alt: Math.round(q('.rac-hero--estacao').getBoundingClientRect().height), fotoLarg: Math.round(q('.rac-hero__foto').getBoundingClientRect().width), heroLarg: Math.round(q('.rac-hero--estacao').getBoundingClientRect().width),
      tiles: [...document.querySelectorAll('.rac-tile-est__nome')].map(n => n.textContent.trim()),
      tilesCols: getComputedStyle(q('.rac-grade-tiles')).gridTemplateColumns.split(' ').length,
      posts: [...document.querySelectorAll('.rac-ler .rac-card-post__titulo')].map(a => a.getAttribute('href')),
      verTodos: q('.rac-ler .rac-botao-contorno').getAttribute('href'),
      outrasVer: q('.rac-outras .rac-secao__topo a').getAttribute('href'),
      eyebrow: q('.rac-ler .rac-eyebrow').textContent.trim(),
      agoraM: getComputedStyle(q('.rac-agora-m')).display, cardAgora: getComputedStyle(q('.rac-agora-card')).display,
    };
  });
  ok(e.h1 === 'Blues & Jazz Clube' && e.selo === 'No ar agora', `topo: nome da estação e selo "No ar agora" (${e.h1})`);
  ok(e.voltar.endsWith('/estacoes/') && e.voltarTxt === '← Todas as estações', 'link "← Todas as estações" aponta para a página de Estações');
  ok(e.alt >= 500 && e.fotoLarg === e.heroLarg, `topo da estação com ≥500px e foto de fundo inteira (${e.alt}px)`);
  ok(e.cta === 'Ouvir agora Tocando agora', `botão com os dois estados no HTML (${e.cta})`);
  const esp = await page.evaluate(() => { const ps = [...document.querySelectorAll('.rac-estacao-texto__inner > p')]; return [Math.round(ps[1].getBoundingClientRect().top - ps[0].getBoundingClientRect().bottom), Math.round(ps[2].getBoundingClientRect().top - ps[1].getBoundingClientRect().bottom)]; });
  ok(esp[0] === 18 && esp[1] === 18, `parágrafos da estação separados por 18px (${esp.join('px, ')}px)`);
  ok(e.nPs === 3 && e.lead === '22px' && e.resto === '16px', `texto da estação (ACF): ${e.nPs} parágrafos, o primeiro em destaque (${e.lead}/${e.resto})`);
  const outrasEsperadas = (() => { const i = ALFA.indexOf('Blues & Jazz Clube'); return [1, 2, 3, 4].map(k => ALFA[(i + k) % ALFA.length]); })();
  ok(JSON.stringify(e.tiles) === JSON.stringify(outrasEsperadas) && !e.tiles.includes('Blues & Jazz Clube'), `"Outras estações": as 4 seguintes em ordem alfabética, sem a atual (${e.tiles.join(', ')})`);
  ok(e.tilesCols === 4, 'tiles em 4 colunas no desktop');
  const tilesImg = await page.evaluate(() => [...document.querySelectorAll('.rac-tile-est__link img')].map(i => i.src));
  ok(tilesImg.length === 4 && tilesImg.every(u => u.includes('placeholder-topo.jpg') || u.includes('/h/capa-')), `os 4 tiles de "Outras estações" têm imagem (placeholder quando a estação não tem) (${tilesImg.length})`);
  ok(JSON.stringify(e.posts) === JSON.stringify(['/blog/post-502/', '/blog/post-501/', '/blog/post-503/']), `"Para ler": post com tag da estação primeiro e depois os recentes (${e.posts.join(' ')})`);
  ok(e.eyebrow === 'Do blog' && e.verTodos.endsWith('/blog/') && e.outrasVer.endsWith('/estacoes/'), 'links "Ver todos os posts" e "Ver todas" corretos');
  ok(e.agoraM === 'none' && e.cardAgora === 'flex', 'desktop: "Tocando agora" no cartão; linha do mobile escondida');

  // Tocando agora (endpoint do plugin)
  ok(await esperar(page, () => document.querySelector('[data-rac-agora]').textContent.includes('Save My Child')), '"Tocando agora" da estação é preenchido pelo endpoint do plugin');
  const ag = await page.evaluate(() => ({ t: document.querySelector('[data-rac-agora]').textContent, mudo: document.querySelector('[data-rac-agora] .rac-mudo')?.textContent }));
  ok(ag.t === 'Save My Child — Willie Dixon' && ag.mudo === ' — Willie Dixon', `formato "Música — Artista" (${ag.t})`);

  // CTA "Ouvir agora"
  await page.click('.rac-cta-estacao');
  ok(await esperar(page, () => !window.__audio.paused && window.__audio.currentTime > 0.2), '"Ouvir agora" toca a estação');
  const c1 = await page.evaluate(() => ({ cls: document.querySelector('.rac-cta-estacao').classList.contains('is-tocando'), pr: document.querySelector('.rac-cta-estacao').getAttribute('aria-pressed'), nome: document.getElementById('racr-nome-estacao').textContent.trim(), ouvirVis: getComputedStyle(document.querySelector('.rac-cta-estacao__ouvir')).display, tocVis: getComputedStyle(document.querySelector('.rac-cta-estacao__tocando')).display }));
  ok(c1.cls && c1.pr === 'true' && c1.nome === 'Blues & Jazz Clube' && c1.ouvirVis === 'none' && c1.tocVis === 'flex', 'botão vira "Tocando agora" (aria-pressed=true) e o player mostra a estação');
  await page.click('.rac-cta-estacao');
  ok(await esperar(page, () => window.__audio.paused), 'clicar de novo no botão pausa');
  ok(await esperar(page, () => !document.querySelector('.rac-cta-estacao').classList.contains('is-tocando')), 'botão volta para "Ouvir agora"');

  // tile: tocar outra estação sem sair da página; link navega com áudio
  const nomeT = await page.evaluate(() => document.querySelector('.rac-tile-est:nth-child(2) .rac-tile-est__nome').textContent.trim());
  await page.hover('.rac-tile-est:nth-child(2) .rac-tile-est__imagem');
  await page.click('.rac-tile-est:nth-child(2) .rac-tile-est__play');
  ok(await esperar(page, n => !window.__audio.paused && document.getElementById('racr-nome-estacao').textContent.trim() === n, nomeT), `botão do tile toca a estação (${nomeT})`);
  ok(await page.evaluate(() => document.querySelector('.rac-tile-est:nth-child(2)').classList.contains('is-tocando') && !document.querySelector('.rac-cta-estacao').classList.contains('is-tocando') && location.pathname === '/estacao/100/'), 'tile fica .is-tocando, o botão da página não, e a página não muda');
  const antesT = await page.evaluate(() => window.__audio.currentTime);
  await page.click('.rac-tile-est:nth-child(3) .rac-tile-est__nome');
  ok(await esperar(page, () => location.pathname !== '/estacao/100/' && /^\/estacao\/\d+\/$/.test(location.pathname)), 'nome do tile leva para a estação');
  const nav2 = await page.evaluate(() => ({ m: window.__marcador, p: window.__audio.paused, t: window.__audio.currentTime, h1: document.querySelector('.rac-hero__titulo').textContent.trim(), tile: document.querySelectorAll('.rac-tile-est').length }));
  ok(nav2.m === 'mesma-pagina' && !nav2.p && nav2.t >= antesT, `navegação entre estações sem recarregar e com o áudio (${nav2.h1})`);
  ok(await esperar(page, () => /Save My Child|Just the Way You Are/.test(document.querySelector('[data-rac-agora]').textContent)), 'na nova estação o "Tocando agora" recarrega (temporizador reiniciado)');
  await page.screenshot({ path: 'capturas/f3a-estacao-d.png', fullPage: true });

  // estação sem texto ACF e com placeholder
  await page.goto(B + '/estacao/105/');
  const t105 = await page.evaluate(() => ({ ps: [...document.querySelectorAll('.rac-estacao-texto__inner > p')].map(p => p.textContent), posts: [...document.querySelectorAll('.rac-ler .rac-card-post__titulo')].map(a => a.getAttribute('href')), ph: /placeholder-topo/.test(document.querySelector('.rac-hero__foto img').src) }));
  ok(t105.ps.length === 3 && t105.ps[0].includes('Poesia Falada') , 'sem texto ACF: mostra o texto padrão com o nome da estação');
  ok(JSON.stringify(t105.posts) === JSON.stringify(['/blog/post-501/', '/blog/post-502/', '/blog/post-503/']), 'sem tag da estação: 3 posts mais recentes');
  ok(t105.ph, 'topo sem imagem da estação usa o placeholder');

  // larguras sem rolagem horizontal
  for (const rota of ['/estacoes/', '/estacao/100/']) for (const w of [375, 900, 1280]) {
    await page.setViewportSize({ width: w, height: 800 }); await page.goto(B + rota);
    ok(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), `${rota} a ${w}px: sem rolagem horizontal`);
  }

  /* ---------- Mobile ---------- */
  const ctx3 = await browser.newContext({ viewport: { width: 375, height: 800 }, deviceScaleFactor: 2 });
  const m = await ctx3.newPage();
  m.on('pageerror', er => errosJs.push(er.message));
  await m.goto(B + '/estacoes/');
  const mm = await m.evaluate(() => { const g = document.querySelector('.rac-grade-img'); const c = g.querySelector('.rac-card-img'); const r = c.getBoundingClientRect(); return { minH: Math.round(document.querySelector('.rac-hero--lista').getBoundingClientRect().height), fotoCobre: Math.round(document.querySelector('.rac-hero__foto').getBoundingClientRect().width), larg: window.innerWidth, cols: getComputedStyle(g).gridTemplateColumns.split(' ').length, trecho: getComputedStyle(g.querySelector('.rac-card-img__trecho') || document.body).display, play: getComputedStyle(c.querySelector('.rac-card-img__play')).display, ver: getComputedStyle(c.querySelector('.rac-card-img__ver')).display, nomes: g.querySelectorAll('.rac-card-img').length, h1: getComputedStyle(document.querySelector('.rac-hero__titulo')).fontSize, larguraCard: Math.round(r.width), altura: Math.round(r.height) }; });
  ok(mm.minH >= 340 && mm.fotoCobre === mm.larg, `mobile: topo ≥340px com a foto cobrindo a largura (${mm.minH}px)`);
  ok(mm.cols === 2 && mm.nomes === 12, `mobile: cards com imagem em 2 colunas (${mm.larguraCard}x${mm.altura}px)`);
  ok(mm.play !== 'none' && mm.ver !== 'none', 'mobile: card mantém o play e o link "Ver estação"');
  ok(mm.h1 === '31px', `mobile: título do topo com 31px (${mm.h1})`);
  ok(await m.evaluate(() => [...document.querySelectorAll('.rac-grade-img .rac-card-img__trecho')].every(t => getComputedStyle(t).display === 'none')), 'mobile: o trecho de texto fica escondido');
  await m.click('.rac-grade-img .rac-card-img:nth-child(2) .rac-card-img__nome');
  ok(await esperar(m, () => !document.getElementById('racr-audio').paused && document.querySelectorAll('.rac-grade-img .rac-card-img.is-tocando').length === 1), 'mobile: tocar pelo card');
  await m.screenshot({ path: 'capturas/f3a-estacoes-m.png', fullPage: true });

  await m.goto(B + '/estacao/100/');
  const ms = await m.evaluate(() => ({ cta: Math.round(document.querySelector('.rac-cta-estacao').getBoundingClientRect().width), alt: Math.round(document.querySelector('.rac-cta-estacao').getBoundingClientRect().height), cartao: getComputedStyle(document.querySelector('.rac-agora-card')).display, linha: getComputedStyle(document.querySelector('.rac-agora-m')).display, sub: getComputedStyle(document.querySelector('.rac-hero--estacao .rac-hero__texto')).display, tilesCols: getComputedStyle(document.querySelector('.rac-grade-tiles')).gridTemplateColumns.split(' ').length, tilePlay: getComputedStyle(document.querySelector('.rac-tile-est__play')).display, posts: getComputedStyle(document.querySelector('.rac-grade-posts')).gridTemplateColumns.split(' ').length, h1: getComputedStyle(document.querySelector('.rac-hero__titulo')).fontSize }));
  ok(ms.cta >= 330 && ms.alt === 48, `mobile: botão "Ouvir agora" em largura total, 48px (${ms.cta}x${ms.alt})`);
  ok(ms.cartao === 'none' && ms.linha === 'block' && ms.sub === 'none', 'mobile: "Tocando: música — artista" em linha própria; cartão e subtítulo do desktop escondidos');
  ok(await esperar(m, () => document.querySelector('[data-rac-agora-m]').textContent === 'Tocando: Save My Child — Willie Dixon'), 'mobile: texto "Tocando: Save My Child — Willie Dixon"');
  ok(ms.tilesCols === 2 && ms.tilePlay === 'none' && ms.posts === 1 && ms.h1 === '34px', `mobile: tiles em 2 colunas sem play, posts em 1 coluna, título 34px`);
  await m.screenshot({ path: 'capturas/f3a-estacao-m.png', fullPage: true });

  // tema escuro
  for (const [nome, rota, w] of [['f3a-estacoes-escuro', '/estacoes/', 1280], ['f3a-estacao-escuro', '/estacao/100/', 1280]]) {
    const cx = await browser.newContext({ viewport: { width: w, height: 900 } });
    const pg = await cx.newPage(); await pg.addInitScript(() => localStorage.setItem('rac-tema', 'dark'));
    await pg.goto(B + rota); await pg.waitForTimeout(600);
    await pg.screenshot({ path: `capturas/${nome}.png`, fullPage: true }); await cx.close();
  }

  ok(errosJs.length === 0, 'sem erros de JavaScript (' + errosJs.join(' | ') + ')');
  out('\nFALHAS:', falhas);
  await browser.close();
  process.exit(falhas ? 1 : 0);
})();
