@props([
    'sobretitulo' => null,
    'titulo',
    'subtitulo' => null,
])

{{-- Abertura em azul institucional usada no topo das páginas internas. --}}
<section class="heroi relative overflow-hidden text-white">
    <div class="relative mx-auto max-w-6xl px-4 pt-8 pb-12 sm:px-6 sm:pt-10 sm:pb-14">
        @isset($trilha)
            <nav aria-label="Você está em" class="mb-5 flex flex-wrap items-center gap-x-2 text-sm text-white/75">
                {{ $trilha }}
            </nav>
        @endisset

        @if ($sobretitulo)
            <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-sm font-medium text-white/90 ring-1 ring-white/15">
                {{ $sobretitulo }}
            </p>
        @endif

        <h1 class="mt-4 max-w-4xl text-4xl leading-[1.1] font-extrabold tracking-tight sm:text-5xl">{{ $titulo }}</h1>

        @if ($subtitulo)
            <p class="mt-4 max-w-3xl text-lg leading-snug text-white/85 sm:text-xl">{{ $subtitulo }}</p>
        @endif

        {{ $slot }}
    </div>
    <div class="relative h-1 bg-accent" aria-hidden="true"></div>
</section>
