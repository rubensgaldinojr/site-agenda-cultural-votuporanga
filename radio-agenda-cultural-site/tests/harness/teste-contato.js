// Teste de navegador da página Contato (Fase 5B). Rotas /f4/contato/ + endpoint admin-ajax (rac_contato).
const { chromium } = require('playwright');
const fs = require('fs');
const B = 'http://localhost:8099';
const out = (...a) => console.log(...a);
let falhas = 0;
const ok = (c, msg) => { out((c ? 'OK   ' : 'FALHA') + ' ' + msg); if (!c) falhas++; };
const esperar = (page, fn, arg, ms = 8000) => page.waitForFunction(fn, arg, { timeout: ms }).then(() => true).catch(() => false);
const txt = (page, sel) => page.evaluate((s) => { const e = document.querySelector(s); return e ? e.textContent.replace(/\s+/g, ' ').trim() : null; }, sel);
const cnt = (page, sel) => page.evaluate((s) => document.querySelectorAll(s).length, sel);
const vis = (page, sel) => page.evaluate((s) => { const e = document.querySelector(s); if (!e) return false; const r = e.getBoundingClientRect(); return !e.hidden && r.width > 0 && r.height > 0 && getComputedStyle(e).display !== 'none'; }, sel);
const ler = (n) => { try { return JSON.parse(fs.readFileSync(n, 'utf8')); } catch (e) { return null; } };
const limpar = () => { for (const f of ['contato-msgs.json', 'contato-emails.json', 'contato-transients.json', 'contato-falha-email.flag']) { try { fs.unlinkSync(f); } catch (e) {} } };
const preencher = async (page, d) => {
  if (d.assunto) await page.click(`.rac-contato__chip:has-text("${d.assunto}")`);
  await page.fill('[name="nome"]', d.nome || 'Ana Paula'); await page.fill('[name="email"]', d.email || 'ana@exemplo.com');
  if (d.musica !== undefined && await vis(page, '[data-rac-contato-musica]')) { await page.fill('[name="musica"]', d.musica); await page.fill('[name="artista"]', d.artista || ''); }
  await page.fill('[name="mensagem"]', d.mensagem || 'Adoro a rádio, parabéns pelo trabalho!');
};

