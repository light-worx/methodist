<?php

namespace App\Filament\Resources\Circuits\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MeetingsRelationManager extends RelationManager
{
    protected static string $relationship = 'meetings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('meetingdate'),
                Select::make('society_id')
                    ->relationship('society', 'id'),
                TextInput::make('description'),
                TextInput::make('quarter'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('meetingdate')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('society.society')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable(),
            ])
            ->defaultSort('meetingdate','desc')
            ->filters([
                Filter::make('hide_old_meetings')
                    ->query(fn (Builder $query): Builder => $query->where('meetingdate', '>', today()))
                    ->default()
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                ]),
            ]);
    }
}
