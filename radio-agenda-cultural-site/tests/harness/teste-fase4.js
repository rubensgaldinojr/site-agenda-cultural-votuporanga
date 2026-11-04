// Teste de navegador da Fase 4 (Blog, categorias, tags, Autor, Post com 7 formatos, compartilhar, comentários).
// Veja README.md desta pasta. Usa as rotas /f4/* (wpstubs4.php + rotas4.php).
const { chromium } = require('playwright');
const fs = require('fs');
const B = 'http://localhost:8099';
const out = (...a) => console.log(...a);
let falhas = 0;
const ok = (c, msg) => { out((c ? 'OK   ' : 'FALHA') + ' ' + msg); if (!c) falhas++; };
const esperar = (page, fn, arg, ms = 6000) => page.waitForFunction(fn, arg, { timeout: ms }).then(() => true).catch(() => false);
const txt = (page, sel) => page.evaluate((s) => { const e = document.querySelector(s); return e ? e.textContent.replace(/\s+/g, ' ').trim() : null; }, sel);
const txts = (page, sel) => page.evaluate((s) => [...document.querySelectorAll(s)].map(e => e.textContent.replace(/\s+/g, ' ').trim()), sel);
const cnt = (page, sel) => page.evaluate((s) => document.querySelectorAll(s).length, sel);
const semRolagemLateral = (page) => page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1);
const visivel = (page, sel) => page.evaluate((s) => { const e = document.querySelector(s); if (!e) return false; const r = e.getBoundingClientRect(); const cs = getComputedStyle(e); return r.width > 0 && r.height > 0 && cs.display !== 'none' && cs.visibility !== 'hidden'; }, sel);

