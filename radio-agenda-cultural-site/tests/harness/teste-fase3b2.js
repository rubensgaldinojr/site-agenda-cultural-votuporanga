// Teste de navegador da Fase 3B-2 (carrosséis, livros, podcasts, recados, "Da biblioteca").
// Veja README.md desta pasta.
const { chromium } = require('playwright');
const fs = require('fs');
const B = 'http://localhost:8099';
const out = (...a) => console.log(...a);
let falhas = 0;
const ok = (c, msg) => { out((c ? 'OK   ' : 'FALHA') + ' ' + msg); if (!c) falhas++; };
const esperar = (page, fn, arg, ms = 6000) => page.waitForFunction(fn, arg, { timeout: ms }).then(() => true).catch(() => false);

// lê o estado de um carrossel
const car = (page, sel) => page.evaluate((s) => {
  const r = document.querySelector(s); const t = r.querySelector('.rac-carrossel__trilho'); const j = r.querySelector('.rac-carrossel__janela');
  const itens = [...t.children]; const clones = itens.filter(i => i.hasAttribute('data-rac-clone'));
  const orig = itens.filter(i => !i.hasAttribute('data-rac-clone'));
  const passo = orig.length > 1 ? orig[1].offsetLeft - orig[0].offsetLeft : 0;
  const m = /translateX\((-?[\d.]+)px\)/.exec(t.style.transform || '');
  const setas = getComputedStyle(r.querySelector('.rac-carrossel__setas')).display;
  return { inf: r.classList.contains('is-infinito'), n: orig.length, clones: clones.length, pos: t.getAttribute('data-pos'), passo,
    indice: m && passo ? Math.round(-parseFloat(m[1]) / passo * 100) / 100 : null, setas, larg: Math.round(orig[0].getBoundingClientRect().width),
    overflowX: getComputedStyle(j).overflowX, snap: getComputedStyle(j).scrollSnapType, esq: Math.round(orig[0].getBoundingClientRect().left),
    inert: clones.every(c => c.hasAttribute('inert') && c.getAttribute('aria-hidden') === 'true') };
}, sel);

