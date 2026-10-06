@props(['titulo' => null, 'descricao' => null, 'panorama' => null])
@php
    use App\Models\Programa;
    use App\Support\Panorama;
    use Illuminate\Support\Str;

    // Links do menu mantêm o ano que a pessoa está vendo.
    $link = fn (string $rota, array $parametros = []) => $panorama ? $panorama->rota($rota, $parametros) : route($rota, $parametros);
    $rotaAtual = Str::after((string) request()->route()?->getName(), 'ano.');

    // Seletor de exercício: leva à mesma página no outro ano (a ficha, se o programa existir lá).
    $anos = $panorama ? Panorama::exerciciosDisponiveis() : [];
    $exibido = Panorama::exercicioExibido();
    $urlAno = function (int $ano) use ($rotaAtual, $exibido) {
        $parametros = array_diff_key(request()->route()?->parameters() ?? [], ['ano' => true]);
        $rota = $rotaAtual ?: 'inicio';

        if ($rota === 'programas.show' && ! Programa::publicados()->doExercicio($ano)->where('slug', $parametros['slug'] ?? '')->exists()) {
            [$rota, $parametros] = ['programas.index', []];
        }

        $url = $ano === $exibido ? route($rota, $parametros) : route('ano.'.$rota, ['ano' => $ano] + $parametros);

        return $rota === 'programas.index' && request()->getQueryString() ? $url.'?'.request()->getQueryString() : $url;
    };
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ? $titulo.' · ' : '' }}Programas Municipais · Santa Helena - PR</title>
    <meta name="description" content="{{ $descricao ?? 'Conheça os programas da Prefeitura de Santa Helena: quanto custam, quem atendem e como participar.' }}">
    <meta name="theme-color" content="#0e6a9c">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('img/favicon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
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
                <a href="{{ route('inicio') }}" class="flex items-center gap-4 no-underline" aria-label="Início: exercício atual">
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
                                <a href="{{ $link($rota) }}"
                                   @class([
                                       'block rounded-md px-3 py-2 text-[#4f4f50] no-underline hover:bg-[#1ba2e8]/10 hover:text-[#0b5a86]',
                                       'bg-[#1ba2e8]/10 font-semibold text-[#0b5a86]' => $rotaAtual === $rota,
                                   ])
                                   @if ($rotaAtual === $rota) aria-current="page" @endif>{{ $rotulo }}</a>
                            </li>
                        @endforeach
                        {{-- Entrada do painel das secretarias, como no site de obras. --}}
                        <li class="ml-1.5 flex items-center">
                            <a href="{{ url('/admin') }}" class="block rounded-lg border border-[#c9d1db] bg-white px-3.5 py-2 text-[0.8125rem] font-semibold whitespace-nowrap text-[#1c1f24] no-underline hover:border-brand hover:text-[#0b5a86]">Acesso restrito</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        {{-- Na pré-visualização de um rascunho a faixa de exercício só confundiria. --}}
        @if (! request()->routeIs('previa.*') && (count($anos) > 1 || ($panorama && ! $panorama->ehExibido())))
            <div class="border-b border-line bg-surface">
                <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-4 gap-y-2 px-4 py-2 text-sm sm:px-6">
                    @if (! $panorama->ehExibido())
                        <p class="rounded-md bg-warn-bg px-2.5 py-1 text-warn-ink">
                            Você está vendo o exercício <strong>{{ $panorama->exercicio }}</strong>.
                            <a href="{{ $urlAno($exibido) }}" class="font-semibold text-warn-ink">Ver o atual ({{ $exibido }}) →</a>
                        </p>
                    @endif
                    <nav aria-label="Escolher exercício" class="flex items-center gap-2">
                        <span class="text-ink-2">Exercício:</span>
                        <ul class="flex flex-wrap gap-1">
                            @foreach ($anos as $ano)
                                <li>
                                    <a href="{{ $urlAno($ano) }}"
                                       @class([
                                           'block rounded-md px-2.5 py-1 font-medium no-underline',
                                           'bg-brand text-white' => $ano === $panorama->exercicio,
                                           'text-ink-2 hover:bg-surface-2 hover:text-ink' => $ano !== $panorama->exercicio,
                                       ])
                                       @if ($ano === $panorama->exercicio) aria-current="true" @endif>{{ $ano }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                </div>
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
                <p><a href="{{ $link('entenda') }}">Como os números são calculados</a></p>
            </div>
        </div>
    </footer>
</body>
</html>