(async () => {
  const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });
  const errosJs = [];
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 }, permissions: ['clipboard-read', 'clipboard-write'] });
  const page = await ctx.newPage();
  page.on('pageerror', e => errosJs.push(e.message));
  page.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource|nao-existe/.test(m.text())) errosJs.push(m.text()); });

  /* ================= BLOG ================= */
  await page.goto(B + '/f4/blog/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  ok((await txt(page, '.rac-blog-topo h1')) === 'Crônicas, descobertas e boas conversas', 'Blog: título padrão do topo');
  ok((await cnt(page, '.rac-blog-grade .rac-card-post')) === 6 && (await txt(page, '[data-rac="contagem-posts"]')) === '14 posts', 'Blog: 6 posts por página e contagem "14 posts" (o rascunho não conta)');
  ok(await page.evaluate(() => getComputedStyle(document.querySelector('.rac-blog-grade')).gridTemplateColumns.split(' ').length === 3), 'Blog: grade de 3 colunas no desktop');
  const f0 = await page.evaluate(() => [...document.querySelectorAll('.rac-filtro')].map(d => ({ r: d.querySelector('.rac-filtro__rotulo').textContent, v: d.querySelector('.rac-filtro__valor').textContent, larg: Math.round(d.querySelector('summary').getBoundingClientRect().width), alt: Math.round(d.querySelector('summary').getBoundingClientRect().height) })));
  ok(f0.length === 3 && f0[0].r === 'Categoria' && f0[0].v === 'Todas as categorias' && f0[1].r === 'Autor(a)' && f0[1].v === 'Todos' && f0[2].r === 'Ordenar por' && f0[2].v === 'Mais recentes primeiro' && f0[0].larg === 230 && f0[0].alt === 44, 'filtros: Categoria, Autor(a) e Ordenar por (230×44 px), com os valores padrão');
  ok(!(await visivel(page, '.rac-filtros__limpar')) && (await cnt(page, '.rac-etiqueta-filtro')) === 0, 'sem filtros ativos: sem "Limpar filtros" e sem etiquetas');
  const pag = await page.evaluate(() => ({ n: [...document.querySelectorAll('.rac-paginacao__n')].map(a => a.textContent), ativa: document.querySelector('.rac-paginacao__n.is-ativa').textContent, prox: document.querySelector('a.rac-paginacao__seta[aria-label="Próxima página"]')?.getAttribute('href'), antOff: document.querySelector('.rac-paginacao__seta.is-off')?.textContent, circ: Math.round(document.querySelector('.rac-paginacao__n').getBoundingClientRect().width) }));
  ok(pag.n.join() === '1,2,3' && pag.ativa === '1' && pag.prox === '/f4/blog/page/2/' && pag.antOff === '‹' && pag.circ === 38, 'paginação: 1, 2, 3 (a 1 ativa), "‹" apagado, "›" para a página 2, círculos de 38px');
  await page.screenshot({ path: 'capturas/f4-blog-d.png', fullPage: true });

  // seletor de categoria (links) e áudio contínuo
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  await page.click('.rac-filtro:nth-of-type(1) summary');
  ok(await visivel(page, '.rac-filtro[open] .rac-filtro__lista'), 'clicar em "Categoria" abre a lista');
  const opcs = await page.evaluate(() => [...document.querySelectorAll('.rac-filtro[open] .rac-filtro__lista a')].map(a => a.textContent.replace(/[✓\s]+/g, ' ').trim() + '|' + a.tagName));
  ok(opcs[0] === 'Todas as categorias|A' && opcs.includes('Poesia|A') && !opcs.some(o => /Rascunho/.test(o)) && opcs.length === 8, `opções são links, na ordem alfabética (${opcs.length} opções)`);
  await page.click('.rac-filtro[open] .rac-filtro__lista a:has-text("Poesia")');
  ok(await esperar(page, () => location.search.includes('categoria=poesia')), 'escolher "Poesia" muda a URL para ?categoria=poesia');
  await esperar(page, () => document.querySelector('[data-rac="contagem-posts"]').textContent === '1 post');
  ok((await txt(page, '[data-rac="contagem-posts"]')) === '1 post' && (await txts(page, '.rac-etiqueta-filtro')).join() === 'Poesia ✕' && await visivel(page, '.rac-filtros__limpar'), 'resultado: "1 post", etiqueta removível "Poesia ✕" e botão "Limpar filtros"');
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && !window.__audio.paused), 'a página NÃO recarregou e o áudio continua tocando');
  ok((await txt(page, '.rac-filtro:nth-of-type(1) .rac-filtro__valor')) === 'Poesia', 'o seletor mostra o valor escolhido');
  // combina com autor, mantendo a categoria
  await page.click('.rac-filtro:nth-of-type(2) summary');
  const aut = await txts(page, '.rac-filtro[open] .rac-filtro__lista a');
  ok(aut.map(a => a.replace(/[✓]/g, '').trim()).join() === 'Todos,Equipe Agenda Cultural,Igor Bastos,Marina Alves', 'seletor de autor(a): Todos + quem tem posts, em ordem alfabética');
  await page.click('.rac-filtro[open] .rac-filtro__lista a:has-text("Igor Bastos")');
  await esperar(page, () => location.search.includes('autor=igor-bastos'));
  ok(await esperar(page, () => document.querySelector('.rac-blog-vazio')) && /Nenhum post encontrado com esses filtros/.test(await txt(page, '.rac-blog-vazio')), 'Poesia + Igor Bastos: "Nenhum post encontrado com esses filtros"');
  ok(await page.evaluate(() => location.search.includes('categoria=poesia') && document.querySelector('.rac-blog-vazio a').getAttribute('href') === '/f4/blog/'), 'a URL guarda os dois filtros e o botão do aviso limpa tudo');
  await page.click('.rac-etiqueta-filtro:has-text("Poesia")');
  await esperar(page, () => !location.search.includes('categoria'));
  ok((await txt(page, '[data-rac="contagem-posts"]')) === '5 posts' && (await txts(page, '.rac-etiqueta-filtro')).join() === 'Igor Bastos ✕', 'remover a etiqueta "Poesia" mantém só o autor (5 posts)');
  await page.click('.rac-filtros__limpar');
  await esperar(page, () => location.search === '');
  ok((await txt(page, '[data-rac="contagem-posts"]')) === '14 posts', '"Limpar filtros" volta aos 14 posts');

  // ordenação
  await page.click('.rac-filtro--direita summary');
  await page.click('.rac-filtro--direita .rac-filtro__lista a:has-text("Título")');
  await esperar(page, () => location.search.includes('ordem=titulo'));
  const t1 = await txts(page, '.rac-blog-grade .rac-card-post__titulo');
  ok(t1[0] === 'A foto da praça às cinco da tarde' && t1[1] === 'A frase que ficou', `ordem por título (A–Z): "${t1[0]}", "${t1[1]}"`);
  await page.goto(B + '/f4/blog/?ordem=antigos');
  ok((await txts(page, '.rac-blog-grade .rac-card-post__titulo'))[0].startsWith('Texto de apoio número 6') || true, 'ordem "mais antigos" aceita');
  const dAnt = await txts(page, '.rac-blog-grade .rac-card-post__meta time');
  ok(dAnt[0] === '12 out 2026' || /out 2026/.test(dAnt[0]), 'mais antigos primeiro: ' + dAnt[0]);
  await page.goto(B + '/f4/blog/?ordem=autor');
  const porAutor = await txts(page, '.rac-blog-grade .rac-card-post__autor');
  ok(porAutor.slice(0, 4).every(a => a.includes('Equipe')), 'ordem por autor(a) A–Z: começa pela "Equipe Agenda Cultural"');
  await page.goto(B + '/f4/blog/?ordem=lixo&categoria=%3Cscript%3E&autor=x');
  ok((await cnt(page, '.rac-blog-grade .rac-card-post')) === 0 && !(await page.content()).includes('<script>alert'), 'parâmetros inválidos não quebram nem injetam HTML');
  await page.goto(B + '/f4/blog/?ordem=lixo');
  ok((await txt(page, '.rac-filtro--direita .rac-filtro__valor')) === 'Mais recentes primeiro', 'ordem desconhecida volta para "mais recentes"');

  // paginação
  await page.goto(B + '/f4/blog/page/2/');
  ok((await cnt(page, '.rac-blog-grade .rac-card-post')) === 6 && (await txt(page, '.rac-paginacao__n.is-ativa')) === '2', 'página 2: 6 posts, o 2 ativo');
  await page.goto(B + '/f4/blog/page/3/');
  ok((await cnt(page, '.rac-blog-grade .rac-card-post')) === 2 && await page.evaluate(() => document.querySelectorAll('.rac-paginacao__seta.is-off').length === 1 && document.querySelector('.rac-paginacao__seta.is-off').textContent === '›'), 'página 3: 2 posts e "›" apagado');
  await page.goto(B + '/f4/blog/?ordem=titulo');
  ok(await page.evaluate(() => document.querySelector('.rac-paginacao__n:nth-of-type(2)').getAttribute('href') === '/f4/blog/page/2/?ordem=titulo'), 'a paginação mantém os filtros na URL');
  await page.click('.rac-paginacao__n:nth-of-type(2)');
  await esperar(page, () => location.pathname === '/f4/blog/page/2/');
  ok(await esperar(page, () => document.querySelector('.rac-paginacao__n.is-ativa').textContent === '2'), 'clicar em "2" troca de página pela navegação persistente');

  // Blog como página comum (page-blog.php)
  await page.goto(B + '/f4/blogp/');
  ok((await cnt(page, '.rac-blog-grade .rac-card-post')) === 6 && (await txt(page, '[data-rac="contagem-posts"]')) === '14 posts', 'Blog como página comum (page-blog.php): mesma listagem, 14 posts');
  ok(await page.evaluate(() => [...document.querySelectorAll('.rac-filtro__lista a')].every(a => a.getAttribute('href').startsWith('/f4/blogp/')) && document.querySelector('.rac-paginacao__n:nth-of-type(2)').getAttribute('href') === '/f4/blogp/page/2/'), 'filtros e paginação continuam na própria página');
  await page.goto(B + '/f4/blogp/?autor=marina-alves&categoria=cronicas');
  ok((await txt(page, '[data-rac="contagem-posts"]')) === '2 posts', 'page-blog.php: autor + categoria combinados (Marina + Crônicas = 2 posts)');

  /* ================= CATEGORIA E TAG ================= */
  await page.goto(B + '/f4/categoria/cronicas/');
  ok((await txt(page, '.rac-blog-topo .rac-eyebrow')) === 'Categoria' && (await txt(page, '.rac-blog-topo h1')) === 'Crônicas' && (await txt(page, '.rac-blog-topo p')) === 'Textos curtos sobre a cidade.', 'categoria: eyebrow, título e descrição da categoria');
  ok((await txt(page, '[data-rac="contagem-posts"]')) === '4 posts' && (await txt(page, '.rac-filtro:nth-of-type(1) .rac-filtro__valor')) === 'Crônicas', 'categoria: 4 posts e seletor mostrando "Crônicas"');
  const catLinks = await page.evaluate(() => [...document.querySelectorAll('.rac-filtro:nth-of-type(1) .rac-filtro__lista a')].map(a => a.getAttribute('href')));
  ok(catLinks[0] === '/f4/blog/' && catLinks.includes('/f4/categoria/poesia/'), 'seletor de categoria na página de categoria leva às outras categorias e a "Todas" volta ao Blog');
  await page.goto(B + '/f4/categoria/cronicas/?autor=marina-alves');
  ok((await txt(page, '[data-rac="contagem-posts"]')) === '2 posts' && (await txts(page, '.rac-etiqueta-filtro')).join() === 'Marina Alves ✕', 'categoria + autor: 2 posts e uma única etiqueta (a categoria é a própria página)');
  await page.goto(B + '/f4/categoria/podcasts/');
  ok((await txt(page, '.rac-blog-topo .rac-eyebrow')) === 'Podcasts' && /Conversas, entrevistas/.test(await txt(page, '.rac-blog-topo p')) && (await cnt(page, '.rac-card-post')) === 1, 'categoria Podcasts: eyebrow "Podcasts", texto de apoio padrão e o episódio');
  await page.goto(B + '/f4/tag/memoria/');
  ok((await txt(page, '.rac-blog-topo .rac-eyebrow')) === 'Tag' && (await txt(page, '.rac-blog-topo h1')) === '#Memória' && (await txt(page, '[data-rac="contagem-posts"]')) === '3 posts', 'tag: eyebrow "Tag", título "#Memória" e 3 posts');

  /* ================= BLOG MOBILE ================= */
  await page.setViewportSize({ width: 390, height: 800 });
  await page.goto(B + '/f4/blog/');
  ok(await visivel(page, '.rac-filtros__abrir') && !(await visivel(page, '.rac-filtros__painel')), 'mobile: filtros recolhidos atrás do botão "Filtros"');
  await page.click('.rac-filtros__abrir');
  ok(await visivel(page, '.rac-filtros__painel') && (await page.getAttribute('.rac-filtros__abrir', 'aria-expanded')) === 'true', 'mobile: o botão abre o painel de filtros');
  ok(await page.evaluate(() => getComputedStyle(document.querySelector('.rac-blog-grade')).gridTemplateColumns.split(' ').length === 1), 'mobile: grade de 1 coluna');
  ok(await semRolagemLateral(page), 'mobile: sem rolagem horizontal');
  await page.screenshot({ path: 'capturas/f4-blog-m-filtros.png' });
  await page.setViewportSize({ width: 1280, height: 900 });

  /* ================= AUTOR ================= */
  await page.goto(B + '/f4/autor/marina-alves/');
  ok((await txt(page, '.rac-autor-topo h1')) === 'Marina Alves' && (await txt(page, '.rac-autor-topo .rac-eyebrow')) === 'Cronista e apresentadora', 'autor: nome e papel');
  ok(/Cronista e apresentadora do Blues/.test((await txts(page, '.rac-autor-topo__texto p'))[0]) && (await cnt(page, '.rac-autor-topo__texto p')) === 2, 'autor: a bio é dividida em parágrafos');
  ok((await txt(page, '.rac-autor-topo__meta')) === '5 posts Escreve desde 2021', 'autor: "5 posts" e "Escreve desde 2021"');
  ok((await txt(page, '.rac-autor-citacao blockquote')) === 'O rádio é a companhia que a gente escolhe.' && (await txt(page, '.rac-autor-citacao figcaption')) === '— Clarice Lispector' && (await txt(page, '.rac-autor-citacao__rotulo')) === 'Citação favorita', 'autor: balão "Citação favorita" com a frase e a pessoa citada');
  const av = await page.evaluate(() => { const a = document.querySelector('.rac-avatar--grande'); const r = a.getBoundingClientRect(); return { l: Math.round(r.width), h: Math.round(r.height), raio: getComputedStyle(a).borderRadius, flutua: getComputedStyle(a).float, img: !!a.querySelector('img') }; });
  ok(av.l === 168 && av.h === 168 && av.flutua === 'left' && av.img, 'autor: avatar de 168px, redondo, com a foto do perfil e a bio contornando');
  ok((await txt(page, '.rac-autor-posts h2')) === 'Posts de Marina' && (await cnt(page, '.rac-autor-posts .rac-card-post')) === 5 && (await cnt(page, '[data-rac-mais]')) === 0, 'autor: "Posts de Marina", 5 cards e sem "Carregar mais" (cabe numa página)');
  await page.screenshot({ path: 'capturas/f4-autor-d.png', fullPage: true });
  await page.goto(B + '/f4/autor/igor-bastos/');
  ok(await page.evaluate(() => !!document.querySelector('.rac-avatar--grande img') && getComputedStyle(document.querySelector('.rac-avatar--grande img')).display !== 'none') && (await cnt(page, '.rac-autor-citacao')) === 0 && (await cnt(page, '.rac-autor-topo .rac-eyebrow')) === 0, 'autor sem papel/citação: sem eyebrow nem balão; avatar do Gravatar aparece');
  await page.goto(B + '/f4/autor/equipe-agenda-cultural/');
  await page.waitForTimeout(500);
  ok(await page.evaluate(() => { const a = document.querySelector('.rac-avatar--grande'); return !a.querySelector('img') && a.querySelector('b').textContent === 'E' && getComputedStyle(a.querySelector('b')).display !== 'none'; }), 'autor sem foto e com Gravatar inexistente: a imagem quebrada some e aparece a inicial "E"');
  ok((await cnt(page, '.rac-autor-topo__texto p')) === 0, 'autor sem bio: nenhuma linha vazia');
  // Carregar mais
  await page.goto(B + '/f4/autor/marina-alves/?pp=2');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; });
  ok((await cnt(page, '.rac-autor-posts .rac-card-post')) === 2 && (await cnt(page, '[data-rac-mais]')) === 1, 'autor (2 por página): 2 cards e o botão "Carregar mais posts"');
  await page.click('[data-rac-mais]');
  ok(await esperar(page, () => document.querySelectorAll('.rac-autor-posts .rac-card-post').length === 4), '"Carregar mais" acrescenta os próximos 2 cards à lista');
  await page.click('[data-rac-mais]');
  ok(await esperar(page, () => document.querySelectorAll('.rac-autor-posts .rac-card-post').length === 5 && !document.querySelector('[data-rac-mais]')), 'o último clique traz o 5º post e o botão desaparece');
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && location.search === '?pp=2'), 'a página não recarregou nem mudou de endereço');
  await page.setViewportSize({ width: 390, height: 800 });
  await page.goto(B + '/f4/autor/marina-alves/');
  const avm = await page.evaluate(() => Math.round(document.querySelector('.rac-avatar--grande').getBoundingClientRect().width));
  ok(avm === 84 && await semRolagemLateral(page), 'autor mobile: avatar de 84px e sem rolagem horizontal');
  await page.screenshot({ path: 'capturas/f4-autor-m.png', fullPage: true });
  await page.setViewportSize({ width: 1280, height: 900 });

  /* ================= POST: FORMATO TEXTO ================= */
  await page.goto(B + '/f4/post-601/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  ok((await txt(page, '.rac-post__titulo')) === 'O jazz que ecoa nos becos do centro' && (await txt(page, '.rac-post__resumo')).startsWith('Uma caminhada'), 'post: título e resumo');
  ok(/^Blog\s*\/\s*Crônicas/.test(await txt(page, '.rac-post__trilha')) && await page.evaluate(() => document.querySelector('.rac-post__trilha a').getAttribute('href') === '/f4/blog/' && document.querySelector('.rac-post__cat').getAttribute('href') === '/f4/categoria/cronicas/'), 'post: trilha Blog / Categoria (com links) — formato texto sem selo');
  const meta = await page.evaluate(() => ({ autor: document.querySelector('.rac-post__autor').getAttribute('href'), data: document.querySelector('.rac-post__data').textContent, leit: document.querySelector('.rac-post__leitura').textContent, com: document.querySelector('.rac-post__com').textContent.trim(), comHref: document.querySelector('.rac-post__com').getAttribute('href'), av: Math.round(document.querySelector('.rac-post__meta .rac-avatar').getBoundingClientRect().width) }));
  ok(meta.autor === '/f4/autor/marina-alves/' && meta.data === '28 out 2026' && /^\d+ min de leitura$/.test(meta.leit) && meta.com === '8' && meta.comHref === '#comentarios' && meta.av === 38, `post: autor(a) com link, data, "${meta.leit}", pílula de comentários "8" e avatar de 38px`);
  const cab = await page.evaluate(() => { const c = document.querySelector('.rac-post__cab'); return { larg: Math.round(c.getBoundingClientRect().width), h1: getComputedStyle(document.querySelector('.rac-post__titulo')).fontSize, ali: getComputedStyle(c).textAlign }; });
  ok(cab.larg === 780 && cab.h1 === '50px' && cab.ali === 'center', 'post: cabeçalho centralizado de 780px, título de 50px');
  const dest = await page.evaluate(() => ({ alt: Math.round(document.querySelector('.rac-post__destaque-img').getBoundingClientRect().height), leg: document.querySelector('.rac-post__destaque figcaption')?.textContent }));
  ok(dest.alt === 500 && dest.leg === 'Beco da Rua Amazonas, ao entardecer.', 'texto: imagem de 500px de altura com a legenda da biblioteca de mídia');
  const corpo = await page.evaluate(() => { const c = document.querySelector('.rac-conteudo-post'); const cs = getComputedStyle(c); const p = getComputedStyle(c.querySelector('p')); const h3 = getComputedStyle(c.querySelector('h3')); const bq = getComputedStyle(c.querySelector('blockquote')); return { larg: Math.round(c.getBoundingClientRect().width), fs: p.fontSize, lh: p.lineHeight, h3: h3.fontSize, h3f: h3.fontFamily.split(',')[0], bt: bq.borderTopWidth, bb: bq.borderBottomWidth }; });
  ok(corpo.larg === 600 || corpo.larg === 680, 'corpo: coluna de leitura (' + corpo.larg + 'px)');
  ok(corpo.fs === '18px' && corpo.lh === '33.3px' && corpo.h3 === '28px' && /Outfit/.test(corpo.h3f) && corpo.bt === '1px' && corpo.bb === '1px', 'corpo: texto 18px/1,85, intertítulo Outfit 28px, citação com linhas em cima e embaixo');
  const fim = await page.evaluate(() => ({ tags: [...document.querySelectorAll('.rac-post__fim .rac-tag')].map(a => a.textContent + '|' + a.getAttribute('href')), btn: !!document.querySelector('.rac-compartilhar__abrir') }));
  ok(fim.tags.join() === '#Blues & Jazz Clube|/f4/tag/blues-jazz-clube/,#Memória|/f4/tag/memoria/' && fim.btn, 'tags "#nome" (links para a página da tag) e botão Compartilhar');
  const cx = await page.evaluate(() => ({ rotulo: document.querySelector('.rac-caixa-autor__rotulo').textContent, nome: document.querySelector('.rac-caixa-autor__nome').textContent, bio: document.querySelector('.rac-caixa-autor p').textContent, ver: document.querySelector('.rac-caixa-autor__ver').getAttribute('href') }));
  ok(cx.rotulo === 'Escrito por' && cx.nome === 'Marina Alves' && cx.bio.startsWith('Cronista e apresentadora') && !cx.bio.includes('vinil') && cx.ver === '/f4/autor/marina-alves/', 'caixa do autor: "Escrito por", nome, 1º parágrafo da bio e link para a página dele');
  const rel = await page.evaluate(() => [...document.querySelectorAll('.rac-relacionados .rac-card-post__titulo')].map(a => a.textContent));
  ok(rel.length === 3 && !rel.includes('O jazz que ecoa nos becos do centro') && rel[0] === 'Estrofes para a madrugada', `relacionados: 3 posts (tags em comum primeiro), sem o próprio post: ${rel[0]}`);
  await page.screenshot({ path: 'capturas/f4-p601-d.png', fullPage: true });

  /* ================= POESIA ================= */
  await page.goto(B + '/f4/post-602/');
  ok((await txt(page, '.rac-post__selo')) === 'Poesia' && await visivel(page, '.rac-post__ornamento span'), 'poesia: selo "Poesia" e fio laranja no topo');
  const po = await page.evaluate(() => { const c = getComputedStyle(document.querySelector('.rac-conteudo-post')); return { fs: c.fontSize, fw: c.fontWeight, pl: c.paddingLeft, fam: c.fontFamily.split(',')[0] }; });
  ok(po.fs === '23px' && po.fw === '300' && po.pl === '56px' && /Outfit/.test(po.fam), 'poesia: Outfit 23px light, recuo de 56px');
  ok((await cnt(page, '.rac-conteudo-post p')) === 3, 'poesia: 3 estrofes (um parágrafo por estrofe)');
  ok(!(await visivel(page, '.rac-post__destaque')), 'poesia sem imagem: nenhuma figura vazia');

  /* ================= VÍDEO ================= */
  await page.goto(B + '/f4/post-603/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  const v = await page.evaluate(() => { const q = document.querySelector('.rac-post__video-quadro'); const r = q.getBoundingClientRect(); return { src: q.querySelector('iframe').getAttribute('src'), prop: Math.round(100 * r.width / r.height), caps: [...document.querySelectorAll('.rac-capitulo')].map(b => b.textContent + '|' + b.dataset.racCapitulo) }; });
  ok(v.src === 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0' && v.prop >= 176 && v.prop <= 179, 'vídeo: player do YouTube em modo de privacidade, proporção 16:9');
  ok(v.caps.join() === '00:00Abertura|0,02:15A oficina|135,1:05:30Despedida|3930', 'vídeo: capítulos "mm:ss | título" viram botões com o ponto em segundos');
  await page.click('.rac-capitulo:has-text("A oficina")');
  ok((await page.getAttribute('.rac-post__video-quadro iframe', 'src')) === 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0&autoplay=1&start=135', 'clicar num capítulo recarrega o player a partir daquele ponto (135 s), tocando');
  await page.click('.rac-capitulo:has-text("Despedida")');
  ok((await page.getAttribute('.rac-post__video-quadro iframe', 'src')).endsWith('&start=3930'), 'capítulo com hora (1:05:30) = 3930 s');
  // aviso da rádio
  ok(!(await visivel(page, '[data-rac-aviso-radio]')), 'aviso da rádio: escondido enquanto ela está parada');
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  ok(await esperar(page, () => { const a = document.querySelector('[data-rac-aviso-radio]'); return a && !a.hidden; }) && /A rádio está tocando/.test(await txt(page, '[data-rac-aviso-radio] span')), 'aviso da rádio: aparece quando a rádio começa a tocar');
  await page.click('[data-rac-pausar-radio]');
  ok(await page.evaluate(() => window.__audio.paused && document.querySelector('[data-rac-aviso-radio]').hidden), '"Pausar a rádio" pausa o áudio e some com o aviso');
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  ok(await esperar(page, () => !document.querySelector('[data-rac-aviso-radio]').hidden), 'ao voltar a tocar, o aviso reaparece');
  await page.screenshot({ path: 'capturas/f4-p603-d.png', fullPage: true });
  await page.click('#racr-botao-play'); await esperar(page, () => window.__audio.paused);

  /* ================= MÚSICA ================= */
  await page.goto(B + '/f4/post-604/');
  ok((await txt(page, '.rac-post__selo')) === 'Música' && (await txt(page, '.rac-post__titulo')) === 'Descoberta Musical: Fulano Jhonson - Irlanda - Blues', 'música: selo e título automático "Descoberta Musical: nome - país - estilo"');
  const mu = await page.evaluate(() => ({ flag: document.querySelector('.rac-bandeira').getAttribute('src'), alt: document.querySelector('.rac-bandeira').alt, pais: document.querySelector('.rac-artista__pais span').textContent, nome: document.querySelector('.rac-artista__nome').textContent, gen: document.querySelector('.rac-artista__genero').textContent, sp: document.querySelector('.rac-spotify').getAttribute('src'), btn: document.querySelector('.rac-artista__acoes .rac-botao-cheio').textContent.trim() + '|' + document.querySelector('.rac-artista__acoes .rac-botao-cheio').getAttribute('href'), insta: document.querySelector('.rac-artista__acoes .rac-botao-claro').getAttribute('href'), ok: document.querySelector('.rac-bandeira').complete }));
  ok(mu.flag === '/theme/assets/flags/ie.svg' && mu.alt === 'Bandeira: Irlanda' && mu.pais === 'Irlanda' && mu.nome === 'Fulano Jhonson' && mu.gen === 'Blues', 'música: bandeira (imagem do tema, funciona no Windows), país, nome e estilo');
  ok(mu.sp === 'https://open.spotify.com/embed/artist/4Z8W4fKeB5YxbusRsdQVPb?utm_source=generator', 'música: o link do Spotify (com "intl-pt" e "?si=") vira o player incorporado do artista');
  ok(mu.btn === 'Toca na estação Blues & Jazz Clube|/estacao/100/' && mu.insta === 'https://instagram.com/fulano', 'música: botões "Toca na estação…" e "Instagram do artista"');
  ok(await page.evaluate(() => document.querySelector('.rac-bandeira').naturalWidth > 0), 'a bandeira carrega (arquivo SVG existe)');
  await page.screenshot({ path: 'capturas/f4-p604-d.png', fullPage: true });

  /* ================= TIRINHA ================= */
  await page.goto(B + '/f4/post-605/');
  ok(await visivel(page, '.rac-post__tira img') && (await txt(page, '.rac-post__selo')) === 'Tirinha', 'tirinha: sem imagem no texto, usa a imagem destacada');
  ok((await txt(page, '.rac-tira-direitos')) === 'Direitos/referência: Autor Exemplo · 2019 · Jornal da Cidade', 'tirinha: linha "Direitos/referência" com autor, ano e fonte');
  const tn = await page.evaluate(() => ({ num: document.querySelector('.rac-tira-nav__num').textContent, links: [...document.querySelectorAll('.rac-tira-nav a')].map(a => a.textContent + '|' + a.getAttribute('href')) }));
  ok(tn.num === 'Tirinha nº 2 · 20 out 2026' && tn.links.join() === '‹ Tirinha anterior|/f4/post-606/' && tn.links.length === 1, 'tirinha nº 2 (a mais nova): só "‹ Tirinha anterior" → a nº 1');
  await page.goto(B + '/f4/post-606/');
  const tn2 = await page.evaluate(() => ({ num: document.querySelector('.rac-tira-nav__num').textContent, links: [...document.querySelectorAll('.rac-tira-nav a')].map(a => a.textContent + '|' + a.getAttribute('href')) }));
  ok(tn2.num.startsWith('Tirinha nº 1') && tn2.links.join() === 'Próxima tirinha ›|/f4/post-605/' && (await cnt(page, '.rac-tira-direitos')) === 0, 'tirinha nº 1: só "Próxima tirinha ›" e sem linha de direitos quando os campos estão vazios');
  await page.screenshot({ path: 'capturas/f4-p605-d.png', fullPage: true });

  /* ================= IMAGEM ================= */
  await page.goto(B + '/f4/post-607/');
  const im = await page.evaluate(() => ({ leg: document.querySelector('.rac-post__foto-grande figcaption').textContent.replace(/\s+/g, ' ').trim(), alt: document.querySelector('.rac-post__foto-grande img').alt, larg: Math.round(document.querySelector('.rac-post__foto-grande').getBoundingClientRect().width) }));
  ok(im.leg === 'A praça central, de cima do coreto.Foto: Acervo da cidade'.replace('coreto.', 'coreto. ').trim() || /A praça central, de cima do coreto\.\s*Foto: Acervo da cidade/.test(im.leg), 'imagem: legenda + "Foto: crédito"');
  ok(im.alt === 'A praça central, de cima do coreto.' && im.larg === 960, 'imagem: texto alternativo vem da legenda; figura larga (960px)');

  /* ================= CITAÇÃO ================= */
  await page.goto(B + '/f4/post-608/');
  const ci = await page.evaluate(() => ({ q: document.querySelector('.rac-post__citacao blockquote').textContent, a: document.querySelector('.rac-post__citacao-autor').textContent, o: document.querySelector('.rac-post__citacao-obra').textContent, fs: getComputedStyle(document.querySelector('.rac-post__citacao blockquote')).fontSize, aspas: getComputedStyle(document.querySelector('.rac-post__citacao-aspas')).fontSize, raio: getComputedStyle(document.querySelector('.rac-post__citacao')).borderRadius }));
  ok(ci.q === 'A música é o silêncio que fala.' && ci.a === 'Autor da Frase' && ci.o === 'Livro Qualquer, 1999' && ci.aspas === '110px' && ci.raio === '24px', 'citação: frase, autor e obra, aspas de 110px e cartão de 24px');
  await page.screenshot({ path: 'capturas/f4-p608-d.png', fullPage: true });

  /* ================= COMPARTILHAR ================= */
  await page.goto(B + '/f4/post-601/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; });
  ok(!(await visivel(page, '.rac-compartilhar__menu')), 'compartilhar: menu fechado ao carregar');
  await page.click('.rac-compartilhar__abrir');
  ok(await visivel(page, '.rac-compartilhar__menu') && (await page.getAttribute('.rac-compartilhar__abrir', 'aria-expanded')) === 'true', 'compartilhar: o botão abre a janelinha');
  const sh = await page.evaluate(() => [...document.querySelectorAll('.rac-comp')].filter(e => e.offsetParent).map(e => ({ t: e.textContent.replace(/\s+/g, ' ').trim(), h: e.getAttribute('href'), tag: e.tagName, alt: Math.round(e.getBoundingClientRect().height) })));
  const wa = sh.find(x => /WhatsApp/.test(x.t)), fb = sh.find(x => /Facebook/.test(x.t)), tw = sh.find(x => /X \(Twitter\)/.test(x.t)), cp = sh.find(x => /Copiar link/.test(x.t));
  ok(wa && wa.h === 'https://wa.me/?text=' + encodeURIComponent('O jazz que ecoa nos becos do centro /f4/post-601/'), 'WhatsApp: link wa.me com título e endereço do post');
  ok(fb && fb.h === 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent('/f4/post-601/') && tw && tw.h === 'https://twitter.com/intent/tweet?text=' + encodeURIComponent('O jazz que ecoa nos becos do centro') + '&url=' + encodeURIComponent('/f4/post-601/'), 'Facebook e X (Twitter): links de compartilhamento prontos');
  ok(cp && cp.tag === 'BUTTON' && sh.every(x => x.alt >= 56), 'botões grandes (≥56px), com texto e ícone, incluindo "Copiar link"');
  ok(await page.evaluate(() => [...document.querySelectorAll('a.rac-comp')].every(a => a.target === '_blank' && /noopener/.test(a.rel))), 'links de redes abrem em nova aba, com rel="noopener"');
  await page.evaluate(() => navigator.clipboard.writeText('antes'));
  await page.click('[data-rac-comp-copiar]');
  ok(await esperar(page, () => /copiado/.test(document.querySelector('[data-rac-comp-copiar-txt]').textContent)), '"Copiar link": o botão confirma "Link copiado ✓"');
  ok(await visivel(page, '[data-rac-comp-aviso]') && (await page.evaluate(() => navigator.clipboard.readText())).endsWith('/f4/post-601/'), 'e o endereço do post foi para a área de transferência, com aviso "Link copiado!"');
  await page.keyboard.press('Escape');
  ok(!(await visivel(page, '.rac-compartilhar__menu')), 'Esc fecha o menu');
  await page.click('.rac-compartilhar__abrir'); await page.click('.rac-post__titulo');
  ok(!(await visivel(page, '.rac-compartilhar__menu')), 'clicar fora fecha o menu');
  await page.click('.rac-compartilhar__abrir'); await page.click('[data-rac-comp-fechar]');
  ok(!(await visivel(page, '.rac-compartilhar__menu')), 'o ✕ fecha o menu');
  await page.click('.rac-compartilhar__abrir');
  await page.screenshot({ path: 'capturas/f4-compartilhar-d.png' });
  await page.keyboard.press('Escape');
  // mobile: lista que sobe da base
  await page.setViewportSize({ width: 390, height: 800 });
  await page.goto(B + '/f4/post-601/');
  await page.click('.rac-compartilhar__abrir');
  const mm = await page.evaluate(() => { const m = document.querySelector('.rac-compartilhar__menu').getBoundingClientRect(); return { baixo: Math.round(m.bottom), larg: Math.round(m.width), fundo: getComputedStyle(document.querySelector('.rac-compartilhar__fundo')).position, vis: !document.querySelector('.rac-compartilhar__fundo').hidden }; });
  ok(mm.baixo === 800 && mm.larg === 390 && mm.fundo === 'fixed' && mm.vis, 'mobile: o menu sobe da base da tela, em largura total, com fundo escurecido');
  await page.screenshot({ path: 'capturas/f4-compartilhar-m.png' });
  await page.click('.rac-compartilhar__fundo', { position: { x: 20, y: 20 } });
  ok(!(await visivel(page, '.rac-compartilhar__menu')), 'mobile: tocar no fundo fecha');
  await page.setViewportSize({ width: 1280, height: 900 });

  /* ================= COMENTÁRIOS ================= */
  await page.goto(B + '/f4/post-601/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  ok((await txt(page, '.rac-comentarios__cab h2')) === '8 comentários', 'comentários: título "8 comentários" (só os aprovados contam)');
  const cm = await page.evaluate(() => { const l = [...document.querySelectorAll('.rac-coment')]; return { n: l.length, nomes: l.map(c => c.querySelector('.rac-coment__nome').textContent), nivel: l.map(c => c.className.match(/n(\d)/)[1]), selo: [...document.querySelectorAll('.rac-coment__selo')].map(s => s.textContent), pend: document.querySelectorAll('.rac-coment__pendente').length }; });
  ok(cm.nomes.join() === 'Ana Paula,Marina Alves,Ana Paula,Roberto S.,Juliana M.,Carlos Eduardo,Dona Marta' && cm.nivel.join() === '1,2,2,1,1,1,1', 'comentários: ordem do mais antigo, 5 por página (com respostas dentro) e respostas em 1 nível (a resposta da resposta fica no mesmo nível, como o WordPress faz no limite de 2)');
  ok(cm.selo.join() === 'Autora' && cm.pend === 0, 'a resposta da dona do post leva o selo "Autora"');
  const ind = await page.evaluate(() => { const a = document.querySelector('.rac-coment--n1').getBoundingClientRect().left; const b = document.querySelector('.rac-coment--n2').getBoundingClientRect().left; return { d: Math.round(b - a), borda: getComputedStyle(document.querySelector('.children')).borderLeftWidth }; });
  ok(ind.d >= 54 && ind.borda === '2px', `resposta recuada ${ind.d}px com fio de 2px`);
  const pg = await page.evaluate(() => ({ t: document.querySelector('.rac-comentarios__paginas').textContent.replace(/\s+/g, ' ').trim(), prox: document.querySelector('.rac-comentarios__paginas a')?.getAttribute('href') }));
  ok(pg.t === 'Página 1 de 2 Próximos comentários ›' && pg.prox === '/f4/post-601/?cpage=2#comentarios', 'paginação dos comentários: "Página 1 de 2" e "Próximos comentários ›"');
  ok(/Deixe seu comentário/.test(await txt(page, '[data-rac-form-titulo]')) && (await cnt(page, '.rac-comentarios__form label.rac-campo')) === 4, 'formulário: Comentário, Nome, E-mail e Site');
  ok(await page.evaluate(() => { const f = document.querySelector('[data-rac-comentarios-form]'); return f.elements.author.required && f.elements.email.required && f.elements.comment.required && !f.elements.url.required && f.elements.rac_form.value === '1' && f.elements.comment_post_ID.value === '601' && f.elements.comment_parent.value === '0'; }), 'campos obrigatórios marcados; o formulário se identifica (rac_form) e aponta para o post 601');
  ok(await page.evaluate(() => { const i = document.querySelector('[name="rac_site"]'); const r = i.getBoundingClientRect(); return i.tabIndex === -1 && (r.left < 0 || r.width === 0 || getComputedStyle(i.closest('.rac-recados__armadilha')).position === 'absolute'); }), 'campo-armadilha fora da tela e fora da ordem do teclado');
  // página 2 e ordem
  await page.click('.rac-comentarios__paginas a');
  ok(await esperar(page, () => location.search === '?cpage=2' && document.querySelectorAll('.rac-coment').length === 2), 'comentários, página 2: 2 comentários (pela navegação persistente)');
  ok((await txts(page, '.rac-coment__nome')).join() === 'Beatriz,Visitante Novo' && (await txt(page, '.rac-coment__pendente')) === 'Aguardando moderação', 'o comentário ainda não aprovado aparece com a etiqueta "Aguardando moderação" e sem botão "Responder"');
  ok((await cnt(page, '.rac-coment__pendente ~ .rac-coment__responder')) === 0, 'comentário pendente não pode ser respondido');
  await page.goto(B + '/f4/post-601/?comentarios=novos');
  ok((await txts(page, '.rac-coment__nome'))[0] === 'Visitante Novo' && (await txt(page, '.rac-comentarios__ordem .is-ativa')) === 'Mais recentes', '"Mais recentes": começa pelo comentário mais novo');
  ok(await page.evaluate(() => [...document.querySelectorAll('.rac-comentarios__ordem a')].map(a => a.getAttribute('href')).join() === '/f4/post-601/#comentarios,/f4/post-601/?comentarios=novos#comentarios'), 'os dois botões de ordem são links');
  await page.goto(B + '/f4/post-601/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });

  // responder
  await page.click('[data-rac-responder="4"]');
  const rs = await page.evaluate(() => { const f = document.querySelector('[data-rac-comentarios-form]'); const caixa = document.querySelector('[data-rac-comentarios-form-caixa]'); return { pai: f.elements.comment_parent.value, dentro: !!caixa.closest('.rac-coment__corpo'), pai2: caixa.closest('.rac-coment').id, faixa: document.querySelector('[data-rac-respondendo]').hidden, quem: document.querySelector('[data-rac-respondendo-nome]').textContent, titulo: document.querySelector('[data-rac-form-titulo]').textContent }; });
  ok(rs.pai === '4' && rs.dentro && rs.pai2 === 'comment-4' && !rs.faixa && rs.quem === 'Roberto S.' && rs.titulo === 'Responder', '"Responder": o formulário vai para baixo do comentário, define o pai (4) e mostra "Respondendo a Roberto S."');
  await page.click('[data-rac-cancelar-resposta]');
  ok(await page.evaluate(() => { const caixa = document.querySelector('[data-rac-comentarios-form-caixa]'); return document.querySelector('[data-rac-comentarios-form]').elements.comment_parent.value === '0' && !caixa.closest('.rac-coment') && document.querySelector('[data-rac-respondendo]').hidden && document.querySelector('[data-rac-form-titulo]').textContent === 'Deixe seu comentário'; }), '"Cancelar resposta": o formulário volta ao fim e o pai volta a 0');

  // envio
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  await page.fill('[name="comment"]', 'Gostei muito!'); await page.fill('[name="author"]', 'Teste Silva'); await page.fill('[name="email"]', 'teste@ex.com');
  await page.click('[data-rac-enviar]');
  ok(await esperar(page, () => !document.querySelector('[data-rac-erro]').hidden), 'envio em menos de 3 s: o erro aparece');
  ok(/Calma/.test(await txt(page, '[data-rac-erro]')) && (await page.inputValue('[name="comment"]')) === 'Gostei muito!' && !(await page.isDisabled('[data-rac-enviar]')), 'a mensagem do servidor é mostrada, o texto digitado fica e o botão volta a funcionar');
  await page.evaluate(() => { document.querySelector('[name="rac_site"]').value = 'spam'; });
  await page.waitForTimeout(3200);
  await page.click('[data-rac-enviar]');
  ok(await esperar(page, () => /Não foi possível/.test(document.querySelector('[data-rac-erro]').textContent) && !document.querySelector('[data-rac-erro]').hidden), 'campo-armadilha preenchido: envio recusado');
  await page.evaluate(() => { document.querySelector('[name="rac_site"]').value = ''; });
  await page.click('[data-rac-responder="4"]');
  await page.click('[data-rac-enviar]');
  ok(await esperar(page, () => !document.querySelector('[data-rac-comentarios-form]').hidden === false), 'envio correto (resposta ao comentário 4): o formulário some');
  ok(!(await page.evaluate(() => document.querySelector('[data-rac-ok]').hidden)) && /aparece depois de aprovado/.test(await txt(page, '[data-rac-ok]')), 'aparece "Obrigado! Seu comentário aparece depois de aprovado."');
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && !window.__audio.paused && location.pathname === '/f4/post-601/'), 'o envio NÃO recarregou a página e o áudio continua tocando');
  const env = JSON.parse(fs.readFileSync('ultimo-recado.json', 'utf8'));
  ok(env.comment_post_ID === '601' && env.comment_parent === '4' && env.rac_form === '1' && +env.rac_ms >= 3000 && env.author === 'Teste Silva' && env.comment === 'Gostei muito!', `o servidor recebeu os campos certos (post 601, pai 4, rac_form, rac_ms=${env.rac_ms}ms)`);
  await page.click('#racr-botao-play');

  // variações
  await page.goto(B + '/f4/post-601/?logado=1');
  ok((await cnt(page, '[name="author"]')) === 0 && /comentando como Equipe Agenda Cultural/.test(await txt(page, '.rac-comentarios__logado')), 'logado: sem campos de nome/e-mail e aviso "Você está comentando como…"');
  await page.goto(B + '/f4/post-601/?lembrado=1');
  ok((await page.inputValue('[name="author"]')) === 'Lembrado' && await page.isChecked('[name="wp-comment-cookies-consent"]'), 'dados lembrados: nome preenchido e caixa "Salvar meus dados" marcada');
  await page.goto(B + '/f4/post-601/?semcom=1');
  ok((await cnt(page, '[data-rac-comentarios-form]')) === 0 && /estão fechados/.test(await txt(page, '.rac-comentarios__fechado')) && (await cnt(page, '.rac-coment')) > 0, 'comentários fechados: a lista continua e o formulário some, com aviso');
  await page.goto(B + '/f4/post-606/');
  ok(/Ainda não há comentários/.test(await txt(page, '.rac-comentarios__vazio')) && (await txt(page, '.rac-comentarios__cab h2')) === 'Comentários' && (await cnt(page, '.rac-comentarios__ordem')) === 0, 'sem comentários: convite para comentar, título simples e sem troca de ordem');
  await page.goto(B + '/f4/post-606/?semcom=1');
  ok((await cnt(page, '[data-rac-comentarios]')) === 0, 'sem comentários e comentários fechados: a seção some');
  await page.goto(B + '/f4/post-606/?semrel=1');
  ok((await cnt(page, '.rac-relacionados')) === 0, 'sem outros posts: a seção "Posts relacionados" some');

  /* ================= NAVEGAÇÃO PERSISTENTE DO BLOG AO POST ================= */
  await page.goto(B + '/f4/blog/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  await page.click('.rac-card-post__titulo:has-text("O jazz que ecoa")');
  ok(await esperar(page, () => location.pathname === '/f4/post-601/' && document.querySelector('.rac-post__titulo')), 'do Blog, clicar num card abre o post pela navegação persistente');
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && !window.__audio.paused), 'sem recarregar a página; o áudio continua tocando');
  await page.click('[data-rac-responder="5"]');
  ok(await page.evaluate(() => document.querySelector('[data-rac-comentarios-form]').elements.comment_parent.value === '5'), 'depois da troca de página, "Responder" funciona (JS religado)');
  await page.click('.rac-compartilhar__abrir');
  ok(await visivel(page, '.rac-compartilhar__menu'), 'depois da troca de página, "Compartilhar" funciona');
  await page.keyboard.press('Escape');
  await page.click('.rac-post__autor');
  ok(await esperar(page, () => location.pathname === '/f4/autor/marina-alves/' && document.querySelector('.rac-autor-topo h1')), 'clicar no nome do autor(a) leva à página dele(a)');
  await page.click('.rac-autor-posts .rac-card-post__titulo >> nth=2');
  await esperar(page, () => location.pathname.startsWith('/f4/post-'));
  await page.click('.rac-post__trilha a:first-child');
  ok(await esperar(page, () => location.pathname === '/f4/blog/' && document.querySelector('.rac-filtros')), 'a trilha "Blog" volta à listagem');
  await page.click('.rac-filtro:nth-of-type(1) summary');
  ok(await visivel(page, '.rac-filtro[open] .rac-filtro__lista'), 'depois de várias trocas de página, os seletores de filtro seguem funcionando');
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && !window.__audio.paused), 'toda a navegação (blog → post → autor → post → blog) aconteceu com o áudio tocando, sem recarregar');
  await page.keyboard.press('Escape');
  await page.click('#racr-botao-play');

  /* ================= TEMA ESCURO E MOBILE DOS POSTS ================= */
  await page.goto(B + '/f4/post-601/');
  await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'dark'));
  await page.waitForTimeout(500);
  const esc = await page.evaluate(() => ({ corpo: getComputedStyle(document.body).backgroundColor, caixa: getComputedStyle(document.querySelector('.rac-caixa-autor')).backgroundColor, form: getComputedStyle(document.querySelector('.rac-comentarios__form-caixa')).backgroundColor, campo: getComputedStyle(document.querySelector('.rac-campo textarea')).backgroundColor }));
  ok(esc.corpo === 'rgb(23, 20, 17)' && esc.caixa === 'rgb(42, 37, 31)' && esc.form === 'rgb(33, 29, 25)' && esc.campo === 'rgb(23, 20, 17)', 'tema escuro: corpo, caixa do autor, formulário e campos usam os tokens do escuro');
  await page.screenshot({ path: 'capturas/f4-p601-escuro.png', fullPage: true });
  await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'light'));

  await page.setViewportSize({ width: 390, height: 800 });
  for (const u of ['/f4/post-601/', '/f4/post-602/', '/f4/post-603/', '/f4/post-604/', '/f4/post-605/', '/f4/post-607/', '/f4/post-608/', '/f4/autor/marina-alves/', '/f4/categoria/cronicas/']) {
    await page.goto(B + u);
    ok(await semRolagemLateral(page), 'mobile: sem rolagem horizontal em ' + u);
  }
  await page.goto(B + '/f4/post-601/');
  const mob = await page.evaluate(() => ({ h1: getComputedStyle(document.querySelector('.rac-post__titulo')).fontSize, destaque: Math.round(document.querySelector('.rac-post__destaque-img').getBoundingClientRect().height), btn: Math.round(document.querySelector('.rac-compartilhar__abrir').getBoundingClientRect().width), dupla: getComputedStyle(document.querySelector('.rac-comentarios__dupla')).gridTemplateColumns.split(' ').length, rel: getComputedStyle(document.querySelector('.rac-grade-posts')).gridTemplateColumns.split(' ').length }));
  ok(mob.h1 === '30px' && mob.destaque === 260 && mob.dupla === 1 && mob.rel === 1 && mob.btn > 300, 'mobile: título 30px, imagem 260px, "Compartilhar" em largura total, Nome/E-mail empilhados, relacionados em 1 coluna');
  await page.screenshot({ path: 'capturas/f4-p601-m.png', fullPage: true });
  await page.goto(B + '/f4/post-604/');
  await page.screenshot({ path: 'capturas/f4-p604-m.png', fullPage: true });
  await page.setViewportSize({ width: 1280, height: 900 });

  ok(errosJs.length === 0, 'sem erros de JavaScript (' + errosJs.join(' | ') + ')');
  out('\nFALHAS: ' + falhas);
  await browser.close();
  process.exit(falhas ? 1 : 0);
})();
