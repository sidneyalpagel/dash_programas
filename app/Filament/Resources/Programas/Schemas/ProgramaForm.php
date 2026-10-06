<?php

namespace App\Filament\Resources\Programas\Schemas;

use App\Enums\FonteRecurso;
use App\Enums\Mecanismo;
use App\Enums\PublicoAlvo;
use App\Enums\TipoValor;
use App\Models\Programa;
use App\Support\Formato;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class ProgramaForm
{
    public const TIPOS_BASE_LEGAL = [
        'Lei Municipal' => 'Lei Municipal',
        'Lei Complementar Municipal' => 'Lei Complementar Municipal',
        'Decreto Municipal' => 'Decreto Municipal',
        'Lei Estadual' => 'Lei Estadual',
        'Lei Federal' => 'Lei Federal',
        'Convênio' => 'Convênio',
        'Termo de Fomento' => 'Termo de Fomento',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Callout::make('Este programa tem alterações aguardando revisão')
                    ->description('O formulário mostra a versão que você enviou. O site continua exibindo a versão publicada até o administrador aprovar.')
                    ->warning()
                    ->visible(fn (?Programa $record) => $record?->temAlteracaoPendente() && ! auth()->user()->isAdmin()),

                Callout::make('Há alterações propostas pela secretaria')
                    ->description('O formulário mostra a versão publicada. Use o botão "Revisar alterações" no topo da página para comparar e aprovar.')
                    ->warning()
                    ->visible(fn (?Programa $record) => $record?->temAlteracaoPendente() && auth()->user()->isAdmin()),

                Section::make('1. Identificação')
                    ->description('Nome do programa e como ele funciona.')
                    ->columns(2)
                    ->schema([
                        Select::make('secretaria_id')
                            ->label('Secretaria')
                            ->relationship('secretaria', 'nome')
                            ->required()
                            ->visible(fn () => auth()->user()->isAdmin()),
                        TextInput::make('exercicio')
                            ->label('Ano (exercício)')
                            ->helperText(fn (string $operation) => $operation === 'create'
                                ? 'Ano a que se referem os valores e a quantidade de atendidos. Não poderá ser alterado depois.'
                                : 'Não pode ser alterado. Para outro ano, use "Copiar para outro exercício".')
                            ->integer()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->default(now()->year)
                            ->required()
                            // Mudar o ano de um registro apagaria os dados do ano original.
                            ->disabledOn('edit')
                            ->live(onBlur: true),
                        TextInput::make('nome')
                            ->label('Nome do programa')
                            ->helperText('Use o nome pelo qual o cidadão conhece o programa.')
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                table: 'programas',
                                column: 'nome',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get, ?Programa $record) => $rule
                                    ->where('exercicio', $record?->exercicio ?? (int) $get('exercicio'))
                                    ->where('secretaria_id', $record?->secretaria_id
                                        ?? ($get('secretaria_id') ?: auth()->user()->secretaria_id)),
                            )
                            ->validationMessages(['unique' => 'Esta secretaria já tem um programa com este nome neste exercício.'])
                            ->columnSpanFull(),
                        TextInput::make('grupo')
                            ->label('Faz parte de um programa maior?')
                            ->helperText('Opcional. Ex.: "Renda Santa Helena" agrupa Progredir, Gerar, Auxílio Gás e outros.')
                            ->datalist(fn () => Programa::query()->whereNotNull('grupo')->distinct()->orderBy('grupo')->pluck('grupo')->all())
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Radio::make('mecanismo')
                            ->label('Como o recurso chega ao cidadão?')
                            ->options(Mecanismo::class)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('2. Explique para o cidadão')
                    ->description('Escreva como se estivesse explicando para um vizinho: frases curtas, sem siglas e sem "juridiquês".')
                    ->schema([
                        Textarea::make('descricao')
                            ->label('O que é o programa?')
                            ->placeholder('Ex.: A Prefeitura paga parte dos juros do financiamento de quem vai construir uma granja de suínos ou aves.')
                            ->rows(4)
                            ->maxLength(2000),
                        Textarea::make('como_participar')
                            ->label('Quem pode participar e como?')
                            ->placeholder('Ex.: Produtores rurais com DAP/CAF ativo. Procure a Secretaria de Agricultura com RG, CPF e o projeto da obra.')
                            ->rows(4)
                            ->maxLength(2000),
                        CheckboxList::make('publico_alvo')
                            ->label('Para quem é este programa?')
                            ->helperText('Marque todos os perfis atendidos. É assim que o cidadão encontra o programa no filtro "Que programas existem para mim?".')
                            ->options(PublicoAlvo::class)
                            ->columns(3)
                            ->required(),
                    ]),

                Section::make('3. Base legal')
                    ->description('Leis, decretos ou convênios que criam ou regulamentam o programa.')
                    ->schema([
                        Repeater::make('bases_legais')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('tipo')
                                    ->label('Tipo')
                                    ->options(self::TIPOS_BASE_LEGAL)
                                    ->default('Lei Municipal')
                                    ->required(),
                                TextInput::make('numero')
                                    ->label('Número')
                                    ->placeholder('3.339')
                                    ->required()
                                    ->maxLength(30),
                                TextInput::make('ano')
                                    ->label('Ano')
                                    ->integer()
                                    ->minValue(1950)
                                    ->maxValue(2100)
                                    ->required(),
                                TextInput::make('link')
                                    ->label('Link para o texto (opcional)')
                                    ->url()
                                    ->placeholder('https://leismunicipais.com.br/...')
                                    ->maxLength(500),
                            ])
                            ->columns(4)
                            ->addActionLabel('Adicionar lei ou convênio')
                            ->itemLabel(fn (array $state) => trim(($state['tipo'] ?? '').' '.($state['numero'] ?? '').(filled($state['ano'] ?? null) ? '/'.$state['ano'] : '')) ?: null)
                            ->defaultItems(0)
                            ->reorderable(false),
                        TextInput::make('ano_criacao')
                            ->label('Ano de criação do programa')
                            ->helperText('Ano da primeira lei. Aparece na linha do tempo do site.')
                            ->integer()
                            ->minValue(1950)
                            ->maxValue(2100),
                    ]),

                Section::make('4. Quem foi atendido')
                    ->description('Separe pessoas de benefícios: uma família pode receber várias parcelas no ano.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('qtd_atendidos')
                            ->label('Quantidade de atendidos')
                            ->integer()
                            ->minValue(0),
                        TextInput::make('unidade_atendidos')
                            ->label('Atendidos são...')
                            ->placeholder('famílias, estudantes, empresas...')
                            ->datalist(['pessoas', 'famílias', 'estudantes', 'produtores', 'empresas', 'atletas', 'idosos', 'mulheres', 'artistas', 'associações', 'crianças e adolescentes'])
                            ->requiredWith('qtd_atendidos')
                            ->maxLength(60),
                        TextInput::make('qtd_beneficios')
                            ->label('Quantidade de benefícios pagos')
                            ->helperText('Opcional. Use quando o programa paga parcelas ou auxílios avulsos.')
                            ->integer()
                            ->minValue(0),
                        TextInput::make('detalhe_atendidos')
                            ->label('Detalhamento (opcional)')
                            ->placeholder('Ex.: 1.115 da rede municipal + 1.319 da rede estadual')
                            ->maxLength(255),
                    ]),

                Section::make('5. Quanto custa')
                    ->columns(2)
                    ->schema([
                        Radio::make('tipo_valor')
                            ->label('Tipo de valor')
                            ->options(TipoValor::class)
                            ->default(TipoValor::Anual)
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        self::campoMoeda('valor')
                            ->label('Valor no ano')
                            ->visible(fn (Get $get) => self::tipo($get) === TipoValor::Anual)
                            ->required(fn (Get $get) => self::tipo($get) === TipoValor::Anual),
                        self::campoMoeda('valor_total_vigencia')
                            ->label('Valor total do programa')
                            ->live(onBlur: true)
                            ->visible(fn (Get $get) => self::tipo($get) === TipoValor::Anualizado)
                            ->required(fn (Get $get) => self::tipo($get) === TipoValor::Anualizado),
                        TextInput::make('vigencia_anos')
                            ->label('Vigência (anos)')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(50)
                            ->live(onBlur: true)
                            ->visible(fn (Get $get) => self::tipo($get) === TipoValor::Anualizado)
                            ->required(fn (Get $get) => self::tipo($get) === TipoValor::Anualizado),
                        Text::make(function (Get $get) {
                            $total = Formato::lerMoeda($get('valor_total_vigencia'));
                            $anos = (int) $get('vigencia_anos');

                            return $total && $anos
                                ? new HtmlString('Valor anual que será exibido: <strong>'.e(Formato::moeda($total / $anos)).'</strong>')
                                : 'Informe o total e a vigência para calcular o valor anual.';
                        })
                            ->visible(fn (Get $get) => self::tipo($get) === TipoValor::Anualizado)
                            ->columnSpanFull(),
                        Select::make('fonte_recurso')
                            ->label('De onde vem o dinheiro?')
                            ->options(FonteRecurso::class)
                            ->default(FonteRecurso::Municipal)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('6. Observações')
                    ->collapsible()
                    ->schema([
                        Textarea::make('nota_publica')
                            ->label('Nota para o cidadão (aparece no site)')
                            ->helperText('Use para explicar algo importante sobre os números, como mudanças de valor ou metodologia.')
                            ->rows(2)
                            ->maxLength(1000),
                        Textarea::make('observacao_interna')
                            ->label('Observação interna (não aparece no site)')
                            ->rows(2)
                            ->maxLength(1000),
                    ]),
            ]);
    }

    private static function tipo(Get $get): ?TipoValor
    {
        $tipo = $get('tipo_valor');

        return $tipo instanceof TipoValor ? $tipo : TipoValor::tryFrom((string) $tipo);
    }

    /** Campo de valor em reais, aceitando o formato 1.234.567,89. */
    private static function campoMoeda(string $nome): TextInput
    {
        return TextInput::make($nome)
            ->prefix('R$')
            ->placeholder('0,00')
            ->inputMode('decimal')
            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
            ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2, ',', '.') : null)
            ->dehydrateStateUsing(fn ($state) => Formato::lerMoeda($state))
            ->rule(fn () => function (string $attribute, mixed $value, Closure $fail) {
                if (filled($value) && Formato::lerMoeda($value) === null) {
                    $fail('Informe um valor válido, por exemplo 1.234,56.');
                }
            });
    }
}
