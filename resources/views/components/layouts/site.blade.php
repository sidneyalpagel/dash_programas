@props(['titulo' => null, 'descricao' => null, 'panorama' => null, 'faixa' => true])
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ? $titulo.' · ' : '' }}Programas Municipais · Santa Helena - PR</title>
    <meta name="description" content="{{ $descricao ?? 'Conheça os programas da Prefeitura de Santa Helena: quanto custam, quem atendem e como participar.' }}">
    <meta name="theme-color" content="#1d3a6b">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-surface focus:px-4 focus:py-2">Pular para o conteúdo</a>

    <header class="bg-brand text-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-4 sm:px-6">
            <a href="{{ route('inicio') }}" class="flex flex-col leading-tight text-white no-underline">
                <span class="text-xs font-medium tracking-wide text-white/75 uppercase">Prefeitura de Santa Helena · PR</span>
                <span class="text-lg font-semibold">Programas Municipais</span>
            </a>
            <nav aria-label="Principal">
                <ul class="flex flex-wrap gap-1 text-[0.9375rem]">
                    @foreach ([
                        'inicio' => 'Início',
                        'programas.index' => 'Todos os programas',
                        'entenda' => 'Entenda os números',
                    ] as $rota => $rotulo)
                        <li>
                            <a href="{{ route($rota) }}"
                               @class([
                                   'block rounded-md px-3 py-2 text-white no-underline hover:bg-white/10',
                                   'bg-white/15 font-semibold' => request()->routeIs($rota),
                               ])
                               @if (request()->routeIs($rota)) aria-current="page" @endif>{{ $rotulo }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
        @if ($faixa)
        <div class="flex h-1.5" aria-hidden="true">
            <span class="flex-1 bg-[#1e7145]"></span>
            <span class="flex-1 bg-gold"></span>
        </div>
        @endif
    </header>

    <main id="conteudo">
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-line bg-surface">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 text-sm text-ink-2 sm:grid-cols-2 sm:px-6">
            <div>
                <p class="font-semibold text-ink">Fonte dos dados</p>
                <p class="mt-1">Informações cadastradas pelas Secretarias Municipais de Agricultura e Abastecimento Rural, Desenvolvimento Econômico, Esportes e Lazer, Assistência Social e Educação e Cultura.</p>
                @if ($panorama)
                    @if ($panorama->ultimaAtualizacao())
                        <p class="mt-2">Última atualização: {{ $panorama->ultimaAtualizacao()->format('d/m/Y') }}.</p>
                    @endif
                @endif
            </div>
            <div class="sm:text-right">
                <p><a href="{{ route('entenda') }}">Como os números são calculados</a></p>
                <p class="mt-2"><a href="{{ url('/admin') }}" class="text-muted">Acesso restrito das secretarias</a></p>
            </div>
        </div>
    </footer>
</body>
</html>
