<?php

namespace App\Filament\Resources\Secretarias;

use App\Filament\Resources\Secretarias\Pages\CreateSecretaria;
use App\Filament\Resources\Secretarias\Pages\EditSecretaria;
use App\Filament\Resources\Secretarias\Pages\ListSecretarias;
use App\Filament\Resources\Secretarias\Schemas\SecretariaForm;
use App\Filament\Resources\Secretarias\Tables\SecretariasTable;
use App\Models\Secretaria;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SecretariaResource extends Resource
{
    protected static ?string $model = Secretaria::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $modelLabel = 'secretaria';

    protected static ?string $pluralModelLabel = 'secretarias';

    protected static ?string $recordTitleAttribute = 'nome_curto';

    protected static string|\UnitEnum|null $navigationGroup = 'Administração';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return SecretariaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SecretariasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecretarias::route('/'),
            'create' => CreateSecretaria::route('/create'),
            'edit' => EditSecretaria::route('/{record}/edit'),
        ];
    }
}