(async () => {
  const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });
  const errosJs = [];
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await ctx.newPage();
  page.on('pageerror', e => errosJs.push(e.message));
  page.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource/.test(m.text())) errosJs.push(m.text()); });

  /* ---------- Home padrão: 3 posts, 8 livros, sem podcasts, 7 recados ---------- */
  await page.goto(B + '/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  const ordem = await page.evaluate(() => { const pos = s => { const e = document.querySelector(s); return e ? Array.prototype.indexOf.call(document.querySelectorAll('main > section'), e) : -1; }; return ['.rac-home-estacoes', '.rac-carrossel--posts', '.rac-carrossel--livros', '.rac-home-sobre', '.rac-podcasts', '.rac-recados'].map(s => pos(s.startsWith('.rac-carrossel') ? s.replace('.rac-carrossel', '.rac-faixa:has(.rac-carrossel') + ')' : s)); });
  ok(ordem[0] < ordem[1] && ordem[1] < ordem[2] && ordem[2] < ordem[3] && ordem[3] < ordem[5] && ordem[4] === -1, `ordem das seções: estações → blog → literatura → sobre nós → recados (podcasts escondido) [${ordem.join(',')}]`);

  const blog = await car(page, '.rac-carrossel--posts');
  ok(!blog.inf && blog.n === 3 && blog.clones === 0 && blog.setas === 'none' && blog.larg === 296, `blog com poucos posts (3): grade simples, sem clones nem setas, cards de ${blog.larg}px`);
  ok(await page.evaluate(() => document.querySelector('.rac-carrossel--posts .rac-faixa__rodape a').getAttribute('href').endsWith('/blog/') && document.querySelector('.rac-carrossel--posts h2').textContent === 'Do Blog'), 'blog: título "Do Blog" e botão "Ver todos os posts →" para /blog/');
  const liv = await car(page, '.rac-carrossel--livros');
  ok(liv.inf && liv.n === 8 && liv.clones === 16 && liv.setas === 'flex' && liv.larg === 168 && liv.passo === 188 && liv.inert, `literatura com 8 livros: carrossel infinito (16 clones inertes, cards de ${liv.larg}px, passo ${liv.passo}px, setas visíveis)`);
  const li = await page.evaluate(() => { const it = [...document.querySelectorAll('.rac-carrossel--livros .rac-carrossel__item:not([data-rac-clone])')]; return { titulos: it.map(i => i.querySelector('.rac-livro__titulo').textContent), autores: it.map(i => i.querySelector('.rac-livro__autor')?.textContent), links: [...new Set(it.map(i => i.querySelector('a').getAttribute('href')))], semCapa: it[2].querySelector('img') === null && it[2].querySelector('.rac-livro__capa').classList.contains('stripe-cover'), comCapa: !!it[0].querySelector('img'), prop: Math.round(100 * it[0].querySelector('.rac-livro__capa').getBoundingClientRect().height / it[0].querySelector('.rac-livro__capa').getBoundingClientRect().width), btn: document.querySelector('.rac-carrossel--livros .rac-faixa__rodape a').getAttribute('href'), btnTxt: document.querySelector('.rac-carrossel--livros .rac-faixa__rodape a .rac-so-desktop').textContent, titEl: getComputedStyle(it[2].querySelector('.rac-livro__titulo')).textOverflow }; });
  ok(li.titulos[0] === 'Contos da Cidade Baixa' && li.autores[0] === 'Marina Alves' && li.autores[1] === 'Igor Bastos' && li.comCapa && li.semCapa, 'card de livro: título, autor, capa (ou placeholder listrado quando não há capa)');
  ok(li.links.length === 1 && li.links[0].endsWith('/literatura/') && li.btn.endsWith('/literatura/') && li.btnTxt === 'Explorar a biblioteca completa →', 'cards de livro e botão levam para a página Literatura');
  ok(li.prop >= 138 && li.prop <= 142 && li.titEl === 'ellipsis', `capa em proporção de livro (altura ${li.prop}% da largura) e título longo cortado com reticências`);
  ok(await page.evaluate(() => !document.querySelector('.rac-podcasts')), 'sem subcategorias de Podcasts, a seção não aparece');
  await page.screenshot({ path: 'capturas/f3b2-home-d.png', fullPage: true });

  // clicar num livro navega pela navegação persistente
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  await page.click('.rac-carrossel--livros .rac-carrossel__item:not([data-rac-clone]) a');
  ok(await esperar(page, () => location.pathname === '/literatura/'), 'clicar num livro abre a página Literatura');
  await page.goBack(); await esperar(page, () => location.pathname === '/'); await page.waitForTimeout(500);

  /* ---------- Carrossel do Blog com 8 posts ---------- */
  await page.goto(B + '/?posts=8');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; });
  let b = await car(page, '.rac-carrossel--posts');
  ok(b.inf && b.n === 6 && b.clones === 12 && b.passo === 316 && b.setas === 'flex' && b.pos === '0' && b.indice === 6, `blog com 8 posts cadastrados mostra os 6 mais recentes em carrossel infinito (passo ${b.passo}px, posição inicial ${b.pos})`);
  // voltar: dá a volta para o último
  await page.click('.rac-carrossel--posts [data-rac-prev]'); await page.waitForTimeout(800);
  b = await car(page, '.rac-carrossel--posts');
  ok(b.pos === '5' && b.indice >= 6 && b.indice < 12, `seta "‹" no primeiro dá a volta para o último (pos ${b.pos}, índice normalizado ${b.indice})`);
  // avançar n vezes volta ao início, sempre normalizado
  let todosNormais = true;
  for (let k = 0; k < 6; k++) { await page.click('.rac-carrossel--posts [data-rac-next]'); await page.waitForTimeout(760); const s = await car(page, '.rac-carrossel--posts'); if (!(s.indice >= 6 && s.indice < 12)) todosNormais = false; }
  b = await car(page, '.rac-carrossel--posts');
  ok(b.pos === '5' && todosNormais, `6 cliques em "›" completam a volta (pos ${b.pos}) e o carrossel nunca sai do bloco do meio`);
  // cliques rápidos não quebram
  for (let k = 0; k < 6; k++) await page.click('.rac-carrossel--posts [data-rac-next]', { delay: 0 });
  await page.waitForTimeout(1200);
  b = await car(page, '.rac-carrossel--posts');
  ok(b.indice >= 6 && b.indice < 12, `cliques muito rápidos não deixam espaço vazio (índice ${b.indice}, pos ${b.pos})`);
  // visual: o item da vez está visível e o trilho cobre a janela
  ok(await page.evaluate(() => { const j = document.querySelector('.rac-carrossel--posts .rac-carrossel__janela').getBoundingClientRect(); const t = document.querySelector('.rac-carrossel--posts .rac-carrossel__trilho'); const itens = [...t.children].map(i => i.getBoundingClientRect()); const sobre = itens.filter(r => r.right > j.left && r.left < j.right); const ultimo = itens[itens.length - 1]; return sobre.length >= 4 && ultimo.right > j.right; }), 'sempre há cards cobrindo toda a largura da janela');

  // avanço automático e pausa
  await page.goto(B + '/?posts=8'); await page.mouse.move(5, 5);
  await page.waitForTimeout(5100);
  b = await car(page, '.rac-carrossel--posts');
  ok(b.pos === '1', `avanço automático: depois de ~4,5 s passou para o 2º post (pos ${b.pos})`);
  await page.hover('.rac-carrossel--posts .rac-carrossel__janela'); const antesH = (await car(page, '.rac-carrossel--posts')).pos;
  await page.waitForTimeout(5400);
  ok((await car(page, '.rac-carrossel--posts')).pos === antesH, 'com o mouse em cima, o avanço automático pausa');
  await page.mouse.move(5, 5); await page.waitForTimeout(5100);
  ok((await car(page, '.rac-carrossel--posts')).pos !== antesH, 'ao tirar o mouse, o avanço volta');
  // foco por teclado também pausa
  await page.focus('.rac-carrossel--posts [data-rac-next]'); const antesF = (await car(page, '.rac-carrossel--posts')).pos;
  await page.waitForTimeout(5400);
  ok((await car(page, '.rac-carrossel--posts')).pos === antesF, 'com o foco nas setas (teclado), o avanço automático pausa');
  await page.evaluate(() => document.activeElement.blur());

  // navegação persistente: sem temporizador duplicado ao voltar para a Home
  await page.goto(B + '/?posts=8'); await page.evaluate(() => { window.__marcador = 'mesma-pagina'; });
  await page.click('.rac-botao-claro'); await esperar(page, () => location.pathname === '/estacoes/');
  await page.goBack(); await esperar(page, () => location.pathname === '/'); await page.mouse.move(5, 5);
  await page.waitForTimeout(300);
  const volta = await car(page, '.rac-carrossel--posts');
  ok(volta.inf && volta.pos === '0' && await page.evaluate(() => window.__marcador === 'mesma-pagina'), 'ao voltar para a Home por navegação persistente o carrossel é remontado do zero');
  await page.waitForTimeout(5100);
  ok((await car(page, '.rac-carrossel--posts')).pos === '1', 'e avança de 1 em 1 (sem temporizadores duplicados da visita anterior)');

  // mobile: rolagem lateral, sem clones
  await page.setViewportSize({ width: 375, height: 800 }); await page.waitForTimeout(500);
  const bm = await car(page, '.rac-carrossel--posts'); const lm = await car(page, '.rac-carrossel--livros');
  ok(!bm.inf && bm.clones === 0 && bm.setas === 'none' && bm.overflowX === 'auto' && bm.snap.includes('x') && bm.larg === 250 && bm.esq >= 16 && bm.esq <= 20, `mobile (blog): rolagem lateral com encaixe, sem clones nem setas, cards de ${bm.larg}px (margem ${bm.esq}px)`);
  ok(!lm.inf && lm.clones === 0 && lm.larg === 120 && lm.setas === 'none', `mobile (literatura): cards de ${lm.larg}px, sem clones nem setas`);
  ok(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), 'mobile: a página não rola na horizontal');
  await page.screenshot({ path: 'capturas/f3b2-home-m-faixas.png', fullPage: true });
  await page.setViewportSize({ width: 1280, height: 900 }); await page.waitForTimeout(600);
  ok((await car(page, '.rac-carrossel--posts')).inf, 'voltando ao desktop, o carrossel infinito é remontado');

  // movimento reduzido
  const ctxR = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion: 'reduce' });
  const pr = await ctxR.newPage(); await pr.goto(B + '/?posts=8');
  await pr.waitForTimeout(5200);
  ok((await car(pr, '.rac-carrossel--posts')).pos === '0', '"reduzir movimento": sem avanço automático');
  await pr.click('.rac-carrossel--posts [data-rac-next]');
  const rr = await car(pr, '.rac-carrossel--posts');
  ok(rr.pos === '1' && rr.indice >= 6 && rr.indice < 12, 'e as setas funcionam, sem animação');
  await ctxR.close();

  /* ---------- Variações de livros ---------- */
  await page.goto(B + '/?livros=4');
  const l4 = await car(page, '.rac-carrossel--livros');
  ok(!l4.inf && l4.n === 4 && l4.clones === 0 && l4.setas === 'none', 'poucos livros (4): grade simples, sem setas');
  await page.goto(B + '/?livros=0');
  ok(await page.evaluate(() => !document.querySelector('.rac-carrossel--livros')), 'sem livros: a seção Literatura some');

  /* ---------- Podcasts ---------- */
  await page.goto(B + '/?podcasts=1');
  const pod = await page.evaluate(() => { const cs = [...document.querySelectorAll('.rac-podcast')]; const sobre = document.querySelector('.rac-home-sobre'); const sec = document.querySelector('.rac-podcasts'); return { n: cs.length, titulos: cs.map(c => c.querySelector('.rac-podcast__titulo').textContent), desc: cs.map(c => c.querySelector('.rac-podcast__desc')?.textContent || null), eps: cs.map(c => c.querySelector('.rac-podcast__eps').textContent), href: cs[0].querySelector('.rac-podcast__titulo').getAttribute('href'), ir: cs[0].querySelector('.rac-podcast__ir').getAttribute('href'), cols: getComputedStyle(document.querySelector('.rac-podcasts__grade')).gridTemplateColumns.split(' ').length, depoisSobre: !!(sobre.compareDocumentPosition(sec) & Node.DOCUMENT_POSITION_FOLLOWING), h2: sec.querySelector('h2').textContent }; });
  ok(pod.n === 2 && pod.titulos[0] === 'Bastidores da Cena' && pod.desc[0] === 'Entrevistas com artistas e curadores locais.' && pod.desc[1] === null, 'podcasts: duas séries (a sem descrição não mostra a linha)');
  ok(pod.eps[0] === '32 episódios' && pod.eps[1] === '1 episódio' && pod.href === pod.ir && pod.href.includes('/category/podcasts/'), 'podcasts: "32 episódios" / "1 episódio" e botão de play levando à série');
  ok(pod.cols === 2 && pod.depoisSobre && pod.h2 === 'Podcasts', 'podcasts: 2 colunas, depois do "Sobre nós"');

  /* ---------- Recados (desktop) ---------- */
  await page.goto(B + '/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  const rec = () => page.evaluate(() => ({
    visiveis: [...document.querySelectorAll('.rac-recado')].filter(e => !e.hidden).map(e => e.querySelector('.rac-recado__nome').textContent),
    datas: [...document.querySelectorAll('.rac-recado')].filter(e => !e.hidden).map(e => e.querySelector('.rac-recado__data').textContent),
    nums: [...document.querySelectorAll('.rac-pag--d [data-rac-pag-ir]')].map(b => b.textContent), ativa: document.querySelector('.rac-pag--d .is-ativa')?.textContent || null,
    rotuloM: document.querySelector('[data-rac-pag-rotulo]')?.textContent, navOculta: document.querySelector('[data-rac-paginas]').hidden,
  }));
  let r = await rec();
  ok(r.visiveis.length === 3 && r.visiveis[0] === 'Ana Paula' && r.visiveis[2] === 'Juliana M.', `recados: 3 por página no desktop (${r.visiveis.join(', ')})`);
  ok(r.datas[0] === 'há 2 horas' && r.datas[1] === 'há 1 dia', `datas relativas (${r.datas.join(' / ')})`);
  ok(JSON.stringify(r.nums) === '["1","2","3"]' && r.ativa === '1' && !r.navOculta, 'paginação numerada: 3 páginas, a 1 ativa');
  const cab = await page.evaluate(() => ({ d: getComputedStyle(document.querySelector('.rac-recados__contagem--d')).display, m: getComputedStyle(document.querySelector('.rac-recados__contagem--m')).display, td: document.querySelector('.rac-recados__contagem--d').textContent, h2: document.querySelector('.rac-recados h2').textContent, cols: getComputedStyle(document.querySelector('.rac-recados__inner')).gridTemplateColumns.split(' ').length, col1: Math.round(document.querySelector('.rac-recados__lado').getBoundingClientRect().width), clamp: getComputedStyle(document.querySelector('.rac-recado__texto')).webkitLineClamp, av: document.querySelector('.rac-recado__avatar').textContent, texto: document.querySelector('.rac-recados__texto').textContent, contato: document.querySelector('.rac-recados__texto a').getAttribute('href') }));
  ok(cab.d !== 'none' && cab.m === 'none' && cab.td === '7 recados' && cab.cols === 2 && cab.col1 === 320 && cab.clamp === '2' && cab.av === 'A', `recados desktop: "7 recados", 2 colunas (${cab.col1}px + lista), texto em até 2 linhas, avatar com a inicial`);
  ok(cab.texto.includes('Quer pedir uma música?') && cab.contato.endsWith('/contato/'), 'texto de apoio manda os pedidos musicais para o Contato');
  await page.click('.rac-pag--d [data-rac-pag-ir="1"]'); r = await rec();
  ok(r.visiveis.join() === 'Carlos Eduardo,Dona Marta,élio Ramos' && r.ativa === '2' && /^\d{2} [a-z]{3} \d{4}$/.test(r.datas[0]), `página 2 (${r.visiveis.join(', ')}); recado antigo mostra a data`);
  await page.click('.rac-pag--d [data-rac-pag-next]'); await page.click('.rac-pag--d [data-rac-pag-next]'); r = await rec();
  ok(r.ativa === '1', 'depois da última página, "›" volta para a primeira');
  await page.click('.rac-pag--d [data-rac-pag-prev]'); r = await rec();
  ok(r.ativa === '3' && r.visiveis.length === 1 && r.visiveis[0] === 'Beatriz', 'na primeira página, "‹" vai para a última (1 recado)');
  await page.click('.rac-pag--d [data-rac-pag-next]');

  /* ---------- Recados: formulário ---------- */
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  ok(await page.isVisible('[data-rac-abrir]') && await page.isHidden('[data-rac-form]') && await page.isHidden('[data-rac-ok]'), 'formulário começa fechado, com o botão "Deixe seu recado"');
  await page.click('[data-rac-abrir]');
  ok(await page.isVisible('[data-rac-form]') && await page.isHidden('[data-rac-abrir]') && await page.evaluate(() => document.activeElement.name === 'author'), 'abrir mostra o formulário e leva o foco ao nome');
  const armadilha = await page.evaluate(() => { const i = document.querySelector('input[name="rac_site"]'); const r = i.getBoundingClientRect(); return { fora: r.right < 0 || r.width <= 1, tab: i.tabIndex, ac: i.autocomplete }; });
  ok(armadilha.fora && armadilha.tab === -1 && armadilha.ac === 'off', 'campo-armadilha fora da tela, fora da ordem do teclado');
  const camposOk = await page.evaluate(() => { const f = document.querySelector('[data-rac-form]'); const bg = e => getComputedStyle(f.elements[e]); return { req: ['author', 'email', 'comment'].every(n => f.elements[n].required), post: f.elements['comment_post_ID'].value, pai: f.elements['comment_parent'].value, acao: f.action.endsWith('/wp-comments-post.php'), borda: bg('author').borderTopWidth, ap: bg('author').appearance, pad: bg('author').paddingLeft }; });
  ok(camposOk.req && camposOk.post === '1000' && camposOk.pai === '0' && camposOk.acao && camposOk.borda === '1px' && camposOk.pad === '14px', 'campos obrigatórios, ligados à página inicial e com o visual do mockup (sem o estilo de campo do tema pai)');
  await page.click('[data-rac-cancelar]');
  ok(await page.isVisible('[data-rac-abrir]') && await page.isHidden('[data-rac-form]'), '"Cancelar" fecha o formulário');

  // envio rápido demais (< 3 s) é recusado
  await page.click('[data-rac-abrir]');
  await page.fill('input[name="author"]', 'Teste Rápido'); await page.fill('input[name="email"]', 'a@b.com'); await page.fill('textarea[name="comment"]', 'Mensagem depressa');
  await page.click('[data-rac-enviar]');
  ok(await esperar(page, () => !document.querySelector('[data-rac-erro]').hidden), 'envio em menos de 3 s: o erro aparece');
  let erroTxt = await page.textContent('[data-rac-erro]');
  ok(erroTxt.startsWith('Calma!') && await page.isVisible('[data-rac-form]') && await page.inputValue('textarea[name="comment"]') === 'Mensagem depressa', `erro do servidor mostrado e o texto digitado é mantido ("${erroTxt}")`);
  // armadilha preenchida (robô que usa o navegador)
  await page.waitForTimeout(3300);
  await page.evaluate(() => { document.querySelector('input[name="rac_site"]').value = 'http://spam.com'; });
  await page.click('[data-rac-enviar]');
  await esperar(page, () => document.querySelector('[data-rac-erro]').textContent.includes('Não foi possível'));
  ok((await page.textContent('[data-rac-erro]')).startsWith('Não foi possível enviar o recado'), 'campo-armadilha preenchido: o envio é recusado');
  await page.evaluate(() => { document.querySelector('input[name="rac_site"]').value = ''; });
  // espaços em branco: o WordPress recusa
  await page.fill('input[name="author"]', '   ');
  await page.click('[data-rac-enviar]');
  await esperar(page, () => document.querySelector('[data-rac-erro]').textContent.includes('obrigatórios'));
  ok((await page.textContent('[data-rac-erro]')).startsWith('Erro: preencha os campos obrigatórios'), 'campo só com espaços: mensagem de erro do WordPress mostrada');
  // duplicado
  await page.fill('input[name="author"]', 'Teste'); await page.fill('textarea[name="comment"]', 'DUPLICADO');
  await page.click('[data-rac-enviar]');
  await esperar(page, () => document.querySelector('[data-rac-erro]').textContent.includes('duplicado'));
  ok((await page.textContent('[data-rac-erro]')).includes('duplicado'), 'comentário duplicado: mensagem do WordPress mostrada');
  // sucesso
  if (fs.existsSync('ultimo-recado.json')) fs.unlinkSync('ultimo-recado.json');
  await page.fill('input[name="author"]', 'Maria das Dores'); await page.fill('input[name="email"]', 'maria@exemplo.com'); await page.fill('textarea[name="comment"]', 'Que rádio linda, obrigada por existir!');
  const botaoTxt = page.click('[data-rac-enviar]');
  await botaoTxt;
  ok(await esperar(page, () => document.querySelector('[data-rac-ok]') && !document.querySelector('[data-rac-ok]').hidden), 'envio correto: aparece "Obrigado! Seu recado aparece após a moderação."');
  const fim = await page.evaluate(() => ({ txt: document.querySelector('[data-rac-ok]').textContent, form: document.querySelector('[data-rac-form]').hidden, abrir: document.querySelector('[data-rac-abrir]').hidden, m: window.__marcador, p: window.__audio.paused, vazio: document.querySelector('textarea[name="comment"]').value === '' }));
  ok(fim.txt === 'Obrigado! Seu recado aparece após a moderação.' && fim.form && fim.abrir && fim.vazio, 'depois do envio: mensagem, formulário fechado e campos limpos');
  ok(fim.m === 'mesma-pagina' && !fim.p, 'o envio NÃO recarrega a página e o áudio continua tocando');
  const salvo = JSON.parse(fs.readFileSync('ultimo-recado.json', 'utf8'));
  ok(salvo.author === 'Maria das Dores' && salvo.email === 'maria@exemplo.com' && salvo.comment_post_ID === '1000' && salvo.comment_parent === '0' && parseInt(salvo.rac_ms, 10) >= 3000 && salvo.rac_site === '', `o servidor recebeu os campos certos (rac_ms=${salvo.rac_ms}ms)`);
  await page.screenshot({ path: 'capturas/f3b2-recados-d.png', clip: { x: 0, y: (await page.evaluate(() => Math.round(document.querySelector('.rac-recados').getBoundingClientRect().top + window.scrollY))) - 10, width: 1280, height: 520 }, fullPage: true });

  /* ---------- Recados: sem comentários / fechados ---------- */
  await page.goto(B + '/?zero=1');
  const z = await page.evaluate(() => ({ lista: !!document.querySelector('.rac-recado'), msg: document.querySelector('.rac-recados__contagem--d').textContent, abrir: !!document.querySelector('[data-rac-abrir]'), nav: !!document.querySelector('[data-rac-paginas]') }));
  ok(!z.lista && z.msg === 'Seja o primeiro a deixar um recado.' && z.abrir && !z.nav, 'sem recados: convite "Seja o primeiro…" e o formulário continua disponível');
  await page.goto(B + '/?semcom=1');
  const sc = await page.evaluate(() => ({ lista: document.querySelectorAll('.rac-recado').length, form: !!document.querySelector('[data-rac-form]') }));
  ok(sc.lista === 7 && !sc.form, 'comentários fechados na Home: os recados aparecem, mas sem formulário');
  await page.goto(B + '/?zero=1&semcom=1');
  ok(await page.evaluate(() => !document.querySelector('.rac-recados')), 'sem recados e com comentários fechados: a seção some');

  /* ---------- Recados (mobile) ---------- */
  const ctxM = await browser.newContext({ viewport: { width: 375, height: 800 }, deviceScaleFactor: 2 });
  const m = await ctxM.newPage(); m.on('pageerror', e => errosJs.push(e.message));
  await m.goto(B + '/');
  const mr = await m.evaluate(() => { const q = s => document.querySelector(s); const rt = s => q(s).getBoundingClientRect(); const vis = [...document.querySelectorAll('.rac-recado')].filter(e => !e.hidden);
    return { n: vis.length, rotulo: q('[data-rac-pag-rotulo]').textContent, contM: getComputedStyle(q('.rac-recados__contagem--m')).display, textoApoio: getComputedStyle(q('.rac-recados__texto')).display, pagD: getComputedStyle(q('.rac-pag--d')).display, pagM: getComputedStyle(q('.rac-pag--m')).display,
      ordem: [rt('.rac-recados__cab').top, rt('.rac-recados__lista').top, rt('.rac-pag--m').top, rt('.rac-recados__abrir').top], largBotao: Math.round(rt('.rac-recados__abrir').width), clamp: getComputedStyle(q('.rac-recado__texto')).webkitLineClamp, overflow: document.documentElement.scrollWidth > window.innerWidth }; });
  ok(mr.n === 4 && mr.rotulo === '1 de 2' && mr.contM !== 'none' && mr.textoApoio === 'none' && mr.pagD === 'none' && mr.pagM === 'flex', `recados mobile: 4 por página, rótulo "${mr.rotulo}", contagem ao lado do título, paginação "‹ Anteriores / Próximos ›"`);
  ok(mr.ordem[0] < mr.ordem[1] && mr.ordem[1] < mr.ordem[2] && mr.ordem[2] < mr.ordem[3] && mr.largBotao >= 330 && mr.clamp === '3' && !mr.overflow, 'recados mobile: título → lista → paginação → botão em largura total; texto em até 3 linhas; sem rolagem horizontal');
  await m.click('.rac-pag--m [data-rac-pag-next]');
  ok(await m.evaluate(() => [...document.querySelectorAll('.rac-recado')].filter(e => !e.hidden).length === 3 && document.querySelector('[data-rac-pag-rotulo]').textContent === '2 de 2'), 'mobile: "Próximos ›" mostra os 3 últimos ("2 de 2")');
  await m.click('.rac-pag--m [data-rac-pag-next]');
  ok(await m.evaluate(() => document.querySelector('[data-rac-pag-rotulo]').textContent === '1 de 2'), 'mobile: dá a volta para "1 de 2"');
  await m.click('[data-rac-abrir]');
  ok(await m.isVisible('[data-rac-form]') && await m.isHidden('[data-rac-abrir]'), 'mobile: o botão abre o formulário embaixo da lista');
  await m.screenshot({ path: 'capturas/f3b2-recados-m.png', fullPage: true });
  // redimensionar do mobile para o desktop recalcula as páginas
  await m.setViewportSize({ width: 1280, height: 800 }); await m.waitForTimeout(300);
  ok(await m.evaluate(() => [...document.querySelectorAll('.rac-recado')].filter(e => !e.hidden).length === 3), 'ao alargar a janela, a paginação passa a 3 por página');

  /* ---------- Página da estação: Da biblioteca ---------- */
  await page.goto(B + '/estacao/100/');
  const bi = await page.evaluate(() => { const it = [...document.querySelectorAll('.rac-biblioteca__item')]; const s = document.querySelector('.rac-biblioteca'); return { titulos: it.map(i => i.querySelector('.rac-livro__titulo').textContent), n: it.length, h2: s.querySelector('h2').textContent, eyebrow: s.querySelector('.rac-eyebrow').textContent, link: s.querySelector('.rac-secao__topo a').getAttribute('href'), cols: getComputedStyle(s.querySelector('.rac-biblioteca__lista')).gridTemplateColumns.split(' ').length, depois: !!(document.querySelector('.rac-ler').compareDocumentPosition(s) & Node.DOCUMENT_POSITION_FOLLOWING) }; });
  ok(bi.n === 6 && bi.titulos[0].startsWith('Cadernos de Improviso') && bi.titulos[1] === 'Crônicas de Rádio', `"Da biblioteca" (Blues & Jazz Clube): os livros da categoria de mesmo nome primeiro (${bi.titulos.slice(0, 3).join(' | ')})`);
  ok(bi.h2 === 'Da biblioteca' && bi.eyebrow === 'Literatura' && bi.link.endsWith('/literatura/') && bi.cols === 6 && bi.depois, 'seção depois de "Para ler enquanto ouve", 6 colunas, link "Ver a biblioteca →"');
  await page.goto(B + '/estacao/105/');
  ok(await page.evaluate(() => [...document.querySelectorAll('.rac-biblioteca__item .rac-livro__titulo')].map(t => t.textContent)[0] === 'Contos da Cidade Baixa' && document.querySelectorAll('.rac-biblioteca__item').length === 6), 'estação sem categoria de livro: os 6 mais recentes');
  await page.goto(B + '/estacao/100/?livros=0');
  ok(await page.evaluate(() => !document.querySelector('.rac-biblioteca')), 'sem livros: a seção "Da biblioteca" some');
  await m.setViewportSize({ width: 375, height: 800 });
  await m.goto(B + '/estacao/100/');
  const bim = await m.evaluate(() => { const l = document.querySelector('.rac-biblioteca__lista'); const it = l.querySelector('.rac-biblioteca__item'); return { rola: l.scrollWidth > l.clientWidth + 10, larg: Math.round(it.getBoundingClientRect().width), esq: Math.round(it.getBoundingClientRect().left), overflow: document.documentElement.scrollWidth > window.innerWidth }; });
  ok(bim.rola && bim.larg === 120 && bim.esq >= 16 && bim.esq <= 20 && !bim.overflow, `mobile: "Da biblioteca" rola na lateral (cards de ${bim.larg}px, margem ${bim.esq}px)`);

  /* ---------- Tema escuro ---------- */
  const cx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const pg = await cx.newPage(); await pg.addInitScript(() => localStorage.setItem('rac-tema', 'dark'));
  await pg.goto(B + '/?posts=8&podcasts=1'); await pg.waitForTimeout(600);
  await pg.screenshot({ path: 'capturas/f3b2-home-escuro.png', fullPage: true });

  ok(errosJs.length === 0, 'sem erros de JavaScript (' + errosJs.join(' | ') + ')');
  out('\nFALHAS:', falhas);
  await browser.close();
  process.exit(falhas ? 1 : 0);
})();
