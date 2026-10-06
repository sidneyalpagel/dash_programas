@php
    use App\Enums\FonteRecurso;
    use App\Enums\TipoValor;
    use App\Support\Formato;

    $porAtendido = $programa->valorPorAtendido();
    $fracaoTotal = $programa->temCustoDireto() && $panorama->total() > 0 ? $programa->valor / $panorama->total() : null;
    $posicao = $panorama->posicao($programa);
    $totalComCusto = $panorama->comCusto()->count();
    $rotuloMedia = $programa->qtd_atendidos ? 'em média, por atendido, no ano' : 'em média, por benefício pago';
    $quantidadeMedia = $programa->qtd_atendidos ?: $programa->qtd_beneficios;
    $unidadeMedia = $programa->qtd_atendidos ? ($programa->unidade_atendidos ?: 'atendidos') : 'benefícios pagos';
    $secretaria = $programa->secretaria;
@endphp

<x-layouts.site :titulo="$programa->nome" :descricao="$programa->descricao" :panorama="$panorama">
    <x-cabecalho-pagina :titulo="$programa->nome">
        <x-slot:trilha>
            <a href="{{ route('programas.index') }}" class="text-white/85 hover:text-white">Programas</a>
            <span aria-hidden="true">›</span>
            <a href="{{ route('programas.index', ['secretaria' => $secretaria->slug]) }}" class="text-white/85 hover:text-white">{{ $secretaria->nome_curto }}</a>
        </x-slot:trilha>

        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
            <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 font-semibold text-[#15171c]">
                <span class="amostra" style="background: {{ $programa->mecanismo->corCss() }}" aria-hidden="true"></span>
                {{ $programa->mecanismo->getLabel() }}
            </span>
            @if ($programa->grupo)
                <a href="{{ route('programas.index', ['grupo' => $programa->grupo]) }}" class="rounded-full px-3 py-1 font-medium text-white no-underline ring-1 ring-white/30 hover:bg-white/10">
                    Parte do {{ $programa->grupo }}
                </a>
            @endif
        </div>

        @if ($programa->descricao)
            <p class="mt-5 max-w-3xl text-lg leading-snug text-white/85 sm:text-xl">{{ $programa->descricao }}</p>
        @endif

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-cartao-heroi :rotulo="($programa->semCustoDireto() ? 'para o Município em ' : 'destinados em ').$programa->exercicio"
                            :detalhe="match (true) {
                                $programa->semCustoDireto() => $programa->explicacaoSemCusto(),
                                $programa->valorPendente() => 'A secretaria responsável ainda vai informar o valor.',
                                default => Formato::moeda($programa->valor),
                            }">
                @if ($programa->semCustoDireto())
                    <span class="text-3xl sm:text-4xl">Sem custo direto</span>
                @elseif ($programa->valorPendente())
                    <span class="text-3xl">A informar</span>
                @elseif ($programa->valor >= 1_000_000)
                    <x-numero-animado :valor="round($programa->valor / 1_000_000, 1)" :casas="1" prefixo="R$ " /><span class="text-2xl font-bold"> mi</span>
                @else
                    <x-numero-animado :valor="round($programa->valor / 1_000)" prefixo="R$ " /><span class="text-2xl font-bold"> mil</span>
                @endif
            </x-cartao-heroi>

            <x-cartao-heroi :rotulo="$programa->qtd_atendidos !== null ? ($programa->unidade_atendidos ?: 'atendidos') : 'benefícios pagos'"
                            :detalhe="$programa->detalhe_atendidos ?? ($programa->qtd_atendidos !== null && $programa->qtd_beneficios !== null ? Formato::numero($programa->qtd_beneficios).' benefícios pagos' : null)">
                @if ($programa->qtd_atendidos !== null || $programa->qtd_beneficios !== null)
                    <x-numero-animado :valor="$programa->qtd_atendidos ?? $programa->qtd_beneficios" />
                @else
                    <span class="text-3xl">A informar</span>
                @endif
            </x-cartao-heroi>

            @if ($porAtendido && $quantidadeMedia > 1)
                <x-cartao-heroi :rotulo="$rotuloMedia" :detalhe="'Valor do ano dividido por '.Formato::numero($quantidadeMedia).' '.$unidadeMedia.'. É uma média: cada um pode receber valores diferentes.'">
                    <x-numero-animado :valor="round($porAtendido)" prefixo="R$ " />
                </x-cartao-heroi>
            @endif

            @if ($posicao)
                <x-cartao-heroi :rotulo="'maior programa entre '.$totalComCusto.' com custo'"
                                :detalhe="$fracaoTotal !== null ? ($fracaoTotal < 0.001 ? 'menos de 0,1%' : Formato::percentual($fracaoTotal)).' de tudo o que o Município destina aos programas' : null">
                    <x-numero-animado :valor="$posicao" sufixo="º" />
                </x-cartao-heroi>
            @endif
        </div>
    </x-cabecalho-pagina>

    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Como participar: o que o cidadão mais procura --}}
            <section class="card overflow-hidden lg:order-2 lg:self-start" aria-labelledby="participar">
                <div class="bg-brand px-6 py-4 text-white">
                    <h2 id="participar" class="text-xl font-bold">Como participar</h2>
                </div>
                <div class="p-6">
                    @if ($programa->como_participar)
                        <p class="text-lg leading-relaxed whitespace-pre-line">{{ $programa->como_participar }}</p>
                    @else
                        <p class="text-lg">Procure a <strong>{{ $secretaria->nome }}</strong>.</p>
                    @endif

                    @if ($secretaria->endereco || $secretaria->telefone || $secretaria->email)
                        <ul class="mt-5 space-y-2 border-t border-line pt-5 text-ink-2">
                            @if ($secretaria->endereco)
                                <li><span class="font-semibold text-ink">Endereço:</span> {{ $secretaria->endereco }}</li>
                            @endif
                            @if ($secretaria->telefone)
                                <li><span class="font-semibold text-ink">Telefone:</span> <a href="tel:{{ preg_replace('/\D/', '', $secretaria->telefone) }}">{{ $secretaria->telefone }}</a></li>
                            @endif
                            @if ($secretaria->email)
                                <li><span class="font-semibold text-ink">E-mail:</span> <a href="mailto:{{ $secretaria->email }}">{{ $secretaria->email }}</a></li>
                            @endif
                        </ul>
                    @endif
                </div>
            </section>

            <div class="space-y-6 lg:col-span-2">
                @if (! $programa->descricao)
                    <section class="card p-6">
                        <h2 class="text-xl font-bold">O que é</h2>
                        <p class="mt-2 text-ink-2">A secretaria responsável ainda está preparando a descrição deste programa.</p>
                    </section>
                @endif

                <section class="card p-6" aria-labelledby="para-quem">
                    <h2 id="para-quem" class="text-xl font-bold">Para quem é</h2>
                    @if ($programa->perfis())
                        <ul class="mt-4 flex flex-wrap gap-2">
                            @foreach ($programa->perfis() as $perfil)
                                <li><a href="{{ route('programas.index', ['perfil' => $perfil->value]) }}" class="chip">{{ $perfil->getLabel() }}</a></li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-2 text-ink-2">Em atualização pela secretaria.</p>
                    @endif
                </section>

                <section class="card p-6" aria-labelledby="como-chega">
                    <h2 id="como-chega" class="text-xl font-bold">{{ $programa->semCustoDireto() ? 'Como funciona' : 'Como o dinheiro chega' }}</h2>
                    <p class="mt-3 flex items-start gap-3 text-ink-2">
                        <span class="amostra mt-1.5" style="background: {{ $programa->mecanismo->corCss() }}" aria-hidden="true"></span>
                        <span><strong class="text-ink">{{ $programa->mecanismo->getLabel() }}.</strong> {{ $programa->semCustoDireto() ? $programa->explicacaoSemCusto() : $programa->mecanismo->explicacaoNaFicha() }}</span>
                    </p>
                    @if ($programa->tipo_valor === TipoValor::Anualizado)
                        <p class="mt-4 rounded-lg bg-warn-bg p-3 text-sm text-warn-ink">
                            <strong>Valor anualizado:</strong> o programa tem {{ Formato::moeda($programa->valor_total_vigencia) }} para {{ $programa->vigencia_anos }} anos.
                            Aqui mostramos o total dividido por {{ $programa->vigencia_anos }}, para comparar com os demais.
                        </p>
                    @endif
                    @if (! $programa->semCustoDireto() && $programa->fonte_recurso !== FonteRecurso::NaoInformado)
                        <p class="mt-4 text-sm text-ink-2">Origem do dinheiro: <strong class="text-ink">{{ $programa->fonte_recurso->getLabel() }}</strong></p>
                    @endif
                </section>

                <section class="card p-6" aria-labelledby="base-legal">
                    <h2 id="base-legal" class="text-xl font-bold">Base legal</h2>
                    @if ($programa->bases_legais)
                        <ul class="mt-3 space-y-2">
                            @foreach ($programa->bases_legais as $lei)
                                @php $texto = trim(($lei['tipo'] ?? 'Lei').' nº '.($lei['numero'] ?? '').'/'.($lei['ano'] ?? '')); @endphp
                                <li class="flex items-center gap-2">
                                    <span class="text-muted" aria-hidden="true">§</span>
                                    @if (filled($lei['link'] ?? null))
                                        <a href="{{ $lei['link'] }}" rel="noopener" target="_blank">{{ $texto }} ↗</a>
                                    @else
                                        <span>{{ $texto }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if ($programa->ano_criacao)
                            <p class="mt-3 text-sm text-muted">Programa criado em {{ $programa->ano_criacao }}.</p>
                        @endif
                    @else
                        <p class="mt-2 text-ink-2">Em atualização pela secretaria.</p>
                    @endif
                </section>

                @if ($programa->nota_publica)
                    <section class="rounded-xl border border-line bg-surface-2 p-5">
                        <h2 class="text-sm font-bold">Nota</h2>
                        <p class="mt-1 text-sm text-ink-2">{{ $programa->nota_publica }}</p>
                    </section>
                @endif

                <p class="text-sm text-muted">Responsável: {{ $secretaria->nome }} · Informações de {{ $programa->exercicio }}.</p>
            </div>
        </div>

        @if ($relacionados->isNotEmpty())
            <section class="mt-16" aria-labelledby="relacionados">
                <h2 id="relacionados" class="text-2xl font-bold">
                    {{ $programa->grupo ? 'Outros benefícios do '.$programa->grupo : 'Outros programas da '.$secretaria->nome }}
                </h2>
                <ul class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relacionados as $relacionado)
                        <li><x-programa-card :programa="$relacionado" /></li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-layouts.site>
