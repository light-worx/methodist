<?php

namespace App\Filament\Resources\Circuits\RelationManagers;

use App\Filament\Actions\SendPushNotificationAction;
use App\Filament\Resources\Preachers\PreacherResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PreachersRelationManager extends RelationManager
{
    protected static string $relationship = 'preachers';

    protected static ?string $relatedResource = PreacherResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                SendPushNotificationAction::forPerson(),
                CreateAction::make()
                    ->url(fn () => PreacherResource::getUrl('create', ['circuit_id' => $this->ownerRecord->id])),
            ]);
    }
}
 