<?php

namespace App\Filament\Resources\PagamentoServidors;

use App\Filament\Resources\PagamentoServidors\Pages\CreatePagamentoServidor;
use App\Filament\Resources\PagamentoServidors\Pages\EditPagamentoServidor;
use App\Filament\Resources\PagamentoServidors\Pages\ListPagamentoServidors;
use App\Filament\Resources\PagamentoServidors\Schemas\PagamentoServidorForm;
use App\Filament\Resources\PagamentoServidors\Tables\PagamentoServidorsTable;
use App\Models\PagamentoServidor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PagamentoServidorResource extends Resource
{
    protected static ?string $model = PagamentoServidor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $modelLabel = 'pagamento a servidores';

    protected static ?string $pluralModelLabel = 'pagamentos a servidores';

    protected static ?string $recordTitleAttribute = 'verba';

    protected static ?string $slug = 'pagamentos-servidores';

    protected static string|\UnitEnum|null $navigationGroup = 'Administração';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return PagamentoServidorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagamentoServidorsTable::configure($table);
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
            'index' => ListPagamentoServidors::route('/'),
            'create' => CreatePagamentoServidor::route('/create'),
            'edit' => EditPagamentoServidor::route('/{record}/edit'),
        ];
    }
}
