// Gera as telas do manual (public/manual-img) e o PDF (public/arquivos/manual-do-cadastrador.pdf)
// a partir do sistema rodando localmente.
//
// Pré-requisitos (ambiente local):
//   php artisan migrate:fresh --seed            (usuários de teste: admin / agricultura, senha "password")
//   php artisan serve                            (http://localhost:8000)
//   um rascunho de 2026 do "Viabiliza Agro" (Programas → Copiar para outro exercício) com valor e atendidos
//
// Uso:
//   npm run manual          telas + PDF
//   npm run manual -- telas  só as telas
//   npm run manual -- pdf    só o PDF
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.MANUAL_URL ?? 'http://localhost:8000';
const SAIDA = path.resolve('public/manual-img');
const PDF = path.resolve('public/arquivos/manual-do-cadastrador.pdf');
const CHROME = [
    process.env.CHROME_PATH,
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
    '/usr/bin/google-chrome',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
].find((p) => p && fs.existsSync(p));

const modo = process.argv[2] ?? 'tudo';
const espera = (ms) => new Promise((r) => setTimeout(r, ms));

const navegador = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--force-color-profile=srgb', '--lang=pt-BR'] });

try {
    if (modo !== 'pdf') await telas();
    if (modo !== 'telas') await gerarPdf();
} finally {
    await navegador.close();
}

async function novaPagina(largura = 1100, altura = 760, escala = 2) {
    const page = await navegador.newPage();
    await page.setViewport({ width: largura, height: altura, deviceScaleFactor: escala });
    await page.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: 'light' }, { name: 'prefers-reduced-motion', value: 'reduce' }]);
    return page;
}

async function ir(page, url) {
    await page.goto(BASE + url, { waitUntil: 'networkidle0' });
    await espera(400);
}

async function foto(page, nome, clip) {
    await page.screenshot({ path: path.join(SAIDA, nome), ...(clip ? { clip } : {}) });
    console.log('  ✓', nome);
}

/** Recorte de um elemento (com folga), achado pelo texto que contém. */
async function fotoDe(page, nome, seletorTexto, ancestral, folga = 12) {
    const alvo = await page.waitForSelector(seletorTexto, { timeout: 15000 });
    const el = ancestral ? await alvo.evaluateHandle((e, a) => e.closest(a) ?? e, ancestral) : alvo;
    await el.evaluate((e) => e.scrollIntoView({ block: 'center' }));
    await espera(300);
    const b = await el.boundingBox();
    await foto(page, nome, { x: Math.max(0, b.x - folga), y: Math.max(0, b.y - folga), width: b.width + folga * 2, height: b.height + folga * 2 });
}

async function entrar(page, usuario) {
    await ir(page, '/admin/login');
    await page.type('input[autocomplete="username"]', usuario);
    await page.type('input[type="password"]', 'password');
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('button[type="submit"]')]);
}

async function editar(page, busca, exercicio) {
    await ir(page, '/admin/programas');
    await page.type('.fi-ta input[type="search"]', busca);
    await espera(1500);
    const link = await page.evaluate(async (busca, exercicio) => {
        const linhas = [...document.querySelectorAll('table tbody tr')];
        const linha = linhas.find((tr) => tr.innerText.includes(busca) && tr.innerText.includes(String(exercicio)));
        return linha?.querySelector('a[href*="/edit"]')?.getAttribute('href');
    }, busca, exercicio);
    if (!link) throw new Error(`Não achei "${busca}" (${exercicio}) na lista de programas.`);
    await page.goto(link, { waitUntil: 'networkidle0' });
    await espera(500);
    return link;
}

