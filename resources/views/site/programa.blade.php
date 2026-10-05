@php
    use App\Enums\FonteRecurso;
    use App\Enums\TipoValor;
    use App\Support\Formato;

    $porAtendido = $programa->valorPorAtendido();
    $fracaoTotal = $programa->temCustoDireto() && $panorama->total() > 0 ? $programa->valor / $panorama->total() : null;
    $rotuloMedia = $programa->qtd_atendidos ? 'por atendido' : 'por benefício pago';
@endphp

<x-layouts.site :titulo="$programa->nome" :descricao="$programa->descricao" :panorama="$panorama">
    <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
        <nav aria-label="Você está em" class="text-sm text-ink-2">
            <a href="{{ route('programas.index') }}">Programas</a>
            <span aria-hidden="true">›</span>
            <a href="{{ route('programas.index', ['secretaria' => $programa->secretaria->slug]) }}">{{ $programa->secretaria->nome_curto }}</a>
        </nav>

        <header class="mt-4">
            <p class="flex items-center gap-2 text-sm font-medium text-ink-2">
                <span class="amostra" style="background: {{ $programa->mecanismo->corCss() }}" aria-hidden="true"></span>
                {{ $programa->mecanismo->getLabel() }}
            </p>
            <h1 class="mt-2 text-3xl leading-tight font-semibold sm:text-4xl">{{ $programa->nome }}</h1>
            @if ($programa->grupo)
                <p class="mt-1 text-ink-2">Faz parte do programa <a href="{{ route('programas.index', ['busca' => $programa->grupo]) }}">{{ $programa->grupo }}</a></p>
            @endif
        </header>

        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            {{-- Números ------------------------------------------------- --}}
            <aside class="card h-fit p-6 lg:order-2" aria-label="Números do programa em {{ $programa->exercicio }}">
                <p class="text-sm text-ink-2">Valor em {{ $programa->exercicio }}</p>
                @if ($programa->temCustoDireto())
                    <p class="mt-1 text-4xl font-bold">{{ Formato::moedaCurta($programa->valor) }}</p>
                    <p class="text-sm text-ink-2 tnum">{{ Formato::moeda($programa->valor) }}</p>
                    @if ($fracaoTotal !== null)
                        <p class="mt-3 text-sm text-ink-2">
                            {{ Formato::percentual($fracaoTotal) }} de tudo o que o Município aplica nos {{ $panorama->quantidade() }} programas.
                        </p>
                    @endif
                @else
                    <p class="mt-1 text-2xl font-semibold">Sem custo direto</p>
                    <p class="mt-1 text-sm text-ink-2">{{ TipoValor::SemCusto->getDescription() }}</p>
                @endif

                <hr class="my-5 border-line">

                <p class="text-sm text-ink-2">Atendidos</p>
                <p class="mt-1 text-2xl font-semibold">{{ $programa->atendidosTexto() ?? 'A informar' }}</p>
                @if ($programa->detalhe_atendidos)
                    <p class="text-sm text-ink-2">{{ $programa->detalhe_atendidos }}</p>
                @endif

                @if ($porAtendido)
                    <hr class="my-5 border-line">
                    <p class="text-sm text-ink-2">
                        <span class="termo" tabindex="0" data-dica="Valor do ano dividido pela quantidade informada. É uma média: cada pessoa pode receber valores diferentes.">Média</span> {{ $rotuloMedia }}
                    </p>
                    <p class="mt-1 text-2xl font-semibold">{{ Formato::moeda($porAtendido, $porAtendido >= 100 ? 0 : 2) }} <span class="text-base font-normal text-ink-2">por ano</span></p>
                @endif

                @if ($programa->tipo_valor === TipoValor::Anualizado)
                    <p class="mt-5 rounded-lg bg-warn-bg p-3 text-sm text-warn-ink">
                        Valor anualizado: total de {{ Formato::moeda($programa->valor_total_vigencia) }} em {{ $programa->vigencia_anos }} anos, dividido por {{ $programa->vigencia_anos }}.
                    </p>
                @endif

                @if ($programa->fonte_recurso !== FonteRecurso::NaoInformado)
                    <p class="mt-5 text-sm text-ink-2">Origem do dinheiro: <strong class="text-ink">{{ $programa->fonte_recurso->getLabel() }}</strong></p>
                @endif
            </aside>

            {{-- Texto --------------------------------------------------- --}}
            <div class="space-y-8 lg:col-span-2">
                <section>
                    <h2 class="text-xl font-semibold">O que é</h2>
                    <p class="mt-2 text-lg leading-relaxed text-ink-2">
                        {{ $programa->descricao ?? 'A secretaria responsável ainda está preparando a descrição deste programa.' }}
                    </p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold">Para quem é</h2>
                    @if ($programa->perfis())
                        <ul class="mt-3 flex flex-wrap gap-2">
                            @foreach ($programa->perfis() as $perfil)
                                <li><a href="{{ route('programas.index', ['perfil' => $perfil->value]) }}" class="chip">{{ $perfil->getLabel() }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section>
                    <h2 class="text-xl font-semibold">Como participar</h2>
                    @if ($programa->como_participar)
                        <p class="mt-2 text-lg leading-relaxed whitespace-pre-line text-ink-2">{{ $programa->como_participar }}</p>
                    @else
                        <p class="mt-2 text-ink-2">Procure a {{ $programa->secretaria->nome }}.</p>
                    @endif
                    @if ($programa->secretaria->endereco || $programa->secretaria->telefone || $programa->secretaria->email)
                        <p class="mt-3 text-sm text-ink-2">
                            {{ collect([$programa->secretaria->endereco, $programa->secretaria->telefone, $programa->secretaria->email])->filter()->join(' · ') }}
                        </p>
                    @endif
                </section>

                <section>
                    <h2 class="text-xl font-semibold">Base legal</h2>
                    @if ($programa->bases_legais)
                        <ul class="mt-2 space-y-1 text-ink-2">
                            @foreach ($programa->bases_legais as $lei)
                                @php $texto = trim(($lei['tipo'] ?? 'Lei').' nº '.($lei['numero'] ?? '').'/'.($lei['ano'] ?? '')); @endphp
                                <li>
                                    @if (filled($lei['link'] ?? null))
                                        <a href="{{ $lei['link'] }}" rel="noopener" target="_blank">{{ $texto }} ↗</a>
                                    @else
                                        {{ $texto }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-2 text-ink-2">Em atualização pela secretaria.</p>
                    @endif
                </section>

                @if ($programa->nota_publica)
                    <section class="rounded-xl border border-line bg-surface-2 p-4">
                        <h2 class="text-sm font-semibold">Nota</h2>
                        <p class="mt-1 text-sm text-ink-2">{{ $programa->nota_publica }}</p>
                    </section>
                @endif

                <p class="text-sm text-muted">Responsável: {{ $programa->secretaria->nome }} · Informações de {{ $programa->exercicio }}.</p>
            </div>
        </div>

        @if ($relacionados->isNotEmpty())
            <section class="mt-14" aria-labelledby="relacionados">
                <h2 id="relacionados" class="text-xl font-semibold">
                    {{ $programa->grupo ? 'Outros benefícios do '.$programa->grupo : 'Outros programas da '.$programa->secretaria->nome_curto }}
                </h2>
                <ul class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relacionados as $relacionado)
                        <li><x-programa-card :programa="$relacionado" /></li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-layouts.site>
