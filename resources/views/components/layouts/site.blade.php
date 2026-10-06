@props(['titulo' => null, 'descricao' => null, 'panorama' => null])
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ? $titulo.' · ' : '' }}Programas Municipais · Santa Helena - PR</title>
    <meta name="description" content="{{ $descricao ?? 'Conheça os programas da Prefeitura de Santa Helena: quanto custam, quem atendem e como participar.' }}">
    <meta name="theme-color" content="#0e6a9c">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=rubik:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-surface focus:px-4 focus:py-2">Pular para o conteúdo</a>

    {{-- Cabeçalho no padrão do portal da Prefeitura (santahelena.atende.net): faixa azul e logo sobre fundo claro, nos dois temas. --}}
    <header>
        <div class="bg-brand text-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-1.5 text-[0.8125rem] sm:px-6">
                <span class="truncate"><span class="hidden sm:inline">Prefeitura Municipal de </span>Santa Helena · PR</span>
                <a href="https://santahelena.atende.net/" class="font-medium whitespace-nowrap text-white no-underline hover:underline" rel="noopener">Portal da Prefeitura ↗</a>
            </div>
        </div>
        <div class="border-b-4 border-accent bg-[#f5f5f5] text-[#4f4f50]">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-3 sm:px-6">
                <a href="{{ route('inicio') }}" class="flex items-center gap-4 no-underline">
                    <img src="{{ asset('img/logo-prefeitura-santa-helena.png') }}" alt="Prefeitura de Santa Helena" width="819" height="203" class="h-12 w-auto sm:h-14">
                    <span class="hidden border-l border-[#d4d7db] pl-4 text-lg leading-tight font-semibold text-[#0b5a86] sm:block">Programas<br>Municipais</span>
                </a>
                <nav aria-label="Principal">
                    <ul class="flex flex-wrap gap-1 text-[0.9375rem] font-medium">
                        @foreach ([
                            'inicio' => 'Início',
                            'programas.index' => 'Todos os programas',
                            'entenda' => 'Entenda os números',
                        ] as $rota => $rotulo)
                            <li>
                                <a href="{{ route($rota) }}"
                                   @class([
                                       'block rounded-md px-3 py-2 text-[#4f4f50] no-underline hover:bg-[#1ba2e8]/10 hover:text-[#0b5a86]',
                                       'bg-[#1ba2e8]/10 font-semibold text-[#0b5a86]' => request()->routeIs($rota),
                                   ])
                                   @if (request()->routeIs($rota)) aria-current="page" @endif>{{ $rotulo }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
        </div>
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
