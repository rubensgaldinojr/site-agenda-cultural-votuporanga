// Teste de navegador da página Sobre (Fase 5A). Rotas /f4/sobre/ (wpstubs4.php + rotas4.php).
const { chromium } = require('playwright');
const B = 'http://localhost:8099';
const out = (...a) => console.log(...a);
let falhas = 0;
const ok = (c, msg) => { out((c ? 'OK   ' : 'FALHA') + ' ' + msg); if (!c) falhas++; };
const esperar = (page, fn, arg, ms = 6000) => page.waitForFunction(fn, arg, { timeout: ms }).then(() => true).catch(() => false);
const txt = (page, sel) => page.evaluate((s) => { const e = document.querySelector(s); return e ? e.textContent.replace(/\s+/g, ' ').trim() : null; }, sel);
const txts = (page, sel) => page.evaluate((s) => [...document.querySelectorAll(s)].map(e => e.textContent.replace(/\s+/g, ' ').trim()), sel);
const cnt = (page, sel) => page.evaluate((s) => document.querySelectorAll(s).length, sel);
const vis = (page, sel) => page.evaluate((s) => { const e = document.querySelector(s); if (!e) return false; const r = e.getBoundingClientRect(); return r.width > 0 && r.height > 0 && getComputedStyle(e).display !== 'none'; }, sel);

