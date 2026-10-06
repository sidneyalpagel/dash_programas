@php
    use App\Enums\Mecanismo;
    use App\Support\Formato;

    $titulo = match (true) {
        (bool) $filtros['perfil'] => 'Programas para: '.$filtros['perfil']->getLabel(),
        (bool) $filtros['grupo'] => $filtros['grupo'],
        (bool) $filtros['secretaria'] => $filtros['secretaria']->nome_curto,
        default => 'Todos os programas',
    };

    $subtitulo = $filtros['secretaria']?->apresentacao
        ?? 'Escolha um perfil ou use os filtros para encontrar os programas que fazem sentido para você.';

    $totalResultado = (float) $programas->sum('valor');
    $secretariasResultado = $programas->pluck('secretaria_id')->unique()->count();
    $semCustoResultado = $programas->filter->semCustoDireto()->count();

    // Estado atual da URL; cada filtro pode ser removido individualmente.
    $consulta = array_filter([
        'perfil' => $filtros['perfil']?->value,
        'grupo' => $filtros['grupo'],
        'secretaria' => $filtros['secretaria']?->slug,
        'tipo' => $filtros['tipo']?->value,
        'busca' => $filtros['busca'] ?: null,
        'ordem' => $ordem !== 'nome' ? $ordem : null,
    ]);
    $semFiltro = fn (string ...$chaves) => $panorama->rota('programas.index', array_diff_key($consulta, array_flip($chaves)));

    // Trocar de perfil recomeça a busca por texto e por grupo, para não somar filtros invisíveis.
    $urlPerfil = fn (?string $perfil) => $panorama->rota('programas.index', array_filter(
        ['perfil' => $perfil] + array_diff_key($consulta, array_flip(['perfil', 'busca', 'grupo'])),
    ));

    $ativos = array_filter([
        'perfil' => $filtros['perfil'] ? 'Para: '.$filtros['perfil']->getLabel() : null,
        'grupo' => $filtros['grupo'] ? 'Programa: '.$filtros['grupo'] : null,
        'secretaria' => $filtros['secretaria'] ? 'Secretaria: '.$filtros['secretaria']->nome_curto : null,
        'tipo' => $filtros['tipo'] ? 'Tipo: '.$filtros['tipo']->rotuloCurto() : null,
        'busca' => $filtros['busca'] ? 'Busca: "'.$filtros['busca'].'"' : null,
    ]);
@endphp

