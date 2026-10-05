@php
    use App\Enums\Mecanismo;
    use App\Enums\PublicoAlvo;
    use App\Support\Formato;

    $titulo = $filtros['perfil']
        ? 'Programas para: '.$filtros['perfil']->getLabel()
        : ($filtros['secretaria'] ? $filtros['secretaria']->nome : 'Todos os programas');
@endphp

<x-layouts.site :titulo="$titulo" :panorama="$panorama">
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
        <h1 class="text-3xl font-semibold">{{ $titulo }}</h1>
        @if ($filtros['secretaria']?->apresentacao)
            <p class="mt-2 max-w-3xl text-lg text-ink-2">{{ $filtros['secretaria']->apresentacao }}</p>
        @endif

        {{-- Perfis -------------------------------------------------------- --}}
        <nav aria-label="Filtrar por perfil" class="mt-6">
            <p class="text-sm font-medium text-ink-2">Para quem?</p>
            <ul class="mt-2 flex flex-wrap gap-2">
                <li>
                    <a href="{{ route('programas.index', array_filter(['secretaria' => $filtros['secretaria']?->slug, 'tipo' => $filtros['tipo']?->value])) }}"
                       class="chip" aria-current="{{ $filtros['perfil'] ? 'false' : 'true' }}">Todos</a>
                </li>
                @foreach ($panorama->perfis() as $item)
                    <li>
                        <a href="{{ route('programas.index', array_filter(['perfil' => $item['perfil']->value, 'secretaria' => $filtros['secretaria']?->slug, 'tipo' => $filtros['tipo']?->value])) }}"
                           class="chip" aria-current="{{ $filtros['perfil'] === $item['perfil'] ? 'true' : 'false' }}">{{ $item['perfil']->getLabel() }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Demais filtros ------------------------------------------------ --}}
        <form method="get" action="{{ route('programas.index') }}" class="card mt-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4" role="search">
            @if ($filtros['perfil'])
                <input type="hidden" name="perfil" value="{{ $filtros['perfil']->value }}">
            @endif
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
            <div class="flex flex-wrap items-center gap-3 sm:col-span-2 lg:col-span-4">
                <button type="submit" class="rounded-lg bg-brand px-4 py-2 font-semibold text-white hover:bg-brand-2">Filtrar</button>
                @if ($filtrado)
                    <a href="{{ route('programas.index') }}" class="text-sm">Limpar filtros</a>
                @endif
            </div>
        </form>

        {{-- Resumo do resultado ------------------------------------------- --}}
        <p class="mt-6 text-ink-2" aria-live="polite">
            @if ($programas->isEmpty())
                Nenhum programa encontrado com esses filtros.
            @else
                <strong class="text-ink">{{ $programas->count() }} {{ $programas->count() === 1 ? 'programa' : 'programas' }}</strong>
                @if ($programas->sum('valor') > 0)
                    · {{ Formato::moeda($programas->sum('valor')) }} no ano
                @endif
            @endif
        </p>

        <ul class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($programas as $programa)
                <li><x-programa-card :programa="$programa" /></li>
            @endforeach
        </ul>
    </div>
</x-layouts.site>
