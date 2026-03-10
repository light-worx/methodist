<?php

namespace App\Filament\Resources\Circuits\RelationManagers;

use App\Filament\Resources\People\PersonResource;
use App\Models\Log;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadersRelationManager extends RelationManager
{
    protected static string $relationship = 'persons';

    protected static ?string $title = 'Circuit leaders';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('firstname')->label('First name')
                    ->required(),
                TextInput::make('surname')
                    ->required(),
                Select::make('title')
                    ->selectablePlaceholder(false)
                    ->options([
                        '' => '',
                        'Mr' => 'Mr',
                        'Mrs' => 'Mrs',
                        'Ms' => 'Ms',
                        'Dr' => 'Dr',
                        'Rev' => 'Rev',
                        'Prof' => 'Prof'
                    ]),
                TextInput::make('phone')
                    ->tel(),
                FileUpload::make('image')
                    ->image(),
                TextEntry::make('log_details')
                    ->hiddenLabel(true)
                    ->state(function ($record){
                        $log = Log::where('model','Person')->where('action','Created')->where('model_id',$record->id)->orderBy('created_at','desc')->first();
                        if ($log) {
                            return "Added by " . $log->user->name . " on " . $log->created_at->format('d/m/Y');
                        }
                    })->hiddenOn('create'),
                Select::make('status')
                    ->label('Circuit role')
                    ->multiple()
                    ->options([
                        'Circuit Steward' => 'Circuit Steward',
                        'Circuit Secretary' => 'Circuit Secretary',
                        'Circuit Treasurer' => 'Circuit Treasurer',
                    ])
                    ->afterStateHydrated(function ($component, $record) {
                        $role = $record->circuitroles()
                            ->where('circuit_id', $this->ownerRecord->id)
                            ->first();

                        if ($role) {
                            $component->state($role->status);
                        }
                    })
                    ->dehydrateStateUsing(fn ($state) => $state)
                    ->saveRelationshipsUsing(function ($record, $state) {
                        $record->circuitroles()
                            ->updateOrCreate(
                                ['circuit_id' => $this->ownerRecord->id],
                                ['status' => $state]
                            );
                    })
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('surname')
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas('circuitroles', function (Builder $q) {
                    $q->where('status', 'like', '%Circuit%');
                })
            )
            ->defaultSort('surname')
            ->columns([
                TextColumn::make('surname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('firstname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')->searchable()
                    ->formatStateUsing(function ($state){
                        return implode(', ',json_decode($state));
                    })
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(fn () => PersonResource::getUrl('create', ['circuit_id' => $this->ownerRecord->id]))
                    ->label('New circuit leader')
            ])
            ->recordActions([
                EditAction::make()
            ])
            ->toolbarActions([
            ]);
    }
}
