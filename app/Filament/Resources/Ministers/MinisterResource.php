<?php

namespace App\Filament\Resources\Ministers;

use App\Filament\Resources\Ministers\Pages\CreateMinister;
use App\Filament\Resources\Ministers\Pages\EditMinister;
use App\Filament\Resources\Ministers\Pages\ListMinisters;
use App\Filament\Resources\Ministers\Schemas\MinisterForm;
use App\Filament\Resources\Ministers\Tables\MinistersTable;
use App\Models\Minister;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MinisterResource extends Resource
{
    protected static ?string $model = Minister::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return MinisterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MinistersTable::configure($table);
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
            'index' => ListMinisters::route('/'),
            'create' => CreateMinister::route('/create'),
            'edit' => EditMinister::route('/{record}/edit'),
        ];
    }
}
