{{-- Legenda única do site: a cor sempre indica como o recurso chega ao cidadão. --}}
<ul {{ $attributes->merge(['class' => 'flex flex-wrap gap-x-5 gap-y-2 text-sm text-ink-2']) }} aria-label="Legenda das cores">
    @foreach (\App\Enums\Mecanismo::cases() as $mecanismo)
        <li class="flex items-center gap-2">
            <span class="amostra" style="background: {{ $mecanismo->corCss() }}" aria-hidden="true"></span>
            {{ $mecanismo->rotuloCurto() }}
        </li>
    @endforeach
</ul>
