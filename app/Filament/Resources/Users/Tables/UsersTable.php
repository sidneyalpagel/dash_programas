<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('secretaria.nome_curto')->label('Secretaria')->placeholder('—'),
                IconColumn::make('is_admin')->label('Administrador')->boolean(),
                TextColumn::make('created_at')->label('Criado em')->date('d/m/Y')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
