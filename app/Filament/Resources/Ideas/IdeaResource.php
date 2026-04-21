<?php

namespace App\Filament\Resources\Ideas;

use App\Filament\Resources\Ideas\Pages\CreateIdea;
use App\Filament\Resources\Ideas\Pages\EditIdea;
use App\Filament\Resources\Ideas\Pages\ListIdeas;
use App\Filament\Resources\Ideas\Schemas\IdeaForm;
use App\Filament\Resources\Ideas\Tables\IdeasTable;
use App\Models\Idea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IdeaResource extends Resource
{
    protected static ?string $model = Idea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'idea';

    public static function form(Schema $schema): Schema
    {
        return IdeaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IdeasTable::configure($table);
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
            'index' => ListIdeas::route('/'),
            'create' => CreateIdea::route('/create'),
            'edit' => EditIdea::route('/{record}/edit'),
        ];
    }
}