(async () => {
  const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });
  const errosJs = [];
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await ctx.newPage();
  page.on('pageerror', e => errosJs.push(e.message));
  page.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource/.test(m.text())) errosJs.push(m.text()); });

  /* ================= DESKTOP ================= */
  await page.goto(B + '/f4/sobre/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  ok((await txt(page, '.rac-sobre-hero h1')) === 'Uma rádio que nasceu de uma coleção de discos' && (await txt(page, '.rac-sobre-hero .rac-home-selo')) === 'Votuporanga, SP — Brasil', 'topo: título e selo (textos provisórios do mockup)');
  ok((await txt(page, '.rac-sobre-hero__texto p')).startsWith('A Agenda Cultural é uma rádio web independente'), 'topo: texto de apresentação');
  const hero = await page.evaluate(() => ({ h1: getComputedStyle(document.querySelector('.rac-sobre-hero h1')).fontSize, foto: Math.round(document.querySelector('.rac-sobre-hero__foto').getBoundingClientRect().width / document.querySelector('.rac-sobre-hero').getBoundingClientRect().width * 100), img: document.querySelector('.rac-sobre-hero__foto img').getAttribute('src') }));
  ok(hero.h1 === '50px' && hero.foto === 62 && /placeholder-topo/.test(hero.img), `topo: título de 50px, foto ocupando 62% e, sem imagem destacada, o placeholder do tema`);
  ok((await txt(page, '.rac-sobre-historia .rac-eyebrow')) === 'Nossa história' && (await txt(page, '.rac-sobre-historia__titulo h2')) === 'De uma estante de vinis para o mundo', 'história: rótulo e título');
  const h = await page.evaluate(() => { const a = getComputedStyle(document.querySelector('.rac-sobre-historia__abertura')); const bl = [...document.querySelectorAll('.rac-sobre-bloco')]; return { a: a.fontSize + '/' + a.fontWeight, fam: a.fontFamily.split(',')[0], blocos: bl.length, sozinho: bl.map(b => b.classList.contains('rac-sobre-bloco--sozinho')), ps: bl.map(b => b.querySelectorAll('.rac-sobre-bloco__texto p').length), pf: getComputedStyle(document.querySelector('.rac-sobre-bloco__texto p')).fontSize, figuras: document.querySelectorAll('.rac-sobre-figura').length, grade: getComputedStyle(document.querySelector('.rac-sobre-historia__cab')).gridTemplateColumns.split(' ').length }; });
  ok(h.a === '22px/300' && /Outfit/.test(h.fam) && h.grade === 2, 'história: rótulo/título à esquerda e o 1º parágrafo como abertura (Outfit 22px leve) à direita');
  ok(h.blocos === 2 && h.ps.join() === '2,1' && h.pf === '17px' && h.figuras === 0 && h.sozinho.join() === 'true,true', 'história provisória sem imagens: bloco 1 (2 parágrafos) e bloco 2 (continuação, 1 parágrafo), cada um ocupando a largura toda, sem figura vazia');
  ok((await txt(page, '.rac-sobre-equipe__topo h2')) === 'As pessoas por trás da rádio' && (await cnt(page, '.rac-sobre-equipe__topo a')) === 0, '"Quem faz": só o título, sem o link "Todos os textos →" (saiu do cabeçalho)');
  const nomes = await txts(page, '.rac-sobre-autor__nome h3');
  ok(nomes.join() === 'Nome do Fundador,Marina Alves,Igor Bastos,Joana Lima', `"Quem faz": só quem foi marcado, na posição escolhida (1, 2, 3) e, sem posição, por último: ${nomes.join(', ')}`);
  ok(!nomes.includes('Equipe Agenda Cultural'), 'usuário NÃO marcado ("Equipe Agenda Cultural") não aparece');
  const dir = await page.evaluate(() => [...document.querySelectorAll('.rac-sobre-autor')].map(a => getComputedStyle(a).flexDirection));
  ok(dir.join() === 'row,row-reverse,row,row-reverse', `o lado da foto alterna a cada pessoa (${dir.join(' / ')})`);
  const foto = await page.evaluate(() => { const e = document.querySelector('.rac-sobre-autor__foto'); const f = e.getBoundingClientRect(); const c = getComputedStyle(e); return { l: Math.round(f.width), a: Math.round(f.height), raio: c.borderRadius, borda: c.borderTopWidth + ' ' + c.borderTopStyle + ' ' + c.borderTopColor }; });
  ok(foto.l === 256 && foto.a === 220 && foto.raio === '16px' && foto.borda === '3px solid rgb(238, 129, 51)', `foto da pessoa: 256×220 (um pouco menor), cantos de 16px e borda laranja de 3px (${foto.borda})`);
  const fotos = await page.evaluate(() => [...document.querySelectorAll('.rac-sobre-autor__foto')].map(f => ({ img: !!f.querySelector('img'), inicial: f.querySelector('b') ? f.querySelector('b').textContent : null })));
  ok(fotos[0].inicial === 'N' && !fotos[0].img && fotos[1].img && fotos[2].img, 'sem foto nem Gravatar: a inicial sobre o fundo listrado; com foto do perfil ou Gravatar: a imagem');
  const info = await page.evaluate(() => [...document.querySelectorAll('.rac-sobre-autor')].map(a => ({ papel: a.querySelector('.rac-sobre-autor__nome span')?.textContent || null, bio: a.querySelector('.rac-sobre-autor__bio')?.textContent.slice(0, 20) || null, itens: [...a.querySelectorAll('.rac-sobre-item')].map(i => i.querySelector('.rac-sobre-item__tipo').textContent + ':' + i.querySelector('.rac-sobre-item__titulo').textContent + ':' + i.getAttribute('href')), ver: a.querySelector('.rac-sobre-autor__ver').getAttribute('href'), titulo: a.querySelector('.rac-sobre-autor__itens-titulo')?.textContent || null })));
  ok(info[0].papel === 'Fundador e curador' && info[1].papel === 'Cronista e apresentadora' && info[2].papel === null && info[2].bio.startsWith('Pesquisador'), 'papel e bio vêm do perfil (omitidos quando vazios)');
  ok(info[1].itens.join('|') === 'Livro:Cidade em voz baixa:http://localhost:8099/literatura/|Artigo:O jazz que ecoa nos becos do centro:/f4/post-601/|Artigo:Estrofes para a madrugada:/f4/post-602/', 'Marina: o livro mais recente (não o antigo) e os 2 textos mais novos; o livro leva à biblioteca e os textos aos posts');
  ok(info[0].itens.length === 1 && info[0].itens[0].startsWith('Livro:Crônicas de uma vitrola') && info[2].itens.map(i => i.split(':')[0]).join() === 'Artigo,Artigo,Artigo' && info[2].itens[0].includes('luthier'), 'Fundador (só tem 1 livro): 1 item; Igor (sem livro): 3 textos, do mais novo ao mais antigo');
  ok(info[3].itens.length === 0 && info[3].bio === null && info[3].papel === null && (await cnt(page, '.rac-sobre-autor:nth-of-type(4) .rac-sobre-autor__itens')) === 0, 'Joana (sem bio, papel nem itens): só nome e foto, sem blocos vazios');
  ok(info[1].ver === '/f4/autor/marina-alves/' && info[3].ver === '/f4/autor/joana-lima/', '"Página do autor →" leva à página de cada pessoa');
  ok(info[0].titulo === 'Publicações recentes' && info[1].titulo === 'Publicações recentes' && info[2].titulo === 'Publicações recentes' && info[3].titulo === null, 'título pequeno "Publicações recentes" acima dos itens (some quando a pessoa não tem itens)');
  const pos = await page.evaluate(() => [...document.querySelectorAll('.rac-sobre-autor')].map(a => { const t = a.querySelector('.rac-sobre-autor__itens-titulo'); const i = a.querySelector('.rac-sobre-autor__itens'); const v = a.querySelector('.rac-sobre-autor__ver'); const bio = a.querySelector('.rac-sobre-autor__bio'); const cs = getComputedStyle(v); return { tituloAcima: t ? t.getBoundingClientRect().bottom <= i.getBoundingClientRect().top + 1 : null, botaoAbaixo: i ? v.getBoundingClientRect().top >= i.getBoundingClientRect().bottom : (bio ? v.getBoundingClientRect().top >= bio.getBoundingClientRect().bottom : true), pilula: cs.borderTopWidth === '1px' && cs.borderRadius !== '0px', esq: Math.round(v.getBoundingClientRect().left) === Math.round((i || bio || v).getBoundingClientRect().left) }; }));
  ok(pos.every(p => p.botaoAbaixo && p.pilula && p.esq) && pos.slice(0, 3).every(p => p.tituloAcima), 'o botão "Página do autor →" fica embaixo dos 3 itens (ou da bio, sem itens), alinhado à esquerda, em formato de pílula; o título fica em cima dos itens');
  const it = await page.evaluate(() => { const l = document.querySelector('.rac-sobre-item--livro .rac-sobre-item__capa').getBoundingClientRect(); const a = document.querySelector('.rac-sobre-item:not(.rac-sobre-item--livro) .rac-sobre-item__capa').getBoundingClientRect(); const g = getComputedStyle(document.querySelectorAll('.rac-sobre-autor__itens')[1]).gridTemplateColumns.split(' ').length; return { livro: Math.round(l.width), artigo: Math.round(a.width), alt: Math.round(a.height), g }; });
  ok(it.livro === 38 && it.artigo === 52 && it.alt === 52 && it.g === 3, 'itens: capa de livro 38×52, de artigo 52×52, em 3 colunas');
  const links = await page.evaluate(() => [...document.querySelectorAll('.rac-sobre-destaques .rac-destaque__link')].map(a => a.textContent.trim() + '|' + a.getAttribute('href')));
  ok(links.join() === 'Veja todas as estações →|http://localhost:8099/estacoes/,Veja todos os livros →|http://localhost:8099/literatura/,Veja todas as publicações →|/f4/blog/', `cada destaque tem o seu link: ${links.map(l => l.split('|')[0]).join(' · ')}`);
  ok(await page.evaluate(() => [...document.querySelectorAll('.rac-sobre-destaques .rac-destaque')].every(d => d.lastElementChild.classList.contains('rac-destaque__link'))), 'o link fica por último, embaixo do texto de cada destaque');
  const dest = await page.evaluate(() => [...document.querySelectorAll('.rac-sobre-destaques .rac-destaque')].map(d => d.querySelector('.rac-destaque__kicker').textContent + '|' + d.querySelector('.rac-destaque__titulo').textContent.trim() + '|' + (getComputedStyle(d.querySelector('.rac-destaque__texto')).display !== 'none')));
  ok(dest.join() === 'Estações|12 estações no ar, 24h por dia|true,Literatura|Conteúdo literário gratuito|true,Blog|Crônicas, bastidores e entrevistas|true', '3 destaques com os mesmos textos da Home (e o número real de estações)');
  const cta = await page.evaluate(() => ({ h: document.querySelector('.rac-sobre-cta h2').textContent, btn: [...document.querySelectorAll('.rac-sobre-cta__botoes a')].map(a => a.textContent.trim() + '|' + a.getAttribute('href')) }));
  ok(cta.h === 'Tem uma música para sugerir?' && cta.btn.join() === 'Fale com a gente|http://localhost:8099/contato/,Ouvir as estações|http://localhost:8099/estacoes/', 'convite final: "Fale com a gente" (Contato) e "Ouvir as estações"');
  await page.screenshot({ path: 'capturas/sobre-d.png', fullPage: true });

  // navegação persistente: da página do autor, sem recarregar e com o áudio tocando
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  await page.click('.rac-sobre-autor:nth-of-type(2) .rac-sobre-autor__ver');
  ok(await esperar(page, () => location.pathname === '/f4/autor/marina-alves/' && document.querySelector('.rac-autor-topo h1')), '"Página do autor →" abre a página do autor pela navegação persistente');
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && !window.__audio.paused), 'sem recarregar a página; o áudio continua tocando');
  await page.click('#racr-botao-play');

  /* ---------- imagens da história ---------- */
  await page.goto(B + '/f4/sobre/?imagens=1');
  const im = await page.evaluate(() => {
    const bl = [...document.querySelectorAll('.rac-sobre-bloco')]; const f = bl.map(b => b.querySelector('.rac-sobre-figura'));
    const r = e => e.getBoundingClientRect(); const t = bl.map(b => b.querySelector('.rac-sobre-bloco__texto'));
    return {
      n: f.length, retrato: f[0].classList.contains('rac-sobre-figura--retrato'), paisagem: f[1].classList.contains('rac-sobre-figura--paisagem'),
      prop1: Math.round(r(f[0].querySelector('.rac-sobre-figura__img')).width / r(f[0].querySelector('.rac-sobre-figura__img')).height * 100), prop2: Math.round(r(f[1].querySelector('.rac-sobre-figura__img')).width / r(f[1].querySelector('.rac-sobre-figura__img')).height * 100),
      lado1: r(f[0]).left < r(t[0]).left, lado2: r(t[1]).left < r(f[1]).left, leg: f.map(x => x.querySelector('figcaption').textContent), alt: f.map(x => x.querySelector('img').alt),
      raio: getComputedStyle(f[0].querySelector('.rac-sobre-figura__img')).borderRadius, deco: getComputedStyle(f[0].querySelector('.rac-sobre-figura__img'), '::before').backgroundColor, decoFigura: getComputedStyle(f[0], '::before').content, decoInset: getComputedStyle(f[0].querySelector('.rac-sobre-figura__img'), '::before').inset, decoH: getComputedStyle(f[0].querySelector('.rac-sobre-figura__img'), '::before').height, imgH: getComputedStyle(f[0].querySelector('.rac-sobre-figura__img')).height, cols: bl.map(b => getComputedStyle(b).gridTemplateColumns.split(' ').length).join(), sozinho: bl.map(b => b.classList.contains('rac-sobre-bloco--sozinho')).join(),
      semOverflow: document.documentElement.scrollWidth <= window.innerWidth,
    };
  });
  ok(im.n === 2 && im.retrato && im.paisagem && im.cols === '2,2' && im.sozinho === 'false,false', 'com as 2 imagens: bloco 1 (imagem em pé + texto) e bloco 2 (texto + imagem deitada), em 2 colunas');
  ok(im.prop1 === 80 && im.prop2 === 150, `proporções das imagens: retrato 4:5 (${im.prop1}%) e paisagem 3:2 (${im.prop2}%), cortadas sem deformar`);
  ok(im.lado1 && im.lado2, 'a Imagem 1 fica à esquerda do texto e a Imagem 2 à direita');
  ok(im.leg.join('|') === 'Legenda da imagem 1|Legenda da imagem 2' && im.alt.join('|') === 'Legenda da imagem 1|Legenda da imagem 2', 'legendas opcionais aparecem embaixo e servem de texto alternativo da imagem');
  ok(im.raio === '20px' && im.deco === 'rgb(253, 238, 226)' && im.semOverflow, 'cantos de 20px, bloco laranja suave atrás da foto (decoração) e sem rolagem horizontal');
  ok(im.decoFigura === 'none' && im.decoH === im.imgH, `o bloco laranja acompanha só a foto, não a legenda (altura do bloco ${im.decoH} = altura da foto ${im.imgH})`);
  await page.screenshot({ path: 'capturas/sobre-imagens-d.png', fullPage: true });
  await page.goto(B + '/f4/sobre/?img1=1');
  ok((await cnt(page, '.rac-sobre-figura')) === 1 && (await cnt(page, '.rac-sobre-bloco:nth-of-type(2) .rac-sobre-figura')) === 1 && (await cnt(page, '.rac-sobre-bloco--sozinho')) === 1 && (await cnt(page, '.rac-sobre-figura figcaption')) === 0, 'só a Imagem 1: o bloco 1 tem imagem + texto; o bloco 2 fica só com texto; sem legenda, nada é desenhado');
  await page.goto(B + '/f4/sobre/?img2=1');
  ok((await cnt(page, '.rac-sobre-figura--paisagem')) === 1 && (await cnt(page, '.rac-sobre-figura--retrato')) === 0 && (await cnt(page, '.rac-sobre-bloco--sozinho')) === 1, 'só a Imagem 2: o bloco 2 tem texto + imagem; o bloco 1 fica só com texto');
  await page.goto(B + '/f4/sobre/?textocheio=1&img2=1');
  const solo = await page.evaluate(() => { const b = document.querySelectorAll('.rac-sobre-bloco'); const i = document.querySelector('.rac-sobre-figura__img').getBoundingClientRect(); return { n: b.length, sozinho: b[1].classList.contains('rac-sobre-bloco--sozinho'), texto: !!b[1].querySelector('.rac-sobre-bloco__texto'), prop: Math.round(i.width / i.height * 10) / 10 }; });
  ok(solo.n === 2 && solo.sozinho && !solo.texto && solo.prop === 2.3, `texto principal preenchido, sem continuação e com Imagem 2: a imagem aparece sozinha, em faixa larga (21:9, ${solo.prop})`);
  await page.goto(B + '/f4/sobre/?continuacao=1');
  ok((await txts(page, '.rac-sobre-bloco:nth-of-type(3) .rac-sobre-bloco__texto p')).join() === 'Continuação própria.' && (await cnt(page, '.rac-sobre-bloco')) === 2, 'continuação preenchida aparece no bloco 2');
  await page.goto(B + '/f4/sobre/?textocheio=1');
  ok((await cnt(page, '.rac-sobre-bloco')) === 1 && (await cnt(page, '.rac-sobre-figura')) === 0, 'texto principal preenchido, sem continuação e sem imagens: só o bloco 1 (sem blocos vazios)');

  /* ---------- textos editados no painel ---------- */
  await page.goto(B + '/f4/sobre/?textos=1');
  ok((await txt(page, '.rac-sobre-hero h1')) === 'Título editado no painel' && (await txt(page, '.rac-sobre-hero .rac-home-selo')) === 'Selo editado' && (await txt(page, '.rac-sobre-hero__texto p')) === 'Texto do topo editado.', 'textos do topo editados no painel substituem os provisórios');
  ok((await txt(page, '.rac-sobre-historia .rac-eyebrow')) === 'Rótulo editado' && (await txt(page, '.rac-sobre-historia__titulo h2')) === 'História editada' && (await txt(page, '.rac-sobre-historia__abertura')) === 'Abertura editada.' && (await cnt(page, '.rac-sobre-historia__abertura b')) === 1, 'história editada: o 1º parágrafo é a abertura (com a formatação em negrito mantida)');
  ok((await txts(page, '.rac-sobre-bloco__texto p')).join('|') === 'Primeiro parágrafo do corpo.|Segundo parágrafo do corpo.' && (await cnt(page, '.rac-sobre-bloco')) === 1, 'o resto da história vira o bloco 1; quem preencheu o texto não ganha o parágrafo provisório de brinde (sem bloco 2)');
  ok((await txt(page, '.rac-sobre-equipe__topo h2')) === 'Equipe editada' && (await txt(page, '.rac-sobre-cta h2')) === 'Convite editado' && (await txt(page, '.rac-sobre-cta p')) === 'Texto do convite editado.', 'título de "Quem faz" e convite final editados');

  /* ---------- N pessoas e nenhuma ---------- */
  await page.goto(B + '/f4/sobre/?muitos=1');
  const muitos = await txts(page, '.rac-sobre-autor__nome h3');
  ok(muitos.join() === 'Nome do Fundador,Marina Alves,Igor Bastos,Pessoa 7,Pessoa 8,Pessoa 9,Joana Lima', `com N pessoas (aqui, 7) a lista cresce sozinha, respeitando a posição: ${muitos.join(', ')}`);
  ok((await page.evaluate(() => [...document.querySelectorAll('.rac-sobre-autor')].map(a => getComputedStyle(a).flexDirection))).join() === 'row,row-reverse,row,row-reverse,row,row-reverse,row', 'o lado da foto continua alternando em toda a lista');
  await page.goto(B + '/f4/sobre/?semequipe=1');
  ok((await cnt(page, '.rac-sobre-equipe')) === 0 && (await cnt(page, '.rac-sobre-historia')) === 1 && (await cnt(page, '.rac-sobre-cta')) === 1, 'ninguém marcado: a seção "Quem faz" some e o resto da página fica');

  /* ---------- tema escuro ---------- */
  await page.goto(B + '/f4/sobre/');
  await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'dark'));
  await page.waitForTimeout(500);
  const esc = await page.evaluate(() => ({ corpo: getComputedStyle(document.body).backgroundColor, item: getComputedStyle(document.querySelector('.rac-sobre-item')).backgroundColor, dest: getComputedStyle(document.querySelector('.rac-sobre-destaques')).backgroundColor, h1: getComputedStyle(document.querySelector('.rac-sobre-hero h1')).color }));
  ok(esc.corpo === 'rgb(23, 20, 17)' && esc.item === 'rgb(33, 29, 25)' && esc.dest === 'rgb(42, 37, 31)' && esc.h1 === 'rgb(243, 236, 227)', 'tema escuro: fundo, cartões dos itens, faixa de destaques e título usam os tokens do escuro');
  await page.screenshot({ path: 'capturas/sobre-escuro.png', fullPage: true });
  await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'light'));

  /* ================= MOBILE ================= */
  await page.setViewportSize({ width: 390, height: 800 });
  await page.goto(B + '/f4/sobre/');
  const m = await page.evaluate(() => {
    const q = s => document.querySelector(s); const r = s => q(s).getBoundingClientRect(); const cs = (s, p) => getComputedStyle(q(s))[p];
    const a = q('.rac-sobre-autor'); const ar = a.getBoundingClientRect();
    return {
      heroAlt: Math.round(r('.rac-sobre-hero').height), h1: cs('.rac-sobre-hero h1', 'fontSize'), hist: cs('.rac-sobre-historia__titulo h2', 'display'), abertura: cs('.rac-sobre-historia__abertura', 'fontSize'),
      foto: [Math.round(r('.rac-sobre-autor__foto').width), Math.round(r('.rac-sobre-autor__foto').height)], fotoRaio: cs('.rac-sobre-autor__foto', 'borderRadius'),
      nomeEsq: Math.round(r('.rac-sobre-autor__nome').left), fotoDir: Math.round(r('.rac-sobre-autor__foto').right),
      bioL: Math.round(r('.rac-sobre-autor__bio').width), cardL: Math.round(ar.width),
      itens: cs('.rac-sobre-autor__itens', 'flexDirection'), itemAlt: Math.round(r('.rac-sobre-item').height), livroCapa: Math.round(r('.rac-sobre-item--livro .rac-sobre-item__capa').width),
      ver: [cs('.rac-sobre-autor__ver', 'borderTopWidth'), cs('.rac-sobre-autor__ver', 'backgroundColor')], tituloItens: cs('.rac-sobre-autor__itens-titulo', 'display'), destaques: cs('.rac-sobre-destaques', 'display'), blocos: [...document.querySelectorAll('.rac-sobre-bloco')].map(b => getComputedStyle(b).gridTemplateColumns.split(' ').length).join(),
      btn: [Math.round(r('.rac-sobre-cta .rac-botao-cheio').width), Math.round(r('.rac-sobre-cta .rac-botao-cheio').height)], segundo: cs('.rac-sobre-cta .rac-botao-claro', 'display'),
      overflow: document.documentElement.scrollWidth > window.innerWidth,
      dirs: [...document.querySelectorAll('.rac-sobre-autor')].map(x => getComputedStyle(x).display).join(),
    };
  });
  ok(m.heroAlt >= 440 && m.h1 === '31px', `mobile: topo com no mínimo 440px (${m.heroAlt}px) e título de 31px`);
  ok(m.hist === 'none' && m.abertura === '19px' , 'mobile: na história só o rótulo e a abertura (19px), sem o título grande');
  ok(m.foto[0] === 72 && m.foto[1] === 72 && m.fotoRaio === '12px' && m.nomeEsq > m.fotoDir, `mobile: foto de 72px ao lado do nome (nome começa em ${m.nomeEsq}px, foto termina em ${m.fotoDir}px)`);
  ok(m.bioL >= m.cardL - 2 && m.itens === 'column' && m.itemAlt >= 52 && m.livroCapa === 30, `mobile: bio e itens em largura total (${m.bioL}px), itens empilhados, capa de livro de 30px`);
  ok(m.ver[0] === '0px' && m.ver[1] === 'rgba(0, 0, 0, 0)' && m.tituloItens !== 'none' && m.destaques === 'none' && m.blocos === '1,1', 'mobile: "Publicações recentes" em cima dos itens e "Página do autor →" como link no fim do bloco; história em coluna única; faixa de destaques escondida');
  ok(m.btn[0] > 330 && m.btn[1] === 48 && m.segundo === 'none', `mobile: "Fale com a gente" em largura total (${m.btn.join('x')}) e sem o botão secundário`);
  ok(!m.overflow, 'mobile: sem rolagem horizontal');
  await page.screenshot({ path: 'capturas/sobre-m.png', fullPage: true });
  await page.goto(B + '/f4/sobre/?muitos=1');
  ok(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), 'mobile com 7 pessoas: sem rolagem horizontal');
  await page.goto(B + '/f4/sobre/?imagens=1');
  const mi = await page.evaluate(() => { const bl = [...document.querySelectorAll('.rac-sobre-bloco')]; const r = e => e.getBoundingClientRect(); const i = bl.map(b => r(b.querySelector('.rac-sobre-figura__img'))); return { imgAntes: bl.map(b => r(b.querySelector('.rac-sobre-figura')).top < r(b.querySelector('.rac-sobre-bloco__texto')).top).join(), prop: i.map(x => Math.round(x.width / x.height * 100)).join(), larg: i.map(x => Math.round(x.width)).join(), semOverflow: document.documentElement.scrollWidth <= window.innerWidth }; });
  ok(mi.imgAntes === 'true,true' && mi.prop === '133,133' && mi.semOverflow, `mobile com imagens: a imagem vem antes do texto nos dois blocos (4:3, ${mi.larg}px) e sem rolagem horizontal`);
  await page.screenshot({ path: 'capturas/sobre-imagens-m.png', fullPage: true });

  ok(errosJs.length === 0, 'sem erros de JavaScript (' + errosJs.join(' | ') + ')');
  out('\nFALHAS: ' + falhas);
  await browser.close();
  process.exit(falhas ? 1 : 0);
})();
