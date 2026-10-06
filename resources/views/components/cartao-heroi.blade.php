@props([
    'rotulo',
    'detalhe' => null,
])

{{-- Número grande sobre o fundo azul da abertura. O valor vai no slot. --}}
<div {{ $attributes->merge(['class' => 'rounded-2xl bg-white/10 p-5 ring-1 ring-white/15 backdrop-blur-sm']) }}>
    <p class="text-4xl leading-none font-extrabold tracking-tight sm:text-5xl lg:text-4xl xl:text-5xl">{{ $slot }}</p>
    <p class="mt-3 text-base font-medium text-white/90">{{ $rotulo }}</p>
    @if ($detalhe)
        <p class="mt-1 text-sm text-white/70">{{ $detalhe }}</p>
    @endif
</div>
