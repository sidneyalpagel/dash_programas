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
    @php
        $milhoes = round($panorama->total() / 1_000_000, 1);
        $destaques = $panorama->destaquesAtendidos(6);
    @endphp
    <section class="heroi relative overflow-hidden text-white">
        <div class="relative mx-auto max-w-6xl px-4 pt-12 pb-14 sm:px-6 sm:pt-16 sm:pb-20">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-sm font-medium text-white/90 ring-1 ring-white/15">
                <span class="h-2 w-2 rounded-full bg-accent-soft" aria-hidden="true"></span>
                Exercício {{ $panorama->exercicio }} · Prefeitura de Santa Helena
            </p>

            <h1 class="mt-6">
                <span class="block text-xl font-medium text-white/80 sm:text-2xl">A Prefeitura destinou</span>
                <span class="mt-1 block text-[3.5rem] leading-none font-extrabold tracking-tight sm:text-8xl lg:text-9xl">
                    <x-numero-animado :valor="$milhoes" :casas="1" prefixo="R$ " />
                    <span class="text-[0.55em] font-bold text-accent-soft">{{ $milhoes < 2 ? 'milhão' : 'milhões' }}</span>
                </span>
                <span class="mt-4 block max-w-3xl text-xl leading-snug font-medium text-white/90 sm:text-2xl">
                    a <strong class="font-bold text-white">{{ $panorama->quantidade() }} programas</strong> que chegam a estudantes, famílias,
                    produtores rurais, empresas, idosos e atletas.
                </span>
            </h1>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#para-mim" class="rounded-lg bg-white px-5 py-3 font-semibold text-brand-2 no-underline shadow-lg shadow-black/20 hover:bg-[#eaf6fd]">Que programas existem para mim?</a>
                <a href="{{ route('programas.index') }}" class="rounded-lg px-5 py-3 font-semibold text-white no-underline ring-1 ring-white/40 hover:bg-white/10">Ver todos os programas</a>
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/15 backdrop-blur-sm">
                    <p class="text-5xl font-extrabold tracking-tight sm:text-6xl">
                        <x-numero-animado :valor="round($panorama->fracaoDireta() * 100, 1)" :casas="1" sufixo="%" />
                    </p>
                    <p class="mt-2 text-base font-medium text-white/90">chega direto a quem é atendido</p>
                    <p class="mt-1 text-sm text-white/70">em dinheiro, bolsa ou incentivo, sem passar por obras ou serviços.</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/15 backdrop-blur-sm">
                    <p class="text-5xl font-extrabold tracking-tight sm:text-6xl">
                        <x-numero-animado :valor="$panorama->quantidade()" />
                    </p>
                    <p class="mt-2 text-base font-medium text-white/90">programas em {{ $porSecretaria->count() }} secretarias</p>
                    <p class="mt-1 text-sm text-white/70">{{ $panorama->semCusto() }} deles funcionam sem custo direto para o Município.</p>
                </div>
                @if ($maiorPrograma)
                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/15 backdrop-blur-sm">
                        <p class="text-5xl font-extrabold tracking-tight sm:text-6xl">
                            <x-numero-animado :valor="round($maiorPrograma->valor / 1_000_000, 1)" :casas="1" prefixo="R$ " /><span class="text-2xl font-bold sm:text-3xl"> mi</span>
                        </p>
                        <p class="mt-2 text-base font-medium text-white/90">no maior programa</p>
                        <p class="mt-1 text-sm text-white/70">
                            <a href="{{ route('programas.show', $maiorPrograma) }}" class="text-white underline decoration-white/40 underline-offset-2 hover:decoration-white">{{ $maiorPrograma->nome }}</a>
                        </p>
                    </div>
                @endif
            </div>
        </div>
        <div class="relative h-1 bg-accent" aria-hidden="true"></div>
    </section>

    {{-- Quem é atendido --------------------------------------------------- --}}
    @if ($destaques->isNotEmpty())
        <section aria-labelledby="atendidos" class="border-b border-line bg-surface">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                <h2 id="atendidos" class="text-2xl font-semibold sm:text-3xl">Por trás dos números, pessoas</h2>
                <p class="mt-2 max-w-3xl text-ink-2">Alguns dos públicos atendidos em {{ $panorama->exercicio }}.</p>

                <ul class="mt-8 grid gap-x-8 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($destaques as $programa)
                        <li class="border-l-4 pl-4" style="border-color: {{ $programa->mecanismo->corCss() }}">
                            <p class="text-4xl leading-none font-extrabold tracking-tight sm:text-5xl">
                                <x-numero-animado :valor="$programa->qtd_atendidos" />
                            </p>
                            <p class="mt-1 text-lg font-semibold text-ink-2">{{ $programa->unidade_atendidos }}</p>
                            <p class="mt-1 text-sm text-muted">
                                <a href="{{ route('programas.show', $programa) }}">{{ $programa->nome }}</a> · {{ $programa->secretaria->nome_curto }}
                            </p>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-8 text-sm text-muted">
                    Cada número é de um programa. Uma mesma pessoa pode participar de vários programas; por isso os números não são somados.
                </p>
            </div>
        </section>
    @endif

    <div class="mx-auto max-w-6xl space-y-14 px-4 pt-12 sm:px-6">

        {{-- Como o dinheiro chega ---------------------------------------- --}}
        <section aria-labelledby="como-chega">
            <h2 id="como-chega" class="text-2xl font-semibold">Como o dinheiro chega às pessoas</h2>
            <p class="mt-2 max-w-3xl text-ink-2">
                <strong class="text-ink">{{ Formato::percentual($panorama->fracaoDireta()) }}</strong> do valor chega como dinheiro ou incentivo direto
                a famílias, estudantes, atletas, produtores, empresas e entidades esportivas. O restante paga serviços e compras
                da própria Prefeitura, como transporte escolar, merenda e horas-máquina.
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
                    <p class="mt-1">Alguns, como as linhas de crédito do Estado, não geram gasto direto ao Município: contam como programa, mas não somam no valor.</p>
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
