@php
    use App\Enums\TipoValor;
    use App\Support\Formato;

    $semCusto = $panorama->programas->filter->semCustoDireto();
    $comCusto = $panorama->comCusto();
    $anualizados = $panorama->programas->where('tipo_valor', TipoValor::Anualizado);

    $glossario = [
        'Exercício' => 'O ano a que se referem os valores. Os números deste site são do exercício '.$panorama->exercicio.'.',
        'Valor no ano' => 'Quanto o programa custa ao Município no exercício.',
        'Valor anualizado' => 'Usado em programas que duram vários anos. O valor total é dividido pelo número de anos, para poder ser comparado com os demais.',
        'Sem custo direto' => 'O programa existe e atende pessoas, mas não gera gasto direto para a Prefeitura. Exemplo: linhas de crédito do Governo do Estado.',
        'Base legal' => 'A lei, o decreto ou o convênio que cria o programa e define suas regras.',
        'Atendidos e benefícios' => 'Atendidos são pessoas, famílias, empresas ou entidades. Benefícios são os pagamentos ou auxílios concedidos: uma mesma família pode receber mais de um.',
        'Média por atendido' => 'O valor do ano dividido pela quantidade atendida. Serve para ter uma ideia de escala; não significa que cada pessoa recebeu exatamente esse valor.',
        'Fonte do recurso' => 'De onde vem o dinheiro: do próprio Município, do Estado, da União ou de convênios entre eles.',
    ];
@endphp

<x-layouts.site titulo="Entenda os números" :panorama="$panorama">
    <x-cabecalho-pagina
        titulo="Entenda os números"
        sobretitulo="Metodologia e glossário"
        subtitulo="Como os valores são calculados, o que cada termo significa e por que alguns números não podem ser somados.">
        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            <x-cartao-heroi rotulo="é o total do exercício" :detalhe="'Soma do valor no ano de '.$comCusto->count().' programas com custo direto.'">
                <x-moeda-animada :valor="$panorama->total()" />
            </x-cartao-heroi>
            <x-cartao-heroi rotulo="programas sem custo direto" detalhe="Contam como programa, mas não entram na soma do valor.">
                <x-numero-animado :valor="$semCusto->count()" />
            </x-cartao-heroi>
            <x-cartao-heroi :rotulo="$anualizados->count() === 1 ? 'programa com valor anualizado' : 'programas com valor anualizado'" detalhe="Duram vários anos; o total é dividido pela vigência.">
                <x-numero-animado :valor="$anualizados->count()" />
            </x-cartao-heroi>
        </div>
    </x-cabecalho-pagina>

    <div class="mx-auto max-w-6xl px-4 pt-12 sm:px-6">
        {{-- Os quatro tipos --}}
        <section aria-labelledby="tipos">
            <h2 id="tipos" class="text-2xl font-bold sm:text-3xl">Os quatro jeitos de o dinheiro chegar às pessoas</h2>
            <p class="mt-2 max-w-3xl text-ink-2">Cada programa é classificado em um destes tipos. A cor é sempre a mesma em todo o site.</p>

            <div class="card mt-6 p-5 sm:p-6">
                <x-barra-empilhada :segmentos="$panorama->porMecanismo()" inteira contexto="Total do exercício" />
            </div>

            <ul class="mt-5 grid gap-5 sm:grid-cols-2">
                @foreach ($panorama->porMecanismo() as $item)
                    <li class="card overflow-hidden">
                        <span class="block h-1.5" style="background: {{ $item['mecanismo']->corCss() }}" aria-hidden="true"></span>
                        <div class="p-6">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                <p class="text-lg font-bold">{{ $item['mecanismo']->getLabel() }}</p>
                                <p class="text-sm text-ink-2">{{ $item['quantidade'] }} programas</p>
                            </div>
                            <p class="mt-3 flex items-baseline gap-3">
                                <span class="text-5xl leading-none font-extrabold tracking-tight">{{ Formato::percentual($item['fracao']) }}</span>
                                <span class="text-lg font-semibold text-ink-2">{{ Formato::moedaCurta($item['total']) }}</span>
                            </p>
                            <p class="mt-3 text-ink-2">{{ $item['mecanismo']->getDescription() }}</p>
                            @if ($item['exemplos']->isNotEmpty())
                                <p class="mt-3 text-sm text-muted">Exemplos: {{ $item['exemplos']->join(', ', ' e ') }}.</p>
                            @endif
                            <a href="{{ $panorama->rota('programas.index', ['tipo' => $item['mecanismo']->value]) }}" class="mt-4 inline-block text-sm font-semibold">Ver estes programas →</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Como o total é calculado --}}
        <section class="mt-16" aria-labelledby="calculo">
            <h2 id="calculo" class="text-2xl font-bold sm:text-3xl">Como chegamos a {{ Formato::moedaCurta($panorama->total()) }}</h2>

            <ol class="mt-6 grid gap-5 lg:grid-cols-3">
                <li class="card p-6">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-brand text-lg font-bold text-white" aria-hidden="true">1</span>
                    <p class="mt-4 text-lg font-bold">Somamos o valor do ano</p>
                    <p class="mt-2 text-ink-2">
                        Cada um dos {{ $comCusto->count() }} programas com custo direto entra com o seu valor no exercício.
                        O resultado é <strong class="text-ink">{{ Formato::moeda($panorama->total()) }}</strong>.
                    </p>
                </li>
                <li class="card p-6">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-brand text-lg font-bold text-white" aria-hidden="true">2</span>
                    <p class="mt-4 text-lg font-bold">Dividimos os programas longos</p>
                    <p class="mt-2 text-ink-2">
                        @if ($anualizados->isNotEmpty())
                            @foreach ($anualizados as $p)
                                {{ $p->nome }}: {{ Formato::moeda($p->valor_total_vigencia) }} ÷ {{ $p->vigencia_anos }} anos = <strong class="text-ink">{{ Formato::moeda($p->valor) }}</strong>{{ $loop->last ? '.' : ';' }}
                            @endforeach
                        @else
                            Nenhum programa deste exercício precisou ser anualizado.
                        @endif
                    </p>
                </li>
                <li class="card p-6">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-brand text-lg font-bold text-white" aria-hidden="true">3</span>
                    <p class="mt-4 text-lg font-bold">Contamos, sem somar, os sem custo</p>
                    <p class="mt-2 text-ink-2">
                        @if ($semCusto->isNotEmpty())
                            {{ $semCusto->pluck('nome')->join(', ', ' e ') }} atendem pessoas, mas não geram gasto direto ao Município.
                        @else
                            Todos os programas deste exercício têm custo direto.
                        @endif
                    </p>
                </li>
            </ol>

            <div class="mt-5 rounded-xl border border-line bg-surface-2 p-5 text-sm text-ink-2">
                Os valores incluem todas as fontes de recurso informadas pelas secretarias (municipal, estadual, federal e convênios).
                Os dados retratam o exercício atual; para comparar com anos anteriores, consulte as Leis Orçamentárias Anuais (LOA)
                no Portal da Transparência do Município.
            </div>
        </section>

        {{-- Glossário --}}
        <section class="mt-16" aria-labelledby="glossario">
            <h2 id="glossario" class="text-2xl font-bold sm:text-3xl">Glossário</h2>
            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                @foreach ($glossario as $termo => $explicacao)
                    <div class="card p-5">
                        <dt class="text-lg font-bold">{{ $termo }}</dt>
                        <dd class="mt-1 text-ink-2">{{ $explicacao }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </div>
</x-layouts.site>
