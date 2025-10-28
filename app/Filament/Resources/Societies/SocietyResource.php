<?php

namespace App\Filament\Resources\Societies;

use App\Filament\Resources\Societies\Pages\CreateSociety;
use App\Filament\Resources\Societies\Pages\EditSociety;
use App\Filament\Resources\Societies\Pages\ListSocieties;
use App\Filament\Resources\Societies\Schemas\SocietyForm;
use App\Filament\Resources\Societies\Tables\SocietiesTable;
use App\Models\Society;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SocietyResource extends Resource
{
    protected static ?string $model = Society::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?string $recordTitleAttribute = 'society';

    public static function form(Schema $schema): Schema
    {
        return SocietyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SocietiesTable::configure($table);
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
            'index' => ListSocieties::route('/'),
            'create' => CreateSociety::route('/create'),
            'edit' => EditSociety::route('/{record}/edit'),
        ];
    }
}