async function telas() {
    fs.mkdirSync(SAIDA, { recursive: true });
    console.log('Telas do manual →', SAIDA);

    // 1. Login
    const login = await novaPagina(900, 620);
    await ir(login, '/admin/login');
    await foto(login, '01-login.png');
    await login.close();

    const page = await novaPagina();
    await entrar(page, 'agricultura');

    // 2. Painel de controle (aguarda os blocos carregarem)
    await page.waitForSelector('::-p-text(Como funciona)');
    await page.waitForSelector('::-p-text(Completar)', { timeout: 20000 });
    await espera(800);
    await foto(page, '02-painel.png');
    await fotoDe(page, '03-fichas-completar.png', '::-p-text(Faltam informações para o cidadão)', '.fi-wi-stats-overview, .fi-section, section', 0).catch(() => {});

    // 3. Lista de programas
    await ir(page, '/admin/programas');
    await page.setViewport({ width: 1100, height: 700, deviceScaleFactor: 2 });
    await foto(page, '04-lista.png');

    // 4. Rascunho: topo do formulário (avisos, botões e parte 1)
    await page.setViewport({ width: 1100, height: 1000, deviceScaleFactor: 2 });
    const rascunho = await editar(page, 'Viabiliza Agro', 2026);
    await foto(page, '05-rascunho-topo.png', { x: 0, y: 0, width: 1100, height: 1000 });
    await fotoDe(page, '06-parte-5-valor.png', '::-p-text(5. Quanto custa)', '.fi-section');
    await fotoDe(page, '07-parte-4-atendidos.png', '::-p-text(4. Quem foi atendido)', '.fi-section');

    // 5. Janela "Publicar no site?"
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.click('::-p-text(Publicar no site)');
    await page.waitForSelector('::-p-text(Publicar no site?)');
    await espera(500);
    await fotoDe(page, '08-publicar-confirmar.png', '::-p-text(Publicar no site?)', '.fi-modal-window', 0);
    await page.keyboard.press('Escape');

    // 6. Pré-visualização (aba que abre ao clicar em Pré-visualizar)
    const id = rascunho.match(/programas\/(\d+)\/edit/)[1];
    await page.setViewport({ width: 1100, height: 820, deviceScaleFactor: 2 });
    await ir(page, `/previa/programas/${id}`);
    await espera(1800);
    await foto(page, '09-previa.png');

    // 7. Programa publicado: aviso azul + Ver no site + histórico
    await page.setViewport({ width: 1100, height: 520, deviceScaleFactor: 2 });
    await editar(page, 'Incentivo para Apicultores', 2025);
    await foto(page, '10-publicado-topo.png');
    await page.setViewport({ width: 1100, height: 900, deviceScaleFactor: 2 });
    // O histórico carrega quando chega à tela: rola até o fim e espera.
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await espera(2500);
    await fotoDe(page, '11-historico.png', '.fi-resource-relation-manager, .fi-ta', null, 0).catch((e) => console.log('  (histórico)', e.message));

    // 8. Copiar para outro exercício (em lote)
    await page.setViewport({ width: 1100, height: 760, deviceScaleFactor: 2 });
    await ir(page, '/admin/programas');
    await page.click('thead input[type="checkbox"]');
    await espera(500);
    await foto(page, '12-lista-selecionada.png', { x: 0, y: 0, width: 1100, height: 420 });
    await page.click('::-p-text(Copiar para outro exercício)');
    await page.waitForSelector('::-p-text(Copiar programas para outro exercício)');
    await espera(500);
    await fotoDe(page, '13-copiar-exercicio.png', '::-p-text(Copiar programas para outro exercício)', '.fi-modal-window', 0);
    await page.close();

    // 9. O resultado no site: ficha publicada (computador e celular)
    const site = await novaPagina(1100, 820);
    await ir(site, '/programas/viabiliza-agro');
    await espera(1800);
    await foto(site, '14-site-ficha.png');
    await site.close();

    const celular = await novaPagina(390, 780, 2);
    await ir(celular, '/programas/viabiliza-agro');
    await espera(1800);
    await foto(celular, '15-celular-ficha.png');
    await celular.close();
}

async function gerarPdf() {
    fs.mkdirSync(path.dirname(PDF), { recursive: true });
    const page = await novaPagina(1000, 800, 1);
    await ir(page, '/manual');
    // As figuras usam loading="lazy": força o carregamento de todas antes de imprimir.
    await page.evaluate(async () => {
        const imagens = [...document.images];
        imagens.forEach((img) => { img.loading = 'eager'; });
        await Promise.all(imagens.map((img) => (img.complete ? img.decode().catch(() => {}) : new Promise((ok) => { img.onload = img.onerror = ok; }))));
    });
    await page.emulateMediaType('print');
    await page.evaluate(() => document.querySelectorAll('.faq details').forEach((d) => d.setAttribute('open', '')));
    await page.pdf({
        path: PDF,
        format: 'A4',
        printBackground: true,
        margin: { top: '14mm', bottom: '16mm', left: '14mm', right: '14mm' },
        displayHeaderFooter: true,
        headerTemplate: '<span></span>',
        footerTemplate: '<div style="width:100%;font:8px Arial,sans-serif;color:#6f737a;padding:0 14mm;display:flex;justify-content:space-between"><span>Manual do cadastrador · Programas Municipais · Santa Helena - PR</span><span><span class="pageNumber"></span>/<span class="totalPages"></span></span></div>',
    });
    console.log('PDF →', PDF);
    await page.close();
}
