@props(['programa'])

@php use App\Support\Formato; @endphp

<article class="card group relative flex h-full flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-black/5 motion-reduce:transition-none motion-reduce:hover:translate-y-0">
    <span class="h-1.5 w-full" style="background: {{ $programa->mecanismo->corCss() }}" aria-hidden="true"></span>

    <div class="flex flex-1 flex-col p-5">
        <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-ink-2">
            <span class="inline-flex items-center gap-1.5">
                <span class="amostra" style="background: {{ $programa->mecanismo->corCss() }}" aria-hidden="true"></span>
                {{ $programa->mecanismo->rotuloCurto() }}
            </span>
            <span aria-hidden="true">·</span>
            <span>{{ $programa->secretaria->nome_curto }}</span>
        </p>

        <h3 class="mt-2 text-lg leading-snug font-bold">
            <a href="{{ route('programas.show', $programa) }}" class="text-ink no-underline after:absolute after:inset-0 group-hover:underline">{{ $programa->nome }}</a>
        </h3>

        @if ($programa->grupo)
            <p class="text-sm text-muted">Parte do programa {{ $programa->grupo }}</p>
        @endif

        @if ($programa->descricao)
            <p class="mt-2 line-clamp-3 text-sm text-ink-2">{{ $programa->descricao }}</p>
        @endif

        <div class="mt-auto pt-5">
        <div class="grid grid-cols-2 gap-3 border-t border-line pt-4">
            <div>
                <p class="text-xs text-muted">Valor no ano</p>
                @if ($programa->temCustoDireto())
                    <p class="text-xl font-extrabold tracking-tight">{{ Formato::moedaCurta($programa->valor) }}</p>
                @else
                    <p class="text-base font-semibold text-ink-2">Sem custo direto</p>
                @endif
            </div>
            <div>
                <p class="text-xs text-muted">Atendidos</p>
                @if ($programa->qtd_atendidos !== null)
                    <p class="text-xl font-extrabold tracking-tight">{{ Formato::numero($programa->qtd_atendidos) }}</p>
                    <p class="text-xs text-ink-2">{{ $programa->unidade_atendidos }}</p>
                @elseif ($programa->qtd_beneficios !== null)
                    <p class="text-xl font-extrabold tracking-tight">{{ Formato::numero($programa->qtd_beneficios) }}</p>
                    <p class="text-xs text-ink-2">benefícios pagos</p>
                @else
                    <p class="text-base font-semibold text-ink-2">—</p>
                @endif
            </div>
        </div>
        </div>
    </div>
</article>