(async () => {
  limpar();
  const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });
  const errosJs = [];
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await ctx.newPage();
  page.on('pageerror', e => errosJs.push(e.message));
  page.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource/.test(m.text())) errosJs.push(m.text()); });

  /* ================= PÁGINA ================= */
  await page.goto(B + '/f4/contato/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  ok((await txt(page, '.rac-contato__info .rac-eyebrow')) === 'Contato' && (await txt(page, '.rac-contato__info h1')) === 'Fale com a gente', 'topo: "Contato" e título "Fale com a gente" (provisório do mockup)');
  ok((await txt(page, '.rac-contato__texto')).startsWith('Quer sugerir uma música, pedir aquela faixa especial'), 'texto de apresentação provisório');
  ok((await cnt(page, '.rac-contato__linha a[href^="mailto"]')) === 0 && (await txt(page, '.rac-contato__linha strong')) === 'Votuporanga, SP — Brasil' && (await cnt(page, '.rac-contato__linha--redes')) === 0, 'sem e-mail público e sem redes preenchidos: só "Onde estamos" aparece (nada de e-mail inventado)');
  const lay = await page.evaluate(() => { const c = document.querySelector('.rac-contato__cartao'); const cs = getComputedStyle(c); return { cols: getComputedStyle(document.querySelector('.rac-contato')).gridTemplateColumns.split(' ').length, larg: Math.round(c.getBoundingClientRect().width), raio: cs.borderRadius, pad: cs.paddingTop, h1: getComputedStyle(document.querySelector('.rac-contato__info h1')).fontSize }; });
  ok(lay.cols === 2 && lay.larg === 600 && lay.raio === '18px' && lay.pad === '32px' && lay.h1 === '48px', `desktop: texto à esquerda e cartão de ${lay.larg}px à direita (cantos de 18px, título de 48px)`);
  const ch = await page.evaluate(() => [...document.querySelectorAll('.rac-contato__chip')].map(c => ({ t: c.textContent.trim(), marcado: c.querySelector('input').checked })));
  ok(ch.map(c => c.t).join() === 'Sugestão de música,Pedido musical,Parcerias,Outro' && ch[0].marcado && ch.filter(c => c.marcado).length === 1, 'assuntos em botões: Sugestão de música (marcado), Pedido musical, Parcerias e Outro');
  const cor = await page.evaluate(() => { const s = document.querySelector('.rac-contato__chip input:checked + span'); const n = document.querySelectorAll('.rac-contato__chip span')[2]; return [getComputedStyle(s).backgroundColor, getComputedStyle(s).color, getComputedStyle(n).backgroundColor]; });
  ok(cor[0] === 'rgb(31, 27, 23)' && cor[1] === 'rgb(251, 248, 243)' && cor[2] === 'rgb(255, 255, 255)', 'o assunto marcado fica escuro com letra clara; os outros ficam brancos');
  ok(await vis(page, '[data-rac-contato-musica]') && await page.evaluate(() => document.querySelector('[name="musica"]').required) && /Conte por que essa música/.test(await page.getAttribute('[name="mensagem"]', 'placeholder')), 'Sugestão de música: aparecem Música (obrigatória) e Artista, e o texto de ajuda da mensagem muda');
  await page.click('.rac-contato__chip:has-text("Parcerias")');
  ok(!(await vis(page, '[data-rac-contato-musica]')) && !(await page.evaluate(() => document.querySelector('[name="musica"]').required)) && (await page.getAttribute('[name="mensagem"]', 'placeholder')) === 'Escreva sua mensagem…', 'Parcerias: Música e Artista somem e o texto de ajuda volta ao padrão');
  await page.click('.rac-contato__chip:has-text("Pedido musical")');
  ok(await vis(page, '[data-rac-contato-musica]'), 'Pedido musical: Música e Artista voltam');
  await page.goto(B + '/f4/contato/?assunto=pedido-musical');
  ok(await page.evaluate(() => document.querySelector('.rac-contato__chip input:checked').value === 'pedido-musical') && await vis(page, '[data-rac-contato-musica]'), '?assunto=pedido-musical já deixa o assunto marcado (e os campos de música abertos)');
  await page.goto(B + '/f4/contato/?assunto=parcerias');
  ok(await page.evaluate(() => document.querySelector('.rac-contato__chip input:checked').value === 'parcerias') && !(await vis(page, '[data-rac-contato-musica]')), '?assunto=parcerias: marcado e sem campos de música');
  await page.goto(B + '/f4/contato/?assunto=lixo');
  ok(await page.evaluate(() => document.querySelector('.rac-contato__chip input:checked').value === 'sugestao-de-musica'), 'assunto inválido na URL volta ao padrão');
  ok(await page.evaluate(() => { const f = document.querySelector('[data-rac-contato-form]'); const i = f.elements.rac_site; return f.elements.nome.required && f.elements.email.required && f.elements.mensagem.required && i.tabIndex === -1 && f.getAttribute('action').endsWith('/wp-admin/admin-ajax.php') && f.elements.action.value === 'rac_contato'; }), 'campos obrigatórios, campo-armadilha fora do teclado e envio para o admin-ajax');
  await page.goto(B + '/f4/contato/?textos=1');
  ok((await txt(page, '.rac-contato__info h1')) === 'Título editado' && (await txt(page, '.rac-contato__texto')) === 'Texto editado.' && (await txt(page, '.rac-contato__linha:nth-child(2) strong')) === 'Rua Seis, 100 — Votuporanga', 'textos editados no painel substituem os provisórios');
  const lk = await page.evaluate(() => ({ mail: document.querySelector('.rac-contato__linha a').getAttribute('href'), redes: [...document.querySelectorAll('.rac-contato__redes a')].map(a => a.textContent + '|' + a.getAttribute('href') + '|' + a.target + '|' + a.rel) }));
  ok(lk.mail === 'mailto:oi@exemplo.com' && lk.redes.join() === 'IG|https://instagram.com/radioagenda|_blank|noopener,FB|https://facebook.com/radioagenda|_blank|noopener,YT|https://youtube.com/@radioagenda|_blank|noopener', 'e-mail público (mailto) e as 3 redes (IG, FB, YT) abrem em nova aba');
  await page.screenshot({ path: 'capturas/contato-d.png', fullPage: true });

  /* ================= ENVIO ================= */
  await page.goto(B + '/f4/contato/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  await preencher(page, { musica: 'Save My Child', artista: 'Willie Dixon', mensagem: 'Texto que não pode se perder' });
  await page.click('[data-rac-contato-enviar]');
  ok(await esperar(page, () => !document.querySelector('[data-rac-contato-erro]').hidden), 'envio em menos de 3 s: o erro aparece');
  ok(/Calma/.test(await txt(page, '[data-rac-contato-erro]')) && (await page.inputValue('[name="mensagem"]')) === 'Texto que não pode se perder' && (await page.inputValue('[name="nome"]')) === 'Ana Paula' && !(await page.isDisabled('[data-rac-contato-enviar]')), 'a mensagem do servidor aparece, nada do que foi digitado se perde e o botão volta a funcionar');
  ok(ler('contato-msgs.json') === null, 'nada é salvo quando o envio é recusado');
  await page.evaluate(() => { document.querySelector('[name="rac_site"]').value = 'robo'; });
  await page.waitForTimeout(3100);
  await page.click('[data-rac-contato-enviar]');
  ok(await esperar(page, () => /Não foi possível enviar a mensagem/.test(document.querySelector('[data-rac-contato-erro]').textContent)), 'campo-armadilha preenchido: envio recusado');
  await page.evaluate(() => { document.querySelector('[name="rac_site"]').value = ''; const f = document.querySelector('[data-rac-contato-form]'); f.noValidate = true; f.elements.email.type = 'text'; f.elements.email.value = 'isso-nao-e-email'; });
  await page.click('[data-rac-contato-enviar]');
  ok(await esperar(page, () => /e-mail válido/.test(document.querySelector('[data-rac-contato-erro]').textContent)), 'e-mail inválido: o servidor também confere e avisa');
  await page.fill('[name="email"]', 'ana@exemplo.com'); await page.fill('[name="musica"]', '');
  await page.evaluate(() => { document.querySelector('[name="musica"]').required = false; });
  await page.click('[data-rac-contato-enviar]');
  ok(await esperar(page, () => /qual música/.test(document.querySelector('[data-rac-contato-erro]').textContent)), 'Sugestão/Pedido sem a música: o servidor pede a música');
  await page.fill('[name="musica"]', 'Save My Child');
  await page.click('[data-rac-contato-enviar]');
  ok(await esperar(page, () => !document.querySelector('[data-rac-contato-enviado]').hidden), 'envio correto: aparece a tela "Mensagem enviada"');
  ok((await txt(page, '[data-rac-contato-enviado] h2')) === 'Mensagem enviada' && (await txt(page, '[data-rac-contato-enviado] p')) === 'Obrigado por escrever! Respondemos pelo e-mail que você informou.' && !(await vis(page, '[data-rac-contato-form]')) && await vis(page, '[data-rac-contato-outra]'), 'tela de confirmação com o texto do mockup e o formulário escondido');
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && !window.__audio.paused && location.pathname === '/f4/contato/'), 'o envio NÃO recarregou a página e o áudio continua tocando');
  const msgs = ler('contato-msgs.json'); const m = msgs && Object.values(msgs)[0];
  ok(msgs && Object.keys(msgs).length === 1 && m.titulo === 'Sugestão de música — Ana Paula' && m.conteudo === 'Texto que não pode se perder' && m.tipo === 'mensagem_contato' && m.meta.contato_email === 'ana@exemplo.com' && m.meta.contato_musica === 'Save My Child' && m.meta.contato_artista === 'Willie Dixon' && m.meta.contato_email_enviado === '1', 'a mensagem ficou SALVA no painel (assunto, nome, e-mail, música, artista e "e-mail enviado")');
  const em = ler('contato-emails.json');
  ok(em && em.length === 1 && em[0].para === 'admin@site.test' && em[0].assunto === '[Contato do site] Sugestão de música — Ana Paula' && /Reply-To: Ana Paula <ana@exemplo.com>/.test(em[0].cabecalhos.join('\n')) && /Texto que não pode se perder/.test(em[0].corpo) && /Música: Save My Child/.test(em[0].corpo), 'o e-mail saiu para o destino (sem campo preenchido: e-mail do administrador), com "Responder" indo para quem escreveu');
  await page.screenshot({ path: 'capturas/contato-enviado-d.png', fullPage: true });
  await page.click('[data-rac-contato-outra]');
  ok(await vis(page, '[data-rac-contato-form]') && !(await vis(page, '[data-rac-contato-enviado]')) && (await page.inputValue('[name="mensagem"]')) === '' && (await page.inputValue('[name="nome"]')) === '', '"Enviar outra mensagem": volta o formulário limpo');
  await preencher(page, { musica: 'Outra faixa', mensagem: 'Segunda mensagem imediata' });
  await page.click('[data-rac-contato-enviar]');
  ok(await esperar(page, () => /Calma/.test(document.querySelector('[data-rac-contato-erro]').textContent)), 'o tempo mínimo de 3 s vale de novo para a segunda mensagem');
  // botão depois do clique: sem azul do Kadence
  const bt = await page.evaluate(() => { const b = document.querySelector('[data-rac-contato-enviar]'); b.focus(); const c = getComputedStyle(b); return { fundo: c.backgroundColor, sombra: c.boxShadow }; });
  ok(bt.sombra === 'none' && bt.fundo !== 'rgb(29, 74, 138)', 'botão "Enviar mensagem" focado: sem sombra nem fundo azul do Kadence');

  /* ---------- e-mail que falha: a mensagem não se perde ---------- */
  limpar(); fs.writeFileSync('contato-falha-email.flag', '1');
  await page.goto(B + '/f4/contato/');
  await page.click('.rac-contato__chip:has-text("Parcerias")');
  await preencher(page, { mensagem: 'Proposta de parceria para o festival' });
  await page.waitForTimeout(3100);
  await page.click('[data-rac-contato-enviar]');
  ok(await esperar(page, () => !document.querySelector('[data-rac-contato-enviado]').hidden), 'se o e-mail falhar, a pessoa ainda vê "Mensagem enviada" (a mensagem foi guardada)');
  const m2 = Object.values(ler('contato-msgs.json') || {})[0];
  ok(m2 && m2.titulo === 'Parcerias — Ana Paula' && m2.meta.contato_email_enviado === '0' && !m2.meta.contato_musica && ler('contato-emails.json') === null, 'e a mensagem fica salva marcada como "e-mail não saiu" (para você ver no painel)');
  fs.unlinkSync('contato-falha-email.flag');

  /* ---------- limite de envios ---------- */
  limpar();
  const respostas = await page.evaluate(async () => {
    const r = [];
    for (let i = 0; i < 6; i++) {
      const fd = new FormData(); fd.append('action', 'rac_contato'); fd.append('rac_ms', '9000'); fd.append('assunto', 'outro'); fd.append('nome', 'Teste ' + i); fd.append('email', 't@exemplo.com'); fd.append('mensagem', 'Mensagem de teste número ' + i);
      const j = await (await fetch('/wp-admin/admin-ajax.php', { method: 'POST', body: fd })).json(); r.push(j.success ? 'ok' : j.data.mensagem);
    }
    return r;
  });
  ok(respostas.slice(0, 5).every(x => x === 'ok') && /várias mensagens seguidas/.test(respostas[5]), `limite por visitante: as 5 primeiras passam e a 6ª na mesma hora é recusada (${respostas[5]})`);
  ok(Object.keys(ler('contato-msgs.json')).length === 5, 'a mensagem recusada pelo limite não é salva');
  limpar();

  /* ---------- navegação persistente ---------- */
  await page.goto(B + '/f4/blog/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); const a = document.createElement('a'); a.href = '/f4/contato/'; a.id = 'ir-contato'; a.textContent = 'Contato'; document.querySelector('#primary').prepend(a); });
  await page.click('#racr-botao-play'); await esperar(page, () => !window.__audio.paused);
  await page.click('#ir-contato');
  ok(await esperar(page, () => location.pathname === '/f4/contato/' && document.querySelector('[data-rac-contato-form]')), 'do Blog para o Contato pela navegação persistente');
  await page.click('.rac-contato__chip:has-text("Outro")');
  ok(!(await vis(page, '[data-rac-contato-musica]')) && await page.evaluate(() => window.__marcador === 'mesma-pagina' && !window.__audio.paused), 'depois da troca de página os botões de assunto funcionam (JS religado), sem recarregar e com o áudio tocando');
  await page.click('#racr-botao-play');

  /* ---------- tema escuro ---------- */
  await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'dark'));
  await page.waitForTimeout(500);
  const esc = await page.evaluate(() => ({ cartao: getComputedStyle(document.querySelector('.rac-contato__cartao')).backgroundColor, campo: getComputedStyle(document.querySelector('[name="nome"]')).backgroundColor, chip: getComputedStyle(document.querySelector('.rac-contato__chip input:checked + span')).backgroundColor, h1: getComputedStyle(document.querySelector('.rac-contato__info h1')).color }));
  ok(esc.cartao === 'rgb(33, 29, 25)' && esc.campo === 'rgb(23, 20, 17)' && esc.chip === 'rgb(243, 236, 227)' && esc.h1 === 'rgb(243, 236, 227)', 'tema escuro: cartão, campos, botão de assunto marcado e título usam os tokens do escuro');
  await page.screenshot({ path: 'capturas/contato-escuro.png', fullPage: true });
  await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'light'));

  /* ================= MOBILE ================= */
  await page.setViewportSize({ width: 390, height: 800 });
  await page.goto(B + '/f4/contato/?textos=1');
  const m1 = await page.evaluate(() => {
    const q = s => document.querySelector(s); const r = s => q(s).getBoundingClientRect(); const cs = (s, p) => getComputedStyle(q(s))[p];
    const chip = q('.rac-contato__chip span').getBoundingClientRect();
    return {
      colunas: cs('.rac-contato', 'flexDirection'), h1: cs('.rac-contato__info h1', 'fontSize'), cartaoEsq: Math.round(r('.rac-contato__cartao').left), cartaoL: Math.round(r('.rac-contato__cartao').width), raio: cs('.rac-contato__cartao', 'borderRadius'),
      infoL: Math.round(r('.rac-contato__info').width), linhaL: Math.round(r('.rac-contato__linha').width), chipAlt: Math.round(chip.height), dupla: cs('.rac-contato__dupla', 'gridTemplateColumns').split(' ').length, campoAlt: Math.round(r('[name="nome"]').height), musicaCol: cs('[data-rac-contato-musica]', 'gridTemplateColumns').split(' ').length,
      btn: [Math.round(r('[data-rac-contato-enviar]').width), Math.round(r('[data-rac-contato-enviar]').height)], notaDepois: r('.rac-contato__rodape > span').top > r('[data-rac-contato-enviar]').bottom - 1, overflow: document.documentElement.scrollWidth > window.innerWidth,
    };
  });
  ok(m1.colunas === 'column' && m1.h1 === '30px' && m1.cartaoEsq === 18 && m1.cartaoL === 354 && m1.raio === '16px', `mobile: texto em cima e cartão embaixo (margem de 18px, ${m1.cartaoL}px), título de 30px`);
  ok(m1.infoL === 390 && m1.linhaL === 354, `mobile: o bloco de texto e as linhas de contato ocupam a largura toda (${m1.infoL}px / ${m1.linhaL}px)`);
  ok(m1.chipAlt === 36 && m1.dupla === 1 && m1.campoAlt === 46 && m1.musicaCol === 1, 'mobile: botões de assunto de 36px, Nome e E-mail empilhados, campos de 46px, Música e Artista empilhados');
  ok(m1.btn[0] >= 316 && m1.btn[1] === 48 && m1.notaDepois && !m1.overflow, `mobile: "Enviar mensagem" em largura total (${m1.btn.join('x')}), a nota "Seu e-mail não será publicado…" embaixo e sem rolagem horizontal`);
  await page.screenshot({ path: 'capturas/contato-m.png', fullPage: true });
  await page.goto(B + '/f4/contato/');
  await preencher(page, { musica: 'Faixa', mensagem: 'Teste no celular' });
  await page.waitForTimeout(3100);
  await page.click('[data-rac-contato-enviar]');
  ok(await esperar(page, () => !document.querySelector('[data-rac-contato-enviado]').hidden) && await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), 'mobile: o envio funciona e a tela "Mensagem enviada" cabe na tela');
  await page.screenshot({ path: 'capturas/contato-enviado-m.png', fullPage: true });
  limpar();

  ok(errosJs.length === 0, 'sem erros de JavaScript (' + errosJs.join(' | ') + ')');
  out('\nFALHAS: ' + falhas);
  await browser.close();
  process.exit(falhas ? 1 : 0);
})();
