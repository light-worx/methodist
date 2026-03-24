<?php

namespace App\Filament\Resources\Circuits\RelationManagers;

use App\Filament\Traits\Notifiable;
use App\Models\Circuitrole;
use App\Models\Log;
use App\Models\Person;
use App\Models\Society;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MinistersRelationManager extends RelationManager
{
    use Notifiable;

    protected static string $relationship = 'persons';

    protected static ?string $title = 'Ministers';

    protected static ?string $modelLabel = 'clergy';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Personal details')
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
                                $this->getNotificationAction('minister')
                            ])->columns(2),
                        Tab::make('Clergy details')
                            ->schema([
                                Section::make()
                                    ->relationship('minister')
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->hiddenOn('create')
                                    ->visible(function ($record){
                                        if ($record->minister){
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    })
                                    ->schema([
                                        Select::make('leadership')->label('District leadership roles')
                                            ->multiple()
                                            ->options(setting('district_leadership_roles')),
                                        TextInput::make('ordained')->numeric(),
                                    ]),
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
                                        ->columnSpanFull()
                                        ->columns(2)
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
                                                            'Retired' => 'Retired deacon',
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
                                                ->dehydrateStateUsing(fn ($state) => collect($state)->map(fn ($v) => (string) $v)->values()->all())
                                                ->statePath('societies'),
                                        ]),                
                                        Repeater::make('circuitroles')->label('Status in other circuits')->hiddenOn('create')
                                            ->relationship(
                                                modifyQueryUsing: function ($query, RelationManager $livewire) {
                                                    $parentRecord = $livewire->getOwnerRecord();
                                                    if ($parentRecord) {
                                                        $query->where('circuit_id', '!=', $parentRecord->id);
                                                    }
                                                    return $query;
                                                }
                                            )
                                            ->addable(false)
                                            ->compact()
                                            ->schema([
                                                TextEntry::make('circuit_id')
                                                    ->hiddenLabel()
                                                    ->getStateUsing(function ($record) {
                                                        return $record->circuit->circuit . " (" . $record->circuit->reference . ")";
                                                    }),
                                                TextEntry::make('status')
                                                    ->hiddenLabel()
                                                    ->getStateUsing(function ($record) {
                                                        return implode(', ',$record->status);
                                                    })
                                            ])->columns(2)
                                    ])
                        ])
                    ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fullname')
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('minister'))
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
                    }),
            ])
            ->filters([
                Filter::make('only_stationed_clergy')
                    ->query(function (Builder $query) {
                        return $query->whereJsonContains('status', 'Minister')->orWhereJsonContains('status', 'Superintendent')->orWhereJsonContains('status', 'Deacon');
                    })
                    ->default()
            ])
            ->headerActions([
                CreateAction::make('Add a new minister')->label('New minister')
                    ->schema([
                        Section::make()->schema([
                            TextInput::make('surname')
                                ->required()
                                ->live(),
                            TextInput::make('firstname')->label('First name')->required(),
                            TextEntry::make('namecheck')->hiddenLabel(true)
                                ->columnSpanFull()
                                ->state(function (Get $get, RelationManager $livewire){
                                    $circuit=$livewire->getOwnerRecord()->id;
                                    $similars = Person::with('circuits')->whereHas('minister')->where('surname',$get('surname'))->get();
                                    if (count($similars)){
                                        $msg="The following similar clergy names already exist in the database (if the person is already in our database, rather transfer them to this circuit or add them as a guest preacher):";
                                        foreach ($similars as $similar){
                                            $circ=" (Circuit ";
                                            foreach ($similar->circuits as $circuit){
                                                $circ.= $circuit->reference . ", ";
                                            }
                                            $circ=substr($circ,0,-2) . ")";
                                            $msg.= " " . $similar->title . " " . $similar->firstname . " " . $similar->surname . $circ . ",";
                                        }
                                        return substr($msg,0,-1) . ".";
                                    }
                                }),
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
                            Select::make('ministerdeacon')->label('Minister or Deacon?')
                                ->selectablePlaceholder(false)
                                ->default('Minister')
                                ->options([
                                    'Minister' => 'Minister',
                                    'Deacon' => 'Deacon'
                                ]),
                            TextInput::make('phone')->tel(),
                            FileUpload::make('image')
                                ->image(),
                            Select::make('leadership')->label('District leadership roles')
                                ->multiple()
                                ->options(setting('district_leadership_roles')),
                            TextInput::make('ordained')->numeric()->integer(),
                            Select::make('societies')->label('Societies: pastoral oversight')
                                ->options(function (RelationManager $livewire){
                                    return Society::where('circuit_id',$livewire->getOwnerRecord()->id)->orderBy('society')->get()->pluck('society','id');
                                })
                                ->multiple()
                                ->statePath('societies'),
                            Select::make('status')->label('Status in this circuit')
                                ->options([
                                    'Deacon' => 'Circuit deacon',
                                    'Guest' => 'Guest preacher',
                                    'Minister' => 'Circuit minister',
                                    'Retired' => 'Retired deacon',
                                    'Superintendent' => 'Superintendent minister',
                                    'Supernumerary' => 'Supernumerary minister'
                                ])
                                ->multiple()
                                ->statePath('status'),
                        ])->columns(2)
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $person=Person::create([
                            'surname'=>$data['surname'],
                            'firstname'=>$data['firstname'],
                            'title'=>$data['title'],
                            'phone'=>$data['phone'],
                            'image'=>$data['image']
                        ]);
                        Log::create([
                            'user_id'=>auth()->id(),
                            'action'=>'Created',
                            'model'=>'Person',
                            'model_id'=>$person->id
                        ]);
                        $minister = $person->minister()->create([
                            'leadership'=>$data['leadership'],
                            'status'=>$data['ministerdeacon'],
                            'ordained'=>$data['ordained']
                        ]);
                        Log::create([
                            'user_id'=>auth()->id(),
                            'action'=>'Created',
                            'model'=>'Minister',
                            'model_id'=>$minister->id
                        ]);
                        if (isset($data['status'])){
                            Circuitrole::create([
                                'person_id'=>$person->id,
                                'circuit_id'=>$livewire->getOwnerRecord()->id,
                                'status'=>array($data['ministerdeacon']),
                                'societies'=>$data['societies'] ?? []
                            ]);
                        }
                    }),
                Action::make('transfer')->label('Transfer clergy or add as guest')
                    ->schema([
                        Grid::make(['sm'])
                            ->schema([
                                Select::make('person_id')->label('Existing names')
                                    ->live()
                                    ->options(function ($livewire){
                                        $circuitid=$livewire->getOwnerRecord()->id;
                                        $persons = Person::whereHas('minister')->whereDoesntHave('circuits', function ($q) use ($circuitid) {
                                            $q->where('circuit_id', $circuitid);
                                        })
                                        ->orderBy('surname')
                                        ->orderBy('firstname')
                                        ->get();
                                        foreach ($persons as $person){
                                            $options[$person->id]=$person->surname . ", " . $person->firstname;
                                        }
                                        return $options;
                                    })
                                    ->searchable(),
                                Select::make('status')->label('Status in this circuit')
                                    ->options(function (Get $get){
                                        $person = Person::find($get('person_id'));
                                        if ($person){
                                            if ($person->minister){
                                                $options=[
                                                    'Guest' => 'Guest preacher',
                                                    'Minister' => 'Circuit minister',
                                                    'Retired' => 'Retired deacon',
                                                    'Superintendent' => 'Superintendent minister',
                                                    'Supernumerary' => 'Supernumerary minister'
                                                ];
                                            } elseif ($person->preacher){
                                                $options=array_combine(setting('preacher_leadership_roles'),setting('preacher_leadership_roles'));
                                                $options['Guest'] = 'Guest preacher';
                                                $options['Preacher'] = 'Local preacher';
                                            } else {
                                                $options=array_combine(setting('district_leadership_roles'),setting('district_leadership_roles'));
                                            }
                                            return $options;
                                        }
                                    })
                                    ->multiple()
                                    ->statePath('status'),
                            ])->columns(2)
                    ])
                    ->action(function (array $data, RelationManager $livewire){
                        $circuit_id=$livewire->getOwnerRecord()->id;
                        if (($data['person_id']) and ($data['status'])){
                            Circuitrole::create([
                                'person_id'=>$data['person_id'],
                                'circuit_id'=>$circuit_id,
                                'status'=>$data['status'],
                                'societies'=>$data['societies'] ?? []
                            ]);
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
            ])
            ->toolbarActions([
            ]);
    }
}
