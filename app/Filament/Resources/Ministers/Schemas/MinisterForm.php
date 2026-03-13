<?php

namespace App\Filament\Resources\Ministers\Schemas;

use App\Models\Log;
use App\Models\Society;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class MinisterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Personal details')
                            ->schema([
                                Section::make()
                                    ->relationship('person')
                                    ->columnSpanFull()
                                    ->columns(2)
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
                                    ])->columns(2),
                            ]),
                        Tab::make('Clergy details')
                            ->schema([
                                Select::make('leadership')->label('District leadership roles')
                                    ->multiple()
                                    ->options(setting('district_leadership_roles')),
                                TextInput::make('ordained')->numeric(),
                                Section::make('Status in this circuit')
                                    ->hiddenOn('create')
                                    ->afterHeader([
                                        Action::make('removeFromCircuit')->label('Remove from this Circuit')
                                            ->requiresConfirmation()
                                            ->action(function ($record, $action) { 
                                                DB::table('circuit_person')
                                                    ->where('person_id', $record->id)
                                                    ->where('circuit_id', $record->pivot_circuit_id)
                                                    ->delete();
                                                $action->cancelParentActions();
                                            }),
                                    ])
                                    ->schema([
                                        Select::make('circuitstatus')->label('Status')
                                            ->live()
                                            ->options(function ($record){
                                                $person = $record;
                                                if ($person->minister){
                                                    $options=[
                                                        'Guest' => 'Guest preacher',
                                                        'Deacon' => 'Circuit deacon',
                                                        'Minister' => 'Circuit minister',
                                                        'Superintendent' => 'Superintendent minister',
                                                        'Supernumerary' => 'Supernumerary minister'
                                                    ];
                                                } elseif ($person->preacher){
                                                    $options=array_combine(setting('district_leadership_roles'),setting('district_leadership_roles'));
                                                    $options['Guest'] = 'Guest preacher';
                                                    $options['Preacher'] = 'Local preacher';
                                                } else {
                                                    $options=array_combine(setting('district_leadership_roles'),setting('district_leadership_roles'));
                                                }
                                                return $options;
                                            })
                                            //->formatStateUsing(fn ($state) => json_decode($state))
                                            ->multiple()
                                            ->statePath('status'),
                                        Select::make('societies')->label('Pastoral oversight')
                                            ->visible(function ($record){
                                                if (($record->minister) and ((in_array('Superintendent',json_decode($record->pivot_status))) or (in_array('Minister',json_decode($record->pivot_status))))){
                                                    return true;
                                                }
                                            })
                                            ->options(function ($record){
                                                return Society::where('circuit_id',$record->circuit_id)->orderBy('society')->get()->pluck('society','id');
                                            })
                                            ->multiple()
                                            ->statePath('societies'),
                                    ]),
                            ])->columns(2)
                        ])
                    ]);
    }
}
