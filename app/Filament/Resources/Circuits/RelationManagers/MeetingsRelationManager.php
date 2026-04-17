<?php

namespace App\Filament\Resources\Circuits\RelationManagers;

use Carbon\Carbon;
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
use Filament\Schemas\Components\Utilities\Set;
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
                    ->label('Meeting date and time')
                    ->required()
                    ->seconds(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, RelationManager $livewire) {
                        $circuit = $livewire->getOwnerRecord();
                        $md = $get('meetingdate') ? date('Y-m-d', strtotime($get('meetingdate'))) : date('Y-m-d');
                        $quarterStart = $this->getQuarterStart($md, $circuit->plan_month)->format('Y-m-01');
                        $set('quarter', $quarterStart);
                    }),
                Select::make('society_id')
                    ->label('Venue')
                    ->relationship('society', 'society', modifyQueryUsing: function (Builder $query, RelationManager $livewire){
                        return $query->where('circuit_id',$livewire->getOwnerRecord()->id);
                    }),
                TextInput::make('description')
                    ->required(),
                Select::make('quarter')
                    ->label('In which plan must the meeting be advertised?')
                    ->required()
                    ->selectablePlaceholder(false)
                    ->options(function (RelationManager $livewire, Get $get) {
                        $circuit = $livewire->getOwnerRecord();
                        $md = $get('meetingdate') ? date('Y-m-d', strtotime($get('meetingdate'))) : date('Y-m-d');
                        return $this->getQuarter($md, $circuit->plan_month);
                    })
                    ->default(function (RelationManager $livewire, Get $get) {
                        $circuit = $livewire->getOwnerRecord();
                        $md = $get('meetingdate') ? date('Y-m-d', strtotime($get('meetingdate'))) : date('Y-m-d');
                        return $this->getQuarterStart($md, $circuit->plan_month)->format('Y-m-01');
                    })
                    ->live()
            ]);
    }

    private function getQuarter($meetingDate, $startMonth)
    {
        $date = Carbon::parse($meetingDate);
        
        $monthOfMeeting = (int)$date->format('n');
        $diff = ($monthOfMeeting - $startMonth + 12) % 3;

        $currentPlanStart = $date->copy()->startOfMonth()->subMonths($diff);
        $nextPlanStart = $currentPlanStart->copy()->addMonths(3);
        $plan1Start = $currentPlanStart->copy()->subMonths(3);

        $plans = [$plan1Start, $currentPlanStart, $nextPlanStart];
        $results = [];

        foreach ($plans as $p) {
            $end = $p->copy()->addMonths(2);
            $rangeLabel = $p->format('M Y') . ' - ' . $end->format('M Y');
            $results[$p->format('Y-m-01')] = $rangeLabel;
        }

        return $results;
    }

    private function getQuarterStart($meetingDate, $startMonth)
    {
        $date = Carbon::parse($meetingDate);

        $monthOfMeeting = (int) $date->format('n');
        $diff = ($monthOfMeeting - $startMonth + 12) % 3;

        return $date->copy()->startOfMonth()->subMonths($diff);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('meetingdate')
                    ->label('Meeting date and time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('society.society')->label('Venue')
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
