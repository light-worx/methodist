<?php

namespace App\Filament\Resources\Circuits\RelationManagers;

use App\Models\Circuitrole;
use App\Models\Person;
use App\Models\Preacher;
use App\Models\Society;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PersonsRelationManager extends RelationManager
{
    protected static string $relationship = 'persons';

    protected static ?string $title = 'People';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal details')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('firstname')->label('First name')
                            ->required(),
                        TextInput::make('surname')
                            ->required(),
                        TextInput::make('title'),
                        TextInput::make('phone')
                            ->tel(),
                        FileUpload::make('image')
                            ->image()
                    ]),
                Section::make('Clergy')
                    ->relationship('minister')
                    ->description('This section relates only to ministers and deacons')
                    ->columnSpanFull()
                    ->columns(3)
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
                        Toggle::make('active')
                            ->onColor('success'),
                    ]),
                Section::make('Preacher')->relationship('preacher')
                    ->description('This section relates only to preachers')
                    ->columnSpanFull()
                    ->hiddenOn('create')
                    ->columns(2)
                    ->visible(function ($record){
                        if ($record->preacher){
                            return true;
                        } else {
                            return false;
                        }
                    })
                    ->schema([
                        Select::make('leadership')->label('Preacher leadership roles')
                            ->multiple()
                            ->options(array_combine(setting('preacher_leadership_roles'),setting('preacher_leadership_roles'))),
                        Select::make('society_id')->label('Society')
                            ->options(function ($livewire){
                                return Society::where('circuit_id',$livewire->getOwnerRecord()->id)->orderBy('society')->get()->pluck('society','id');
                            }),
                        Select::make('status')
                            ->live()
                            ->options([
                                'note' => 'Preacher on note',
                                'trial' => 'Preacher on trial',
                                'preacher' => 'Local preacher',
                                'emeritus' => 'Emeritus preacher',
                                'guest' => 'Guest preacher'
                            ]),
                        TextInput::make('number')->label('Preacher number (optional)')
                            ->numeric(),
                        TextInput::make('induction')->label('Year of induction')
                            ->readonly(function (Get $get){
                                if (($get('status')=="preacher") or ($get('status')=="emeritus")){
                                    return false;
                                } else {
                                    return true;
                                }
                            }),
                        Toggle::make('active')
                            ->onColor('success'),
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
                        Select::make('status')->label('Status')
                            ->live()
                            ->options(function ($record){
                                $person = $record;
                                if ($person->minister){
                                    $options=[
                                        'Guest' => 'Guest preacher',
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
                        Select::make('societies')->label('Pastoral oversight ')
                            ->visible(fn ($record) => $record->minister)
                            ->options(function ($record){
                                return Society::where('circuit_id',$record->circuit_id)->orderBy('society')->get()->pluck('society','id');
                            })
                            ->multiple()
                            ->statePath('societies'),
                    ]),                
                TextEntry::make('circuitroles')->label('Status in other circuits')
                    ->hiddenOn('create')
                    ->visible(function ($record){
                        if (count($record->circuitroles)>1){
                            return true;
                        } else {
                            return false;
                        }
                    })
                    ->columnSpanFull()
                    ->listWithLineBreaks()
                    ->state(function ($record, RelationManager $livewire){
                        $states=[];
                        $thiscircuit = $livewire->getOwnerRecord()->id;
                        foreach ($record->circuitroles as $role){
                            if ($role->circuit_id !== $thiscircuit){
                                $states[]=$role->circuit->reference . " " . $role->circuit->circuit . " (" . implode(", ",$role->status) . ")";
                            }
                        }
                        return $states;
                    })
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('surname')
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
                    })
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make('Add a new person')
                    ->steps([ 
                        Step::make('Step One')
                            ->description('Status of new person')
                            ->schema([
                                Radio::make('clergylay')->label('Is this person a')
                                    ->live()
                                    ->default('preacher')
                                    ->options([
                                        'preacher' => 'Local preacher',
                                        'leader' => 'Circuit leader (but not a preacher)',
                                        'deacon' => 'Deacon',
                                        'minister' => 'Minister'
                                    ]),

                            ]),
                        Step::make('Step Two')
                            ->description('Personal details')
                            ->columns(2)
                            ->schema([
                                TextInput::make('surname')
                                    ->required()
                                    ->live(),
                                TextInput::make('firstname')->label('First name')->required(),
                                TextEntry::make('namecheck')->hiddenLabel(true)
                                    ->columnSpanFull()
                                    ->state(function (Get $get, RelationManager $livewire){
                                        $circuit=$livewire->getOwnerRecord()->id;
                                        if (($get('clergylay')=="preacher") or ($get('clergylay')=="leader")){
                                            $societies=Society::where('circuit_id',$circuit)->get()->pluck('id')->toArray();
                                            $similars = Person::where('surname', $get('surname'))
                                                ->where(function ($query) use ($circuit, $societies) {
                                                    $query->whereHas('preacher', function ($q) use ($societies) {
                                                        $q->whereIn('society_id', $societies);
                                                    })
                                                    ->orWhereHas('circuits', function ($q) use ($circuit) {
                                                        $q->where('circuit_id', $circuit);
                                                    });
                                                })->get();
                                            if (count($similars)){
                                                $msg="The following similar names already exist in this circuit: ";
                                                foreach ($similars as $similar){
                                                    $msg.= $similar->title . " " . $similar->firstname . " " . $similar->surname . ",";
                                                }
                                                return substr($msg,0,-1) . ".";
                                            }
                                        } else {
                                            $similars = Person::with('circuits')->whereHas('minister')->where('surname',$get('surname'))->get();
                                            if (count($similars)){
                                                $msg="The following similar clergy names already exist in the database (if the person is already in our database, rather transfer them or add them as a guest preacher):";
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
                                TextInput::make('phone')->tel(),
                                FileUpload::make('image')
                                    ->image()
                            ]),
                        Step::make('Step Three')
                            ->description(fn (Get $get) => ucfirst($get('clergylay')) . " details")
                            ->schema([
                                Section::make()
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->visible(fn (Get $get) => $get('clergylay') == "minister")
                                    ->schema([
                                        Select::make('leadership')->label('District leadership roles')
                                            ->multiple()
                                            ->options(setting('district_leadership_roles')),
                                        TextInput::make('ordained')->numeric(),
                                        Toggle::make('active')
                                            ->default(true)
                                            ->onColor('success'),
                                        Select::make('societies')->label('Societies')
                                            ->options(function (RelationManager $livewire){
                                                return Society::where('circuit_id',$livewire->getOwnerRecord()->id)->orderBy('society')->get()->pluck('society','id');
                                            })
                                            ->multiple()
                                            ->statePath('societies')
                                    ]),
                                Section::make()
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->visible(fn (Get $get) => $get('clergylay') == "deacon")
                                    ->schema([
                                        TextInput::make('ordained')->numeric(),
                                        Toggle::make('active')
                                            ->default(true)
                                            ->onColor('success'),
                                        Select::make('societies')->label('Societies')
                                            ->options(function (RelationManager $livewire){
                                                return Society::where('circuit_id',$livewire->getOwnerRecord()->id)->orderBy('society')->get()->pluck('society','id');
                                            })
                                            ->multiple()
                                            ->statePath('societies')
                                    ]),
                                Section::make()
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->visible(fn (Get $get) => $get('clergylay') == "preacher")
                                    ->schema([
                                        Select::make('leadership')->label('Preacher leadership roles (if applicable)')
                                            ->multiple()
                                            ->options(array_combine(setting('preacher_leadership_roles'),setting('preacher_leadership_roles'))),
                                        Select::make('society_id')->label('Society')
                                            ->selectablePlaceholder(false)
                                            ->options(function ($livewire){
                                                return Society::where('circuit_id',$livewire->getOwnerRecord()->id)->orderBy('society')->get()->pluck('society','id');
                                            }),
                                        Select::make('status')
                                            ->live()
                                            ->required()
                                            ->selectablePlaceholder(false)
                                            ->options([
                                                'note' => 'Preacher on note',
                                                'trial' => 'Preacher on trial',
                                                'preacher' => 'Local preacher',
                                                'emeritus' => 'Emeritus preacher',
                                                'guest' => 'Guest preacher'
                                            ]),
                                        TextInput::make('number')->label('Preacher number (optional)')
                                            ->numeric(),
                                        TextInput::make('induction')->label('Year of induction')
                                            ->readonly(function (Get $get){
                                                if (($get('status')=="preacher") or ($get('status')=="emeritus")){
                                                    return false;
                                                } else {
                                                    return true;
                                                }
                                            }),
                                        Toggle::make('active')
                                            ->default(true)
                                            ->onColor('success'),
                                    ])
                            ])
                        
                    ])
                    ->action(function (array $data) {
                        $person=Person::create([
                            'surname'=>$data['surname'],
                            'firstname'=>$data['firstname'],
                            'title'=>$data['title'],
                            'phone'=>$data['phone'],
                            'image'=>$data['image'],
                        ]);
                        if ($data['clergylay']=="preacher"){
                            $person->society_id = $data['society_id'];
                            $person->save();
                            $preacher = Preacher::create([
                                'person_id'=>$person->id,
                                'society_id'=>$person->society_id,
                                'status'=>$data['status'],
                                'leadership'=>json_encode($data['leadership']),
                                'induction'=>$data['induction'],
                                'number'=>$data['number'],
                                'active'=>$data['active']
                            ]);
                        }
                    }),
                Action::make('transfer')->label('Transfer minister / deacon or add as guest')
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
