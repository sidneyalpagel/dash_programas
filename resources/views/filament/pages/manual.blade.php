<x-filament-panels::page>
    {{-- Estilos locais: o CSS do Filament não traz tipografia para textos longos. --}}
    <style>
        .manual { max-width: 52rem; line-height: 1.65; font-size: .95rem; color: var(--gray-700); }
        .dark .manual { color: var(--gray-300); }
        .manual h2 { font-size: 1.3rem; font-weight: 700; color: var(--gray-950); margin: 2.5rem 0 .75rem; scroll-margin-top: 5rem; }
        .dark .manual h2 { color: #fff; }
        .manual p, .manual ul, .manual ol, .manual table { margin: 0 0 1rem; }
        .manual ul { list-style: disc; padding-left: 1.4rem; }
        .manual ol { list-style: decimal; padding-left: 1.4rem; }
        .manual li { margin: .25rem 0; }
        .manual strong { color: var(--gray-950); font-weight: 600; }
        .dark .manual strong { color: #fff; }
        .manual a { color: var(--primary-600); text-decoration: underline; text-underline-offset: 2px; }
        .manual code { font-size: .85em; padding: .1rem .35rem; border-radius: .3rem; background: var(--gray-100); }
        .dark .manual code { background: var(--gray-800); }
        .manual table { width: 100%; border-collapse: collapse; font-size: .875rem; display: block; overflow-x: auto; }
        .manual th, .manual td { text-align: left; vertical-align: top; padding: .55rem .75rem; border: 1px solid var(--gray-200); }
        .dark .manual th, .dark .manual td { border-color: var(--gray-700); }
        .manual th { background: var(--gray-50); font-weight: 600; color: var(--gray-950); }
        .dark .manual th { background: var(--gray-800); color: #fff; }
        .manual-sumario { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.5rem; }
        .manual-sumario a { font-size: .85rem; padding: .35rem .7rem; border-radius: 999px; border: 1px solid var(--gray-200); color: var(--gray-700); text-decoration: none; }
        .manual-sumario a:hover { border-color: var(--primary-600); color: var(--primary-600); }
        .dark .manual-sumario a { border-color: var(--gray-700); color: var(--gray-300); }
        .manual-diagrama { margin: .5rem 0 1rem; padding: 1rem; border: 1px solid var(--gray-200); border-radius: .75rem; overflow-x: auto; }
        .dark .manual-diagrama { border-color: var(--gray-700); }
        @media print { .fi-sidebar, .fi-topbar, .manual-sumario { display: none !important; } }
    </style>

    <x-filament::section>
        <nav class="manual-sumario" aria-label="Sumário do manual">
            @foreach ($this->sumario() as $item)
                <a href="#{{ $item['id'] }}">{{ $item['titulo'] }}</a>
            @endforeach
        </nav>

        <article class="manual">
            {{ $this->conteudo() }}
        </article>
    </x-filament::section>
</x-filament-panels::page>
