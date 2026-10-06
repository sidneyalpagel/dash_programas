@props([
    'valor',
    // classes do "mi"/"mil" ao lado do número
    'classeUnidade' => 'text-2xl font-bold',
])

{{-- Valor em reais na forma curta (R$ 1,98 mi), com a mesma regra de arredondamento do resto do site. --}}
@php $partes = \App\Support\Formato::partesCurtas((float) $valor); @endphp

@if ($partes['unidade'] === null)
    {{ \App\Support\Formato::moeda($valor) }}
@else
    <span class="whitespace-nowrap"><x-numero-animado :valor="$partes['numero']" :casas="$partes['casas']" prefixo="R$ " /><span class="{{ $classeUnidade }}">&nbsp;{{ $partes['abreviada'] }}</span></span>
@endif
