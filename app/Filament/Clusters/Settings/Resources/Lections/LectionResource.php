<?php

namespace App\Filament\Clusters\Settings\Resources\Lections;

use App\Filament\Clusters\Settings\Resources\Lections\Pages\CreateLection;
use App\Filament\Clusters\Settings\Resources\Lections\Pages\EditLection;
use App\Filament\Clusters\Settings\Resources\Lections\Pages\ListLections;
use App\Filament\Clusters\Settings\Resources\Lections\Schemas\LectionForm;
use App\Filament\Clusters\Settings\Resources\Lections\Tables\LectionsTable;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\Lection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LectionResource extends Resource
{
    protected static ?string $model = Lection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'lection';

    public static function form(Schema $schema): Schema
    {
        return LectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LectionsTable::configure($table);
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
            'index' => ListLections::route('/'),
            'create' => CreateLection::route('/create'),
            'edit' => EditLection::route('/{record}/edit'),
        ];
    }
}
