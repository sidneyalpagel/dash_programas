@props([
    'valor',
    'casas' => 0,
    'prefixo' => '',
    'sufixo' => '',
])

{{-- O número final já vem renderizado; o JS só anima a contagem quando visível. --}}
<span class="tnum whitespace-nowrap" data-contar="{{ $valor }}" data-casas="{{ $casas }}" data-prefixo="{{ $prefixo }}" data-sufixo="{{ $sufixo }}">{{ $prefixo }}{{ number_format((float) $valor, $casas, ',', '.') }}{{ $sufixo }}</span>