<x-layouts.site :titulo="$titulo" :panorama="$panorama">
    <x-cabecalho-pagina :titulo="$titulo" :subtitulo="$subtitulo" :sobretitulo="'Exercício '.$panorama->exercicio">
        {{-- Perfis --}}
        <nav aria-label="Filtrar por perfil" class="mt-8">
            <p class="text-sm font-semibold tracking-wide text-white/80 uppercase">Para quem?</p>
            <ul class="mt-3 flex flex-wrap gap-2">
                <li><a href="{{ $urlPerfil(null) }}" class="chip chip-claro" aria-current="{{ $filtros['perfil'] ? 'false' : 'true' }}">Todos</a></li>
                @foreach ($panorama->perfis() as $item)
                    <li>
                        <a href="{{ $urlPerfil($item['perfil']->value) }}" class="chip chip-claro"
                           aria-current="{{ $filtros['perfil'] === $item['perfil'] ? 'true' : 'false' }}">{{ $item['perfil']->getLabel() }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Resultado em números --}}
        <div class="mt-10 grid gap-4 sm:grid-cols-3" aria-live="polite">
            <x-cartao-heroi :rotulo="$programas->count() === 1 ? 'programa encontrado' : 'programas encontrados'"
                            :detalhe="$filtrado ? 'de '.$panorama->quantidade().' no total' : null">
                <x-numero-animado :valor="$programas->count()" />
            </x-cartao-heroi>
            <x-cartao-heroi rotulo="destinados no ano"
                            :detalhe="($filtrado && $panorama->total() > 0 ? Formato::percentual($totalResultado / $panorama->total()).' do total do Município' : 'soma dos programas com custo direto')
                                .($semCustoResultado ? ' · '.$semCustoResultado.' sem custo direto' : '')">
                <x-moeda-animada :valor="$totalResultado" classe-unidade="text-2xl font-bold sm:text-3xl" />
            </x-cartao-heroi>
            <x-cartao-heroi :rotulo="$secretariasResultado === 1 ? 'secretaria responsável' : 'secretarias responsáveis'">
                <x-numero-animado :valor="$secretariasResultado" />
            </x-cartao-heroi>
        </div>
    </x-cabecalho-pagina>

    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        {{-- Demais filtros --}}
        <form method="get" action="{{ $panorama->rota('programas.index') }}" class="card relative z-10 -mt-6 grid gap-4 p-4 shadow-lg shadow-black/5 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_repeat(3,minmax(0,1fr))_auto] lg:items-end" role="search">
            @foreach (['perfil', 'grupo'] as $oculto)
                @isset($consulta[$oculto])
                    <input type="hidden" name="{{ $oculto }}" value="{{ $consulta[$oculto] }}">
                @endisset
            @endforeach
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-ink-2">Buscar pelo nome</span>
                <input type="search" name="busca" value="{{ $filtros['busca'] }}" placeholder="Ex.: transporte, bolsa, leite"
                       class="rounded-lg border border-line bg-surface px-3 py-2 text-base text-ink">
            </label>
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-ink-2">Secretaria</span>
                <select name="secretaria" data-auto-submit class="rounded-lg border border-line bg-surface px-3 py-2 text-base text-ink">
                    <option value="">Todas</option>
                    @foreach ($secretarias as $secretaria)
                        <option value="{{ $secretaria->slug }}" @selected($filtros['secretaria']?->is($secretaria))>{{ $secretaria->nome_curto }}</option>
                    @endforeach
                </select>
            </label>
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-ink-2">Como o recurso chega</span>
                <select name="tipo" data-auto-submit class="rounded-lg border border-line bg-surface px-3 py-2 text-base text-ink">
                    <option value="">Todos</option>
                    @foreach (Mecanismo::cases() as $mecanismo)
                        <option value="{{ $mecanismo->value }}" @selected($filtros['tipo'] === $mecanismo)>{{ $mecanismo->rotuloCurto() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-ink-2">Ordenar por</span>
                <select name="ordem" data-auto-submit class="rounded-lg border border-line bg-surface px-3 py-2 text-base text-ink">
                    <option value="nome" @selected($ordem === 'nome')>Nome (A–Z)</option>
                    <option value="valor" @selected($ordem === 'valor')>Maior valor</option>
                </select>
            </label>
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-brand px-4 py-2 font-semibold text-white hover:bg-brand-2">Filtrar</button>
                @if ($filtrado)
                    <a href="{{ $panorama->rota('programas.index') }}" class="text-sm whitespace-nowrap">Limpar</a>
                @endif
            </div>
        </form>

        @if ($ativos)
            <ul class="mt-5 flex flex-wrap items-center gap-2 text-sm" aria-label="Filtros ativos">
                @foreach ($ativos as $chave => $rotulo)
                    <li>
                        <a href="{{ $semFiltro($chave) }}" class="chip py-1.5" aria-label="Remover filtro {{ $rotulo }}">
                            {{ $rotulo }} <span aria-hidden="true" class="text-muted">✕</span>
                        </a>
                    </li>
                @endforeach
                @if (count($ativos) > 1)
                    <li><a href="{{ $panorama->rota('programas.index') }}" class="ml-1 font-medium">Limpar tudo</a></li>
                @endif
            </ul>
        @endif

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
            <p class="text-ink-2">
                @if ($programas->isEmpty())
                    Nenhum programa encontrado com esses filtros.
                    @if (count($ativos) > 1)
                        Tente remover um deles acima ou <a href="{{ $panorama->rota('programas.index') }}">ver todos</a>.
                    @else
                        <a href="{{ $panorama->rota('programas.index') }}">Ver todos</a>.
                    @endif
                @else
                    <strong class="text-ink">{{ $programas->count() }} {{ $programas->count() === 1 ? 'programa' : 'programas' }}</strong>
                    @if ($totalResultado > 0)
                        · {{ Formato::moeda($totalResultado) }} no ano
                    @endif
                @endif
            </p>
            <x-legenda-mecanismos />
        </div>

        <ul class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($programas as $programa)
                <li><x-programa-card :programa="$programa" /></li>
            @endforeach
        </ul>
    </div>
</x-layouts.site>
