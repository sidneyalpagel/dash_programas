// Dica flutuante para barras e termos: qualquer elemento com data-dica.
// O mesmo texto também está no title/aria-label ou na tabela, então nada depende só disto.
const dica = document.createElement('div');
dica.id = 'dica';
dica.setAttribute('role', 'tooltip');
document.body.appendChild(dica);

let alvoAtual = null;

function posicionar(x, y) {
    const margem = 14;
    const largura = dica.offsetWidth;
    const altura = dica.offsetHeight;
    let left = x + margem;
    let top = y + margem;

    if (left + largura > window.innerWidth - 8) left = x - largura - margem;
    if (top + altura > window.innerHeight - 8) top = y - altura - margem;

    dica.style.left = `${Math.max(8, left)}px`;
    dica.style.top = `${Math.max(8, top)}px`;
}

function mostrar(alvo, x, y) {
    alvoAtual = alvo;
    dica.textContent = alvo.dataset.dica;
    dica.classList.add('visivel');
    posicionar(x, y);
}

function esconder() {
    alvoAtual = null;
    dica.classList.remove('visivel');
}

document.addEventListener('pointerover', (e) => {
    const alvo = e.target.closest('[data-dica]');
    if (alvo) mostrar(alvo, e.clientX, e.clientY);
});

document.addEventListener('pointermove', (e) => {
    if (alvoAtual) posicionar(e.clientX, e.clientY);
});

document.addEventListener('pointerout', (e) => {
    if (alvoAtual && !alvoAtual.contains(e.relatedTarget)) esconder();
});

document.addEventListener('focusin', (e) => {
    const alvo = e.target.closest('[data-dica]');
    if (!alvo) return;
    const r = alvo.getBoundingClientRect();
    mostrar(alvo, r.left, r.bottom);
});

document.addEventListener('focusout', esconder);
document.addEventListener('keydown', (e) => e.key === 'Escape' && esconder());
window.addEventListener('scroll', esconder, { passive: true });

// Contagem animada dos números de destaque. O valor final já está no HTML;
// sem JS ou com "reduzir movimento" ativado, nada muda.
const semMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const contadores = document.querySelectorAll('[data-contar]');

if (!semMovimento && 'IntersectionObserver' in window && contadores.length) {
    const formatar = (el, valor) => {
        const casas = Number(el.dataset.casas || 0);
        const texto = valor.toLocaleString('pt-BR', { minimumFractionDigits: casas, maximumFractionDigits: casas });
        return `${el.dataset.prefixo || ''}${texto}${el.dataset.sufixo || ''}`;
    };

    const animar = (el) => {
        const final = Number(el.dataset.contar);
        const duracao = 1400;
        const inicio = performance.now();

        const passo = (agora) => {
            const t = Math.min(1, (agora - inicio) / duracao);
            const suave = 1 - Math.pow(1 - t, 3);
            el.textContent = formatar(el, final * suave);
            if (t < 1) requestAnimationFrame(passo);
        };

        requestAnimationFrame(passo);
    };

    const observador = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada) => {
            if (!entrada.isIntersecting) return;
            observador.unobserve(entrada.target);
            animar(entrada.target);
        });
    }, { threshold: 0.4 });

    contadores.forEach((el) => {
        el.setAttribute('aria-label', el.textContent);
        observador.observe(el);
    });
}

// Filtros da lista de programas: aplica ao trocar o select, sem botão extra.
document.querySelectorAll('[data-auto-submit]').forEach((campo) => {
    campo.addEventListener('change', () => campo.form.requestSubmit());
});
