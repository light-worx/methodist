<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Change password')
                ->visible(function () {
                    if (auth()->user()->hasRole('super_admin')) {
                        return true;
                    } else {
                        return auth()->user()->id === $this->record->id;
                    }
                })
                ->schema([
                    TextInput::make('password')
                        ->label('New password')
                        ->required()
                        ->password(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'password' => Hash::make($data['password']),
                    ]);
                }),
            DeleteAction::make(),
        ];
    }
}
