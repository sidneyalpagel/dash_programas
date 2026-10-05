@props(['programa'])

@php use App\Support\Formato; @endphp

<article class="card relative flex h-full flex-col p-5">
    <p class="flex items-center gap-2 text-xs font-medium text-ink-2">
        <span class="amostra" style="background: {{ $programa->mecanismo->corCss() }}" aria-hidden="true"></span>
        {{ $programa->mecanismo->rotuloCurto() }}
        <span aria-hidden="true">·</span>
        {{ $programa->secretaria->nome_curto }}
    </p>

    <h3 class="mt-2 text-lg leading-snug font-semibold">
        <a href="{{ route('programas.show', $programa) }}" class="text-ink no-underline after:absolute after:inset-0 hover:underline">{{ $programa->nome }}</a>
    </h3>

    @if ($programa->grupo)
        <p class="text-sm text-muted">Parte do programa {{ $programa->grupo }}</p>
    @endif

    @if ($programa->descricao)
        <p class="mt-2 line-clamp-3 text-sm text-ink-2">{{ $programa->descricao }}</p>
    @endif

    <dl class="mt-auto grid grid-cols-2 gap-3 pt-4 text-sm">
        <div>
            <dt class="text-muted">Valor no ano</dt>
            <dd class="font-semibold">{{ $programa->temCustoDireto() ? Formato::moedaCurta($programa->valor) : 'Sem custo direto' }}</dd>
        </div>
        <div>
            <dt class="text-muted">Atendidos</dt>
            <dd class="font-semibold">{{ $programa->atendidosTexto() ?? '—' }}</dd>
        </div>
    </dl>
</article>
