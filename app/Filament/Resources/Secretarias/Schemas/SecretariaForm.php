<?php

namespace App\Filament\Resources\Secretarias\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SecretariaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('nome')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('nome_curto')
                            ->label('Nome curto')
                            ->helperText('Usado nos gráficos e filtros do site. Ex.: "Agricultura".')
                            ->required()
                            ->maxLength(60)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set, $get) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
                        TextInput::make('slug')
                            ->label('Endereço no site')
                            ->prefix('/secretarias/')
                            ->required()
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        ColorPicker::make('cor')
                            ->label('Cor de identificação')
                            ->required(),
                        TextInput::make('ordem')
                            ->label('Ordem de exibição')
                            ->integer()
                            ->default(0),
                        Textarea::make('apresentacao')
                            ->label('Apresentação para o cidadão')
                            ->helperText('Uma ou duas frases sobre o que a secretaria faz.')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('endereco')->label('Endereço'),
                        TextInput::make('telefone')->label('Telefone')->tel(),
                        TextInput::make('email')->label('E-mail de contato')->email(),
                    ]),
            ]);
    }
}
