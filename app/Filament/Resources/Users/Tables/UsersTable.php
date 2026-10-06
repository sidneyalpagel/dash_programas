<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('username')->label('Usuário')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable()->placeholder('—')->toggleable(),
                TextColumn::make('secretaria.nome_curto')->label('Secretaria')->placeholder('—'),
                // Selo só para administradores; servidores ficam em branco (sem "X" vermelho).
                TextColumn::make('perfil')
                    ->label('Perfil')
                    ->state(fn ($record) => $record->is_admin ? 'Administrador' : null)
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-shield-check'),
                TextColumn::make('created_at')->label('Criado em')->date('d/m/Y')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
