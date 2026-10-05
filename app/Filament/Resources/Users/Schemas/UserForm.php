<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Toggle::make('is_admin')
                            ->label('Administrador')
                            ->helperText('Revisa, publica e gerencia secretarias e usuários.')
                            ->live()
                            ->columnSpanFull(),
                        Select::make('secretaria_id')
                            ->label('Secretaria')
                            ->helperText('O servidor só verá e editará os programas desta secretaria.')
                            ->relationship('secretaria', 'nome')
                            ->required(fn (Get $get) => ! $get('is_admin'))
                            ->columnSpanFull(),
                        TextInput::make('password')
                            ->label(fn (string $operation) => $operation === 'create' ? 'Senha' : 'Nova senha (deixe em branco para manter)')
                            ->password()
                            ->revealable()
                            ->rule(Password::min(8))
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
