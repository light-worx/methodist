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
use Filament\Schemas\Components\Utilities\Get;
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
                DateTimePicker::make('meetingdate')
                    ->live(),
                Select::make('society_id')
                    ->relationship('society', 'society', modifyQueryUsing: function (Builder $query, RelationManager $livewire){
                        return $query->where('circuit_id',$livewire->getOwnerRecord()->id);
                    }),
                TextInput::make('description'),
                Select::make('quarter')
                    ->label('Which plan must the meeting be listed in?')
                    ->selectablePlaceholder(false)
                    ->options(function (RelationManager $livewire, Get $get){
                        $circuit=$livewire->getOwnerRecord();
                        if ($get('meetingdate')){
                            $yr = date('Y',strtotime($get('meetingdate')));
                        } else {
                            $yr=date('Y');
                        }
                        if ($circuit->plan_month==2) {
                            return [
                                2 => 'Feb - Apr ' . $yr,
                                5 => 'May - Jul ' . $yr,
                                8 => 'Aug - Oct ' . $yr,
                                11 => 'Nov - Jan ' . $yr
                            ];
                        } elseif ($circuit->plan_month==3) {
                            return [
                                3 => 'Mar - May ' . $yr,
                                6 => 'Jun - Aug ' . $yr,
                                9 => 'Sep - Nov ' . $yr,
                                12 => 'Dec - Feb ' . $yr
                            ];
                        } else {
                            return [
                                1 => 'Jan - Mar ' . $yr,
                                4 => 'Apr - Jun ' . $yr,
                                7 => 'Jul - Sep ' . $yr,
                                10 => 'Oct - Dec ' . $yr
                            ];
                        }
                    }),
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
