<?php

namespace App\Filament\Resources\Secretarias\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SecretariasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['programas', 'usuarios']))
            ->columns([
                ColorColumn::make('cor')->label(''),
                TextColumn::make('nome')->label('Secretaria')->searchable()->wrap(),
                TextColumn::make('programas_count')->label('Programas')->alignEnd(),
                TextColumn::make('usuarios_count')->label('Usuários')->alignEnd(),
                TextColumn::make('ordem')->label('Ordem')->sortable()->alignEnd(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
