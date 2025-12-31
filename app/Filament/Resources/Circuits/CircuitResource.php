<?php

namespace App\Filament\Resources\Circuits;

use App\Filament\Resources\Circuits\Pages\CreateCircuit;
use App\Filament\Resources\Circuits\Pages\EditCircuit;
use App\Filament\Resources\Circuits\Pages\ListCircuits;
use App\Filament\Resources\Circuits\Schemas\CircuitForm;
use App\Filament\Resources\Circuits\Tables\CircuitsTable;
use App\Models\Circuit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CircuitResource extends Resource
{
    protected static ?string $model = Circuit::class;

    public static array|string $routeMiddleware = ['checkperms'];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'circuit';

    public static function form(Schema $schema): Schema
    {
        return CircuitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CircuitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SocietiesRelationManager::class,
            RelationManagers\PersonsRelationManager::class,
            RelationManagers\MeetingsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCircuits::route('/'),
            'create' => CreateCircuit::route('/create'),
            'edit' => EditCircuit::route('/{record}/edit'),
        ];
    }
}
