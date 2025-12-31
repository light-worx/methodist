<?php

namespace App\Filament\Resources\Circuits\RelationManagers;

use App\Models\Circuitrole;
use App\Models\Person;
use App\Models\Society;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PersonsRelationManager extends RelationManager
{
    protected static string $relationship = 'persons';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal details')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('firstname')
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
                Section::make('Status and pastoral responsibilities in this circuit')
                    ->hiddenOn('create')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('status')->label('Status')
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
                        Select::make('societies')->label('Societies')
                            ->options(function ($record){
                                return Society::where('circuit_id',$record->circuit_id)->orderBy('society')->get()->pluck('society','id');
                            })
                            //->formatStateUsing(fn ($state) => json_decode($state))
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
                Action::make('transfer')->label('Transfer a minister or add as guest')
                    ->schema([
                        Grid::make(['sm'])
                            ->schema([
                                Select::make('person_id')->label('Existing names')
                                    ->live()
                                    ->options(function ($livewire){
                                        $circuitid=$livewire->getOwnerRecord()->id;
                                        $persons = Person::whereHas('circuits', function ($q) use ($circuitid) { $q->where('circuit_id','<>',$circuitid); })->orderBy('surname')->orderBy('firstname')->get();
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
                        Circuitrole::create([
                            'person_id'=>$data['person_id'],
                            'circuit_id'=>$circuit_id,
                            'status'=>$data['status'],
                            'societies'=>$data['societies']
                        ]);
                    }),
                    CreateAction::make()->label('Add a new person')
                        ->using(function (array $data, RelationManager $livewire){
                            $circuit_id=$livewire->getOwnerRecord()->id;
                            $person=Person::create([
                                'firstname' => $data['firstname'],
                                'surname' => $data['surname'],
                                'title' => $data['title'],
                                'phone' => $data['phone'],
                                'leadership' => $data['leadership'],
                                'society_id' => $data['society_id']
                            ]);
                            $circuitrole=Circuitrole::create([
                                'person_id'=>$person->id,
                                'circuit_id'=>$circuit_id,
                                'status'=>$data['status'],
                                'societies'=>$data['societies']
                            ]);
                            return $person;
                        })
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                    BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
