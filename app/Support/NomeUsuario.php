<?php

namespace App\Support;

use Filament\Forms\Components\TextInput;

/** Campo e regras do nome de usuário usado no login. */
class NomeUsuario
{
    public const REGRA = '/^[a-z0-9._-]+$/';

    public static function campo(): TextInput
    {
        return TextInput::make('username')
            ->label('Usuário (para entrar no painel)')
            ->helperText('Letras minúsculas, números, ponto, hífen ou sublinhado. Ex.: maria.silva')
            ->required()
            ->minLength(3)
            ->maxLength(60)
            ->regex(self::REGRA)
            ->unique(ignoreRecord: true)
            ->dehydrateStateUsing(fn (?string $state) => mb_strtolower(trim((string) $state)))
            ->autocomplete('off');
    }
}
