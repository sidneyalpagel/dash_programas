{{-- Caminho de um programa: rascunho → pré-visualização → publicado (mesmo desenho do manual). --}}
<figure class="manual-diagrama">
    <svg viewBox="0 0 760 256" role="img" aria-label="Você confere e publica: rascunho não aparece no site" style="width: 100%; min-width: 560px; height: auto" font-size="13" font-family="inherit">
        <defs>
            <marker id="manual-seta" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                <path d="M0 0L10 5L0 10z" fill="var(--gray-400)"/>
            </marker>
        </defs>
        <text x="24" y="34" font-size="15" font-weight="600" fill="currentColor">Você confere e publica: rascunho não aparece no site</text>

        <g fill="none" stroke="var(--gray-400)" stroke-width="1.25">
            <path d="M204 110H280" marker-end="url(#manual-seta)"/>
            <path d="M460 110H536" marker-end="url(#manual-seta)"/>
            <path d="M370 138V180H134V138" marker-end="url(#manual-seta)"/>
            <path d="M586 138V230H94V138" marker-end="url(#manual-seta)"/>
        </g>

        <rect x="24" y="82" width="180" height="56" rx="8" fill="none" stroke="var(--gray-400)" stroke-width="1.25"/>
        <text x="114" y="106" text-anchor="middle" font-weight="600" fill="currentColor">Rascunho</text>
        <text x="114" y="122" text-anchor="middle" font-size="11.5" fill="var(--gray-500)">você cadastra e salva</text>

        <rect x="280" y="82" width="180" height="56" rx="8" fill="none" stroke="var(--gray-400)" stroke-width="1.25"/>
        <text x="370" y="106" text-anchor="middle" font-weight="600" fill="currentColor">Pré-visualização</text>
        <text x="370" y="122" text-anchor="middle" font-size="11.5" fill="var(--gray-500)">a ficha como vai ficar</text>

        <rect x="536" y="82" width="180" height="56" rx="8" fill="var(--primary-600)" fill-opacity="0.1" stroke="var(--primary-600)" stroke-width="2"/>
        <text x="626" y="106" text-anchor="middle" font-weight="600" fill="currentColor">Publicado no site</text>
        <text x="626" y="122" text-anchor="middle" font-size="11.5" fill="currentColor">o cidadão vê a ficha</text>

        <text x="242" y="70" text-anchor="middle" font-size="11.5" fill="var(--gray-500)">Pré-visualizar</text>
        <text x="498" y="70" text-anchor="middle" font-size="11.5" fill="var(--gray-500)">Publicar no site</text>
        <text x="252" y="172" text-anchor="middle" font-size="11.5" fill="var(--gray-500)">ajustar e salvar</text>
        <text x="370" y="222" text-anchor="middle" font-size="11.5" fill="var(--gray-500)">Tirar do site (volta a rascunho)</text>
        <text x="604" y="164" font-size="11.5" fill="var(--gray-500)">editar e salvar:</text>
        <text x="604" y="180" font-size="11.5" fill="var(--gray-500)">o site muda na hora</text>
    </svg>
</figure>
