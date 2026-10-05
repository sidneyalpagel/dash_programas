@php
    $admin = auth()->user()->isAdmin();
    $passos = $admin
        ? [
            ['Revise', 'Programas "Aguardando revisão" e com alterações propostas aparecem com um número no menu Programas.'],
            ['Compare', 'Abra o programa e clique em "Revisar alterações" para ver o antes e depois, campo a campo.'],
            ['Publique', 'Aprove a proposta ou clique em "Publicar no site". O site é atualizado na hora.'],
        ]
        : [
            ['Cadastre ou atualize', 'Em Programas, crie um novo ou abra um existente. Escreva como se explicasse para um vizinho.'],
            ['Envie para revisão', 'Programas novos começam como rascunho. Quando terminar, clique em "Enviar para revisão".'],
            ['Acompanhe', 'Se o programa já está no site, suas alterações ficam guardadas até o administrador aprovar.'],
        ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section heading="Como funciona" icon="heroicon-o-light-bulb">
        <ol style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); list-style: none; padding: 0; margin: 0">
            @foreach ($passos as $i => [$titulo, $texto])
                <li style="display: flex; gap: .75rem">
                    <span style="flex: none; width: 1.75rem; height: 1.75rem; border-radius: 999px; display: grid; place-items: center; font-weight: 600; font-size: .875rem; background: var(--primary-600); color: white">{{ $i + 1 }}</span>
                    <div>
                        <p style="font-weight: 600; margin: 0 0 .25rem">{{ $titulo }}</p>
                        <p style="font-size: .875rem; opacity: .75; margin: 0">{{ $texto }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
        <p style="font-size: .875rem; margin: 1rem 0 0">
            <a href="{{ url('/') }}" target="_blank" style="color: var(--primary-600); font-weight: 500">Abrir o site público ↗</a>
        </p>
    </x-filament::section>
</x-filament-widgets::widget>
