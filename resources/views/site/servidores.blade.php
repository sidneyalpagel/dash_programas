@php
    use App\Support\Formato;

    /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, \App\Models\PagamentoServidor>> $porAno */
    $destaques = $porAno->take(3);
@endphp

<x-layouts.site titulo="Pagamentos a servidores" descricao="Direitos e verbas pagos aos servidores municipais de Santa Helena, por ano.">
    <x-cabecalho-pagina
        titulo="Pagamentos a servidores"
        sobretitulo="Recursos humanos"
        subtitulo="Direitos e verbas pagos aos servidores municipais. São despesas de pessoal e não fazem parte dos programas municipais nem dos seus totais.">
        @if ($destaques->isNotEmpty())
            <div class="mt-10 grid gap-4 sm:grid-cols-3">
                @foreach ($destaques as $ano => $pagamentos)
                    <x-cartao-heroi
                        :rotulo="'pagos em '.$ano.($pagamentos->first()->periodo ? ' ('.$pagamentos->first()->periodo.')' : '')"
                        :detalhe="$pagamentos->count().' '.($pagamentos->count() === 1 ? 'verba' : 'verbas')">
                        <x-moeda-animada :valor="$pagamentos->sum('valor')" />
                    </x-cartao-heroi>
                @endforeach
            </div>
        @endif
    </x-cabecalho-pagina>

    <div class="mx-auto max-w-6xl px-4 pt-12 sm:px-6">
        @forelse ($porAno as $ano => $pagamentos)
            <section class="mt-2 mb-14" aria-labelledby="ano-{{ $ano }}">
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                    <h2 id="ano-{{ $ano }}" class="text-2xl font-bold sm:text-3xl">
                        {{ $ano }}
                        @if ($pagamentos->first()->periodo)
                            <span class="text-lg font-medium text-ink-2">· {{ $pagamentos->first()->periodo }}</span>
                        @endif
                    </h2>
                    <p class="text-ink-2">Total: <strong class="text-ink">{{ Formato::moeda($pagamentos->sum('valor')) }}</strong></p>
                </div>

                <ul class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($pagamentos as $pagamento)
                        <li class="card flex h-full flex-col overflow-hidden">
                            <span class="block h-1.5 bg-brand" aria-hidden="true"></span>
                            <div class="flex flex-1 flex-col p-6">
                                <h3 class="text-lg leading-snug font-bold">{{ $pagamento->verba }}</h3>
                                @if ($pagamento->descricao)
                                    <p class="mt-2 text-sm text-ink-2">{{ $pagamento->descricao }}</p>
                                @endif
                                @if ($pagamento->base_legal)
                                    <p class="mt-2 text-xs text-muted">{{ $pagamento->base_legal }}</p>
                                @endif
                                <div class="mt-auto pt-5">
                                    <div class="grid grid-cols-2 gap-3 border-t border-line pt-4">
                                        <div>
                                            <p class="text-xs text-muted">Valor no ano</p>
                                            <p class="text-xl font-extrabold tracking-tight whitespace-nowrap">{{ Formato::moedaCurta($pagamento->valor) }}</p>
                                            <p class="text-xs text-ink-2 tnum">{{ Formato::moeda($pagamento->valor) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-muted">Servidores</p>
                                            <p class="text-xl font-extrabold tracking-tight">{{ $pagamento->qtd_servidores !== null ? Formato::numero($pagamento->qtd_servidores) : '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="card p-6 text-ink-2">Ainda não há pagamentos a servidores informados.</p>
        @endforelse

        <section class="rounded-xl border border-line bg-surface-2 p-5 text-sm text-ink-2" aria-label="Como ler estes números">
            <ul class="list-disc space-y-2 pl-5">
                <li><strong class="text-ink">Os servidores não são somados entre as verbas:</strong> a mesma pessoa pode ter recebido mais de uma verba no ano.</li>
                <li><strong class="text-ink">Valores retroativos</strong> referem-se a direitos de anos anteriores, pagos no ano indicado.</li>
                <li>Os valores vêm do levantamento do setor de Recursos Humanos. A folha de pagamento completa está no Portal da Transparência do Município.</li>
            </ul>
        </section>
    </div>
</x-layouts.site>
