@php
    use App\Enums\Mecanismo;
    use App\Enums\TipoValor;
    use App\Support\Formato;

    $semCusto = $panorama->programas->reject->temCustoDireto();
    $anualizados = $panorama->programas->where('tipo_valor', TipoValor::Anualizado);

    $glossario = [
        'Exercício' => 'O ano a que se referem os valores. Os números deste site são do exercício '.$panorama->exercicio.'.',
        'Valor no ano' => 'Quanto o programa custa ao Município no exercício.',
        'Valor anualizado' => 'Usado em programas que duram vários anos. O valor total é dividido pelo número de anos, para poder ser comparado com os demais.',
        'Sem custo direto' => 'O programa existe e atende pessoas, mas não gera gasto direto para a Prefeitura. Exemplo: linhas de crédito do Governo do Estado.',
        'Base legal' => 'A lei, o decreto ou o convênio que cria o programa e define suas regras.',
        'Atendidos e benefícios' => 'Atendidos são pessoas, famílias, empresas ou entidades. Benefícios são os pagamentos ou auxílios concedidos: uma mesma família pode receber mais de um.',
        'Média por atendido' => 'O valor do ano dividido pela quantidade atendida. Serve para ter uma ideia de escala; não significa que cada pessoa recebeu exatamente esse valor.',
    ];
@endphp

<x-layouts.site titulo="Entenda os números" :panorama="$panorama">
    <div class="mx-auto max-w-3xl px-4 pt-10 sm:px-6">
        <h1 class="text-3xl font-semibold">Entenda os números</h1>
        <p class="mt-3 text-lg text-ink-2">
            Este site reúne os programas das secretarias municipais de Santa Helena. Cada secretaria cadastra seus programas,
            e as informações são revisadas antes de serem publicadas.
        </p>

        <section class="mt-10" aria-labelledby="tipos">
            <h2 id="tipos" class="text-2xl font-semibold">Os quatro jeitos de o dinheiro chegar às pessoas</h2>
            <p class="mt-2 text-ink-2">Cada programa é classificado em um destes tipos. A cor é sempre a mesma em todo o site.</p>
            <ul class="mt-5 space-y-4">
                @foreach ($panorama->porMecanismo() as $item)
                    <li class="card flex gap-4 p-5">
                        <span class="amostra mt-1.5" style="background: {{ $item['mecanismo']->corCss() }}" aria-hidden="true"></span>
                        <div>
                            <p class="font-semibold">{{ $item['mecanismo']->getLabel() }}</p>
                            <p class="mt-1 text-ink-2">{{ $item['mecanismo']->getDescription() }}</p>
                            @if ($item['exemplos']->isNotEmpty())
                                <p class="mt-2 text-sm text-muted">Exemplos: {{ $item['exemplos']->join(', ', ' e ') }}.</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="mt-12" aria-labelledby="glossario">
            <h2 id="glossario" class="text-2xl font-semibold">Glossário</h2>
            <dl class="mt-5 divide-y divide-line border-y border-line">
                @foreach ($glossario as $termo => $explicacao)
                    <div class="grid gap-1 py-4 sm:grid-cols-3 sm:gap-6">
                        <dt class="font-semibold">{{ $termo }}</dt>
                        <dd class="text-ink-2 sm:col-span-2">{{ $explicacao }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="mt-12" aria-labelledby="calculo">
            <h2 id="calculo" class="text-2xl font-semibold">Como o total é calculado</h2>
            <ul class="mt-4 list-disc space-y-3 pl-5 text-ink-2">
                <li>
                    O total de <strong class="text-ink">{{ Formato::moeda($panorama->total()) }}</strong> é a soma do valor no ano dos
                    {{ $panorama->quantidade() - $semCusto->count() }} programas com custo direto.
                </li>
                @if ($semCusto->isNotEmpty())
                    <li>
                        {{ $semCusto->count() }} programas não têm custo direto e entram na contagem, mas não no valor:
                        {{ $semCusto->pluck('nome')->join(', ', ' e ') }}.
                    </li>
                @endif
                @if ($anualizados->isNotEmpty())
                    <li>
                        Valores anualizados:
                        @foreach ($anualizados as $p)
                            {{ $p->nome }} ({{ Formato::moeda($p->valor_total_vigencia) }} ÷ {{ $p->vigencia_anos }} anos){{ $loop->last ? '.' : ';' }}
                        @endforeach
                    </li>
                @endif
                <li>
                    Os valores incluem todas as fontes de recurso informadas pelas secretarias (municipal, estadual, federal e convênios),
                    exceto as linhas de crédito sem custo à Prefeitura.
                </li>
                <li>
                    Os dados retratam o exercício atual. Para comparar com anos anteriores, consulte as Leis Orçamentárias Anuais (LOA)
                    no Portal da Transparência do Município.
                </li>
            </ul>
        </section>
    </div>
</x-layouts.site>
