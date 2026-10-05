@props([
    // list<array{mecanismo: \App\Enums\Mecanismo, total: float}>
    'segmentos',
    // largura da barra em relação ao maior valor do gráfico (0 a 1)
    'proporcao' => 1,
    'inteira' => false,
    'contexto' => null,
])

@php
    use App\Support\Formato;

    $segmentos = collect($segmentos)->filter(fn ($s) => $s['total'] > 0)->values();
    $soma = $segmentos->sum('total') ?: 1;
    $descricao = $segmentos
        ->map(fn ($s) => $s['mecanismo']->rotuloCurto().': '.Formato::moedaCurta($s['total']))
        ->join('; ');
@endphp

<div @class(['barra', 'barra-inteira' => $inteira])
     style="width: {{ max(0.5, $proporcao * 100) }}%"
     role="img"
     aria-label="{{ $contexto ? $contexto.'. ' : '' }}{{ $descricao }}">
    @foreach ($segmentos as $s)
        <span style="flex: {{ round($s['total'] / $soma * 1000) }} 1 0; background: {{ $s['mecanismo']->corCss() }}"
              data-dica="{{ $contexto ? $contexto.' · ' : '' }}{{ $s['mecanismo']->rotuloCurto() }}: {{ Formato::moeda($s['total']) }} ({{ Formato::percentual($s['total'] / $soma) }})"></span>
    @endforeach
</div>
