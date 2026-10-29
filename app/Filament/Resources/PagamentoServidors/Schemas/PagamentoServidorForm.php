<?php

namespace App\Filament\Resources\PagamentoServidors\Schemas;

use App\Filament\Resources\Programas\Schemas\ProgramaForm;
use App\Models\PagamentoServidor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class PagamentoServidorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Callout::make('Fora dos programas municipais')
                    ->description('Estes pagamentos aparecem na página "Pagamentos a servidores" do site e não entram no total dos programas.')
                    ->info(),

                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('exercicio')
                            ->label('Ano (exercício)')
                            ->integer()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->default(now()->year)
                            ->required()
                            ->live(onBlur: true),
                        TextInput::make('periodo')
                            ->label('Período (opcional)')
                            ->helperText('Para o ano em andamento, ex.: "janeiro a setembro".')
                            ->maxLength(120),
                        TextInput::make('verba')
                            ->label('Verba')
                            ->placeholder('Ex.: Licença-prêmio')
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('exercicio', (int) $get('exercicio')),
                            )
                            ->validationMessages(['unique' => 'Esta verba já está cadastrada neste ano.'])
                            ->columnSpanFull(),
                        Textarea::make('descricao')
                            ->label('O que é (explicação para o cidadão)')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        TextInput::make('base_legal')
                            ->label('Base legal (opcional)')
                            ->placeholder('Ex.: Lei Municipal nº 1.234/2020')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('qtd_servidores')
                            ->label('Nº de servidores')
                            ->integer()
                            ->minValue(0),
                        ProgramaForm::campoMoeda('valor')
                            ->label('Valor total no ano')
                            ->required(),
                        TextInput::make('ordem')
                            ->label('Ordem de exibição')
                            ->integer()
                            ->default(fn () => (int) PagamentoServidor::max('ordem') + 1),
                        Toggle::make('publicado')
                            ->label('Mostrar no site')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
