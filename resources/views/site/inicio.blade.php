@php
    use App\Support\Formato;

    $porSecretaria = $panorama->porSecretaria();
    $porMecanismo = $panorama->porMecanismo();
    $maiores = $panorama->maiores(10);
    $maiorSecretaria = $porSecretaria->max('total') ?: 1;
    $maiorPrograma = $maiores->first();
@endphp

<x-layouts.site :panorama="$panorama">
    {{-- Abertura ---------------------------------------------------------- --}}
    <section class="border-b border-line bg-surface">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-14">
            <p class="text-sm font-semibold tracking-wide text-ink-2 uppercase">Exercício {{ $panorama->exercicio }}</p>
            <h1 class="mt-3 max-w-3xl text-2xl leading-snug font-semibold text-ink-2 sm:text-3xl">
                A Prefeitura de Santa Helena destinou
                <span class="block text-5xl leading-tight font-bold text-ink sm:text-6xl">{{ Formato::moedaCurta($panorama->total()) }}</span>
                a {{ $panorama->quantidade() }} programas de {{ $porSecretaria->count() }} secretarias.
            </h1>
            <p class="mt-4 max-w-2xl text-lg text-ink-2">
                Aqui você descobre para onde vai esse dinheiro, quem é atendido e como participar.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="#para-mim" class="rounded-lg bg-brand px-5 py-3 font-semibold text-white no-underline hover:bg-brand-2">Que programas existem para mim?</a>
                <a href="{{ route('programas.index') }}" class="rounded-lg border border-line bg-surface px-5 py-3 font-semibold text-ink no-underline hover:border-link">Ver todos os programas</a>
            </div>

            <dl class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-xl bg-surface-2 p-4">
                    <dt class="text-sm text-ink-2">Programas</dt>
                    <dd class="mt-1 text-3xl font-semibold">{{ $panorama->quantidade() }}</dd>
                </div>
                <div class="rounded-xl bg-surface-2 p-4">
                    <dt class="text-sm text-ink-2">Secretarias</dt>
                    <dd class="mt-1 text-3xl font-semibold">{{ $porSecretaria->count() }}</dd>
                </div>
                <div class="rounded-xl bg-surface-2 p-4">
                    <dt class="text-sm text-ink-2">Programas sem custo direto</dt>
                    <dd class="mt-1 text-3xl font-semibold">{{ $panorama->semCusto() }}</dd>
                </div>
                @if ($maiorPrograma)
                    <div class="rounded-xl bg-surface-2 p-4">
                        <dt class="text-sm text-ink-2">Maior programa</dt>
                        <dd class="mt-1 text-2xl font-semibold sm:text-3xl">{{ Formato::moedaCurta($maiorPrograma->valor) }}</dd>
                        <dd class="text-sm text-ink-2">{{ $maiorPrograma->nome }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </section>

    <div class="mx-auto max-w-6xl space-y-14 px-4 pt-12 sm:px-6">

        {{-- Como o dinheiro chega ---------------------------------------- --}}
        <section aria-labelledby="como-chega">
            <h2 id="como-chega" class="text-2xl font-semibold">Como o dinheiro chega às pessoas</h2>
            <p class="mt-2 max-w-3xl text-ink-2">
                <strong class="text-ink">{{ Formato::percentual($panorama->fracaoDireta()) }}</strong> do valor chega como dinheiro ou incentivo direto
                a famílias, estudantes, produtores e empresas. O restante paga serviços que a própria Prefeitura presta,
                como transporte escolar, merenda e máquinas agrícolas.
            </p>

            <div class="card mt-6 p-5 sm:p-6">
                <x-barra-empilhada :segmentos="$porMecanismo" inteira contexto="Total do exercício" />

                <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($porMecanismo as $item)
                        <li class="flex flex-col rounded-xl bg-surface-2 p-4">
                            <p class="flex items-center gap-2 text-sm font-semibold">
                                <span class="amostra" style="background: {{ $item['mecanismo']->corCss() }}" aria-hidden="true"></span>
                                {{ $item['mecanismo']->rotuloCurto() }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold">{{ Formato::moedaCurta($item['total']) }}</p>
                            <p class="text-sm text-ink-2">{{ Formato::percentual($item['fracao']) }} do total · {{ $item['quantidade'] }} programas</p>
                            <p class="mt-3 text-sm text-ink-2">{{ $item['mecanismo']->getDescription() }}</p>
                            <a href="{{ route('programas.index', ['tipo' => $item['mecanismo']->value]) }}" class="mt-auto pt-3 text-sm font-medium">Ver estes programas →</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Por secretaria ------------------------------------------------ --}}
        <section aria-labelledby="secretarias">
            <h2 id="secretarias" class="text-2xl font-semibold">Quanto cada secretaria aplica</h2>
            <p class="mt-2 max-w-3xl text-ink-2">O tamanho da barra mostra o valor no ano; as cores mostram como esse dinheiro chega às pessoas.</p>

            <div class="card mt-6 p-5 sm:p-6">
                <x-legenda-mecanismos class="mb-6" />

                <ul class="space-y-5">
                    @foreach ($porSecretaria as $linha)
                        @php
                            $segmentos = $porMecanismo->map(fn ($m) => [
                                'mecanismo' => $m['mecanismo'],
                                'total' => (float) $panorama->programas
                                    ->where('secretaria_id', $linha['secretaria']->id)
                                    ->where('mecanismo', $m['mecanismo'])
                                    ->sum('valor'),
                            ]);
                        @endphp
                        <li>
                            <p class="flex flex-wrap items-baseline justify-between gap-x-3 text-sm">
                                <a href="{{ route('programas.index', ['secretaria' => $linha['secretaria']->slug]) }}" class="font-semibold text-ink">{{ $linha['secretaria']->nome_curto }}</a>
                                <span class="text-ink-2">{{ $linha['quantidade'] }} programas</span>
                            </p>
                            <div class="mt-1.5 flex items-center gap-3">
                                <div class="min-w-0 flex-1">
                                    <x-barra-empilhada :segmentos="$segmentos" :proporcao="$linha['total'] / $maiorSecretaria" :contexto="$linha['secretaria']->nome_curto" />
                                </div>
                                <span class="w-28 shrink-0 text-right text-sm font-semibold tnum">{{ Formato::moedaCurta($linha['total']) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <details class="mt-6 text-sm">
                    <summary class="cursor-pointer font-medium text-link">Ver em tabela</summary>
                    <div class="mt-3 overflow-x-auto">
                        <table class="tabela">
                            <thead>
                                <tr>
                                    <th scope="col">Secretaria</th>
                                    @foreach ($porMecanismo as $m)
                                        <th scope="col" class="num">{{ $m['mecanismo']->rotuloCurto() }}</th>
                                    @endforeach
                                    <th scope="col" class="num">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($porSecretaria as $linha)
                                    <tr>
                                        <th scope="row" class="font-normal">{{ $linha['secretaria']->nome_curto }}</th>
                                        @foreach ($porMecanismo as $m)
                                            <td class="num">{{ Formato::moeda($panorama->programas->where('secretaria_id', $linha['secretaria']->id)->where('mecanismo', $m['mecanismo'])->sum('valor')) }}</td>
                                        @endforeach
                                        <td class="num font-semibold">{{ Formato::moeda($linha['total']) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th scope="row">Total</th>
                                    @foreach ($porMecanismo as $m)
                                        <td class="num font-semibold">{{ Formato::moeda($m['total']) }}</td>
                                    @endforeach
                                    <td class="num font-semibold">{{ Formato::moeda($panorama->total()) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        </section>

        {{-- Maiores programas --------------------------------------------- --}}
        <section aria-labelledby="maiores">
            <h2 id="maiores" class="text-2xl font-semibold">Os {{ $maiores->count() }} maiores programas</h2>
            <p class="mt-2 max-w-3xl text-ink-2">
                Juntos, somam {{ Formato::moedaCurta($maiores->sum('valor')) }}, ou {{ Formato::percentual($maiores->sum('valor') / ($panorama->total() ?: 1), 0) }} de tudo.
                Clique no nome para ver a ficha completa.
            </p>

            <div class="card mt-6 p-5 sm:p-6">
                <x-legenda-mecanismos class="mb-6" />
                <ol class="space-y-4">
                    @foreach ($maiores as $programa)
                        <li>
                            <p class="text-sm">
                                <a href="{{ route('programas.show', $programa) }}" class="font-semibold text-ink">{{ $programa->nome }}</a>
                                <span class="text-muted">· {{ $programa->secretaria->nome_curto }}</span>
                            </p>
                            <div class="mt-1.5 flex items-center gap-3">
                                <div class="min-w-0 flex-1">
                                    <x-barra-empilhada
                                        :segmentos="[['mecanismo' => $programa->mecanismo, 'total' => (float) $programa->valor]]"
                                        :proporcao="$programa->valor / $maiorPrograma->valor"
                                        :contexto="$programa->nome" />
                                </div>
                                <span class="w-28 shrink-0 text-right text-sm font-semibold tnum">{{ Formato::moedaCurta($programa->valor) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Para mim ------------------------------------------------------ --}}
        <section id="para-mim" aria-labelledby="para-mim-titulo" class="scroll-mt-6">
            <h2 id="para-mim-titulo" class="text-2xl font-semibold">Que programas existem para mim?</h2>
            <p class="mt-2 max-w-3xl text-ink-2">Escolha o que mais combina com você ou sua família.</p>

            <ul class="mt-6 flex flex-wrap gap-3">
                @foreach ($panorama->perfis() as $item)
                    <li>
                        <a href="{{ route('programas.index', ['perfil' => $item['perfil']->value]) }}" class="chip">
                            {{ $item['perfil']->frase() }}
                            <span class="rounded-full bg-surface-2 px-2 py-0.5 text-xs font-semibold text-ink-2">{{ $item['quantidade'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Linha do tempo ------------------------------------------------ --}}
        @php $linhaDoTempo = $panorama->linhaDoTempo(); @endphp
        @if ($linhaDoTempo->isNotEmpty())
            <section aria-labelledby="linha-tempo">
                <h2 id="linha-tempo" class="text-2xl font-semibold">Quando cada programa foi criado</h2>
                <p class="mt-2 max-w-3xl text-ink-2">
                    Ano da primeira lei de cada programa. {{ $linhaDoTempo->filter(fn ($g, $ano) => $ano >= 2022)->flatten()->count() }}
                    dos {{ $linhaDoTempo->flatten()->count() }} programas com lei informada foram criados de 2022 para cá.
                </p>

                <ol class="mt-6 border-l-2 border-line pl-6">
                    @foreach ($linhaDoTempo as $ano => $programas)
                        <li class="relative pb-6 last:pb-0">
                            <span class="absolute -left-[33px] top-1 h-4 w-4 rounded-full border-2 border-surface bg-brand" aria-hidden="true"></span>
                            <p class="font-semibold">{{ $ano }}</p>
                            <ul class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                @foreach ($programas as $programa)
                                    <li><a href="{{ route('programas.show', $programa) }}">{{ $programa->nome }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        {{-- Entenda ------------------------------------------------------- --}}
        <section aria-labelledby="entenda-titulo" class="card p-6">
            <h2 id="entenda-titulo" class="text-xl font-semibold">Antes de comparar os números</h2>
            <ul class="mt-4 grid gap-5 text-sm text-ink-2 md:grid-cols-3">
                <li>
                    <p class="font-semibold text-ink">Os valores são do ano</p>
                    <p class="mt-1">Programas que duram vários anos aparecem com o valor total dividido pelo número de anos (valor anualizado).</p>
                </li>
                <li>
                    <p class="font-semibold text-ink">Alguns programas não têm custo direto</p>
                    <p class="mt-1">Linhas de crédito do Estado e atendimentos feitos pela equipe contam como programa, mas não somam no valor.</p>
                </li>
                <li>
                    <p class="font-semibold text-ink">Pessoas não são benefícios</p>
                    <p class="mt-1">Uma família pode receber várias parcelas. Por isso mostramos quantas pessoas e quantos benefícios, separadamente.</p>
                </li>
            </ul>
            <a href="{{ route('entenda') }}" class="mt-5 inline-block font-medium">Saiba como os números são calculados →</a>
        </section>
    </div>
</x-layouts.site>
