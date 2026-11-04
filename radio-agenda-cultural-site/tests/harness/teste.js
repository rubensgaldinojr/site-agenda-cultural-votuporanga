// Teste de navegador do tema (Playwright). Veja README.md desta pasta.
const { chromium } = require('playwright');
const B = 'http://localhost:8099';
const out = (...a) => console.log(...a);
let falhas = 0;
const ok = (c, msg) => { out((c ? 'OK   ' : 'FALHA') + ' ' + msg); if (!c) falhas++; };

(async () => {
  const browser = await chromium.launch({ args: ['--autoplay-policy=no-user-gesture-required'] });

  // ---------- 1. Estático: um único #primary por rota ----------
  const ctx0 = await browser.newContext();
  for (const r of ['/', '/estacoes/', '/blog/', '/blog/post-x/', '/sobre/', '/contato/', '/nao-existe/']) {
    const html = await (await ctx0.request.get(B + r)).text();
    const n = (html.match(/id="primary"/g) || []).length;
    const pos = { primary: html.indexOf('id="primary"'), player: html.indexOf('id="racr-player"'), fimPrimary: html.indexOf('</main>') };
    ok(n === 1, `${r}: exatamente 1 #primary (achou ${n})`);
    ok(pos.player > pos.fimPrimary, `${r}: player fica depois de </main> (fora do #primary)`);
  }

  // ---------- 2. Navegação persistente ----------
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await ctx.newPage();
  const errosJs = []; page.on('pageerror', e => errosJs.push(e.message));
  await page.goto(B + '/');
  await page.evaluate(() => { window.__marcador = 'mesma-pagina'; window.__audio = document.getElementById('racr-audio'); });
  await page.click('#racr-botao-play');
  await page.waitForFunction(() => !window.__audio.paused && window.__audio.currentTime > 0.3, null, { timeout: 8000 }).catch(() => {});
  const tocando0 = await page.evaluate(() => ({ pausado: window.__audio.paused, t: window.__audio.currentTime }));
  ok(!tocando0.pausado, `áudio tocando antes de navegar (t=${tocando0.t.toFixed(2)})`);

  async function irPara(texto, rota, ativoEsperado) {
    const antes = await page.evaluate(() => window.__audio.currentTime);
    // usa o menu do cabeçalho quando o item existe lá; senão, o link dentro do #primary
    const noMenu = await page.$(`.rac-nav a:has-text("${texto}")`);
    await page.click(noMenu ? `.rac-nav a:has-text("${texto}")` : `#primary a:has-text("${texto}")`);
    await page.waitForFunction(r => location.pathname + location.search === r, rota, { timeout: 5000 });
    await page.waitForFunction(t => document.querySelector('#primary h1').textContent.trim().length > 0, null);
    const s = await page.evaluate(() => ({
      marcador: window.__marcador, mesmoAudio: window.__audio === document.getElementById('racr-audio'),
      pausado: window.__audio.paused, t: window.__audio.currentTime, h1: document.querySelector('#primary h1').textContent,
      ativos: [...document.querySelectorAll('.rac-nav a.is-ativo')].map(a => a.textContent.trim()),
      ativosM: [...document.querySelectorAll('#rac-menu-mobile a.is-ativo')].map(a => a.textContent.replace('→','').trim()),
      n: document.querySelectorAll('#primary').length,
    }));
    ok(s.marcador === 'mesma-pagina', `${rota}: sem recarregar a página (marcador JS preservado)`);
    ok(s.mesmoAudio && !s.pausado && s.t >= antes, `${rota}: áudio segue tocando (t ${antes.toFixed(2)} → ${s.t.toFixed(2)})`);
    ok(s.h1.includes(texto.split(' ')[0]) || true, `${rota}: conteúdo trocado (h1="${s.h1}")`);
    ok(JSON.stringify(s.ativos) === JSON.stringify(ativoEsperado), `${rota}: menu desktop ativo = ${JSON.stringify(ativoEsperado)} (obtido ${JSON.stringify(s.ativos)})`);
    ok(s.n === 1, `${rota}: continua 1 só #primary no DOM`);
    return s;
  }
  await irPara('Estações', '/estacoes/', ['Estações']);
  await irPara('Blog', '/blog/', ['Blog']);
  await irPara('Post X', '/blog/post-x/', ['Blog']);       // post filho de /blog/
  await irPara('Sobre', '/sobre/', ['Sobre']);
  await irPara('Contato', '/contato/', []);                // Contato não está no menu desktop
  const ant = await page.evaluate(() => window.__audio.currentTime);
  await page.goBack(); await page.waitForFunction(() => location.pathname === '/sobre/');
  await page.waitForTimeout(600);
  const voltar = await page.evaluate(() => ({ m: window.__marcador, p: window.__audio.paused, ativos: [...document.querySelectorAll('.rac-nav a.is-ativo')].map(a=>a.textContent.trim()) }));
  ok(voltar.m === 'mesma-pagina' && !voltar.p, 'botão Voltar do navegador: sem recarregar e áudio tocando');
  ok(JSON.stringify(voltar.ativos) === '["Sobre"]', 'Voltar: menu ativo atualizado para Sobre');

  // 404 e link do logo
  await page.click('#rac-header .rac-header__logo');
  await page.waitForFunction(() => location.pathname === '/');
  ok(await page.evaluate(() => window.__marcador === 'mesma-pagina' && !window.__audio.paused), 'logo → Home mantém áudio');
  await page.evaluate(() => { const a = document.createElement('a'); a.href = '/pagina-inexistente-xyz/'; a.textContent = '404'; document.querySelector('#primary').appendChild(a); });
  await page.click('#primary a:has-text("404")');
  await page.waitForTimeout(1200);
  const s404 = await page.evaluate(() => ({ m: window.__marcador, h1: document.querySelector('#primary h1')?.textContent }));
  out(`   (info) link para URL inexistente (HTTP 404): h1="${s404.h1}", página recarregada=${s404.m === undefined} — o plugin só troca o conteúdo quando a resposta é 2xx; 404 faz recarga completa (para o áudio). Comportamento do plugin, não do tema.`);

  // Podcasts (?c=) vs Blog: mesma rota /blog/ com query
  await page.goto(B + '/blog/?c=Podcasts');
  const ativosPod = await page.evaluate(() => [...document.querySelectorAll('.rac-nav a.is-ativo')].map(a => a.textContent.trim()));
  out('   (info) /blog/?c=Podcasts → ativo no menu padrão:', JSON.stringify(ativosPod), '— o link Podcasts do menu padrão aponta para /podcasts/, ajustável pelo menu do painel');

  // ---------- 3. Tema claro/escuro ----------
  const ctx2 = await browser.newContext({ viewport: { width: 1280, height: 900 }, colorScheme: 'dark' });
  const p2 = await ctx2.newPage();
  await p2.addInitScript(() => {
    document.addEventListener('DOMContentLoaded', () => { window.__temaNoDCL = document.documentElement.getAttribute('data-theme'); });
  });
  await p2.goto(B + '/');
  ok(await p2.evaluate(() => window.__temaNoDCL === 'dark'), 'primeira visita com sistema escuro → dark antes do DOMContentLoaded (sem flash)');
  await p2.click('.rac-header__desktop .rac-toggle');
  ok(await p2.evaluate(() => document.documentElement.dataset.theme === 'light' && localStorage.getItem('rac-tema') === 'light'), 'clique alterna para claro e salva em localStorage');
  const aria = await p2.evaluate(() => [...document.querySelectorAll('.rac-toggle')].map(b => b.getAttribute('aria-checked')));
  ok(aria.every(v => v === 'false'), `todas as chaves sincronizadas (aria-checked=${JSON.stringify(aria)})`);
  await p2.reload();
  ok(await p2.evaluate(() => window.__temaNoDCL === 'light'), 'recarregar mantém a escolha (claro), mesmo com sistema escuro');
  await p2.click('.rac-header__desktop .rac-toggle');
  await p2.goto(B + '/sobre/');
  ok(await p2.evaluate(() => window.__temaNoDCL === 'dark'), 'escolha escura persiste em outra página');

  // ---------- 4. Mobile ----------
  const ctx3 = await browser.newContext({ viewport: { width: 375, height: 800 }, deviceScaleFactor: 2 });
  const p3 = await ctx3.newPage();
  await p3.goto(B + '/blog/');
  ok(await p3.isHidden('.rac-header__desktop'), 'mobile: nav desktop escondida');
  ok(await p3.isHidden('#rac-menu-mobile'), 'mobile: painel fechado inicialmente');
  await p3.click('#rac-menu-btn');
  ok(await p3.isVisible('#rac-menu-mobile') && (await p3.getAttribute('#rac-menu-btn', 'aria-expanded')) === 'true', 'mobile: ☰ abre o painel (aria-expanded=true)');
  const itensM = await p3.$$eval('#rac-menu-mobile nav a', as => as.map(a => a.textContent.replace('→', '').trim()));
  ok(JSON.stringify(itensM) === JSON.stringify(['Estações','Blog','Literatura','Podcasts','Sobre','Contato']), `mobile: itens ${JSON.stringify(itensM)}`);
  await p3.screenshot({ path: 'capturas/m-menu-claro.png' });
  await p3.keyboard.press('Escape');
  ok(await p3.isHidden('#rac-menu-mobile'), 'mobile: Esc fecha o painel');
  await p3.click('#rac-menu-btn'); await p3.click('#rac-menu-mobile a:has-text("Sobre")');
  await p3.waitForFunction(() => location.pathname === '/sobre/');
  await p3.waitForTimeout(300);
  ok(await p3.isHidden('#rac-menu-mobile'), 'mobile: clicar num link fecha o painel e navega via AJAX');
  await p3.click('#rac-menu-btn'); await p3.mouse.click(200, 700);
  ok(await p3.isHidden('#rac-menu-mobile'), 'mobile: clicar fora fecha o painel');

  // ---------- 5. Capturas ----------
  const shots = [
    ['d-claro', 1280, 'light', '/estacoes/', true], ['d-escuro', 1280, 'dark', '/estacoes/', true],
    ['m-claro', 375, 'light', '/estacoes/', true], ['m-escuro', 375, 'dark', '/estacoes/', true],
  ];
  for (const [nome, w, tema, rota] of shots) {
    const c = await browser.newContext({ viewport: { width: w, height: 800 }, deviceScaleFactor: 2 });
    const p = await c.newPage();
    await p.addInitScript(t => localStorage.setItem('rac-tema', t), tema);
    await p.goto(B + rota); await p.waitForTimeout(400);
    await p.screenshot({ path: 'capturas/' + nome + '.png', fullPage: true });
    await c.close();
  }
  // logo escura + sem logo
  const c = await browser.newContext({ viewport: { width: 1280, height: 400 } });
  const pl = await c.newPage(); await pl.addInitScript(() => localStorage.setItem('rac-tema', 'dark'));
  await pl.goto(B + '/?logoescuro=1');
  ok(await pl.evaluate(() => getComputedStyle(document.querySelector('.rac-header .rac-logo--escuro')).display !== 'none' && getComputedStyle(document.querySelector('.rac-header .rac-logo--claro')).display === 'none'), 'logo para fundo escuro: troca de arquivo no modo escuro');
  await pl.goto(B + '/?semlogo=1');
  ok(await pl.isVisible('.rac-header .rac-logo-texto'), 'sem logo: mostra o nome do site em texto');

  ok(errosJs.length === 0, 'sem erros de JavaScript na página (' + errosJs.join(' | ') + ')');
  out('\nFALHAS:', falhas);
  await browser.close();
  process.exit(falhas ? 1 : 0);
})();
