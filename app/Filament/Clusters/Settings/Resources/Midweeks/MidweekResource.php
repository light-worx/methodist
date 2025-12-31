<?php

namespace App\Filament\Clusters\Settings\Resources\Midweeks;

use App\Filament\Clusters\Settings\Resources\Midweeks\Pages\CreateMidweek;
use App\Filament\Clusters\Settings\Resources\Midweeks\Pages\EditMidweek;
use App\Filament\Clusters\Settings\Resources\Midweeks\Pages\ListMidweeks;
use App\Filament\Clusters\Settings\Resources\Midweeks\Schemas\MidweekForm;
use App\Filament\Clusters\Settings\Resources\Midweeks\Tables\MidweeksTable;
use Lightworx\FilamentSettings\Filament\Clusters\SettingsCluster;
use App\Models\Midweek;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MidweekResource extends Resource
{
    protected static ?string $model = Midweek::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $recordTitleAttribute = 'midweek';

    public static function form(Schema $schema): Schema
    {
        return MidweekForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MidweeksTable::configure($table);
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
            'index' => ListMidweeks::route('/'),
            'create' => CreateMidweek::route('/create'),
            'edit' => EditMidweek::route('/{record}/edit'),
        ];
    }
}
