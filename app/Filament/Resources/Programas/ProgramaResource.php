<?php

namespace App\Filament\Resources\Programas;

use App\Enums\StatusPrograma;
use App\Filament\Resources\Programas\Pages\CreatePrograma;
use App\Filament\Resources\Programas\Pages\EditPrograma;
use App\Filament\Resources\Programas\Pages\ListProgramas;
use App\Filament\Resources\Programas\RelationManagers\HistoricosRelationManager;
use App\Filament\Resources\Programas\Schemas\ProgramaForm;
use App\Filament\Resources\Programas\Tables\ProgramasTable;
use App\Models\Programa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProgramaResource extends Resource
{
    protected static ?string $model = Programa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'programa';

    protected static ?string $pluralModelLabel = 'programas';

    protected static ?string $recordTitleAttribute = 'nome';

    // O slug se repete entre exercícios (o mesmo programa em 2025 e 2026); no painel, identifica pelo id.
    protected static ?string $recordRouteKeyName = 'id';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProgramaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramasTable::configure($table);
    }

    /** Servidores de secretaria só enxergam os programas da própria secretaria. */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('secretaria');
        $user = auth()->user();

        if ($user && ! $user->isAdmin()) {
            $query->where('secretaria_id', $user->secretaria_id);
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        $rascunhos = static::getEloquentQuery()->where('status', StatusPrograma::Rascunho)->count();

        return $rascunhos ?: null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Rascunhos ainda não publicados';
    }

    public static function getRelations(): array
    {
        return [
            HistoricosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProgramas::route('/'),
            'create' => CreatePrograma::route('/create'),
            'edit' => EditPrograma::route('/{record}/edit'),
        ];
    }
}
