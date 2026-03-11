<?php

namespace App\Filament\Resources\Societies\RelationManagers;

use App\Models\Circuit;
use App\Models\Person;
use App\Models\Preacher;
use App\Models\Society;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class PreachersRelationManager extends RelationManager
{
    protected static string $relationship = 'preachers';

    protected static ?string $recordTitleAttribute = 'person.firstname';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Preachers')->columnSpanFull()->tabs([
                    Tab::make('Personal details')->schema([
                        Section::make()
                            ->columns(2)
                            ->relationship('person')
                            ->columnSpanFull()
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
                                        'Ms' => 'Ms',
                                        'Mrs' => 'Mrs',
                                        'Rev' => 'Rev'
                                    ]),
                                TextInput::make('phone')
                                    ->tel(),
                                FileUpload::make('image')
                                    ->image()
                        ])
                    ]),
                    Tab::make('Preacher details')->columns(2)->schema([
                        Section::make()
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                Select::make('leadership')->label('Preacher leadership roles')
                                    ->multiple()
                                    ->options(array_combine(setting('preacher_leadership_roles'),setting('preacher_leadership_roles'))),
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
                            ]),
                    ])
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('person'))
            ->columns([
                TextColumn::make('person.surname')
                    ->label('Surname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('person.firstname')
                    ->label('First name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->formatStateUsing(function ($state){
                        return ucfirst($state);
                    })
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema([
                        Tabs::make('Preachers')->columnSpanFull()->tabs([
                            Tab::make('Personal details')->schema([
                                Section::make()
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('person.surname')
                                            ->live()
                                            ->required(),
                                        TextInput::make('person.firstname')->label('First name')
                                            ->required(),
                                        TextEntry::make('namecheck')->hiddenLabel(true)
                                            ->columnSpanFull()
                                            ->state(function (Get $get, RelationManager $livewire){
                                                $circuit=Society::find($livewire->getOwnerRecord()->id)->circuit_id;
                                                $similars = Person::whereHas('circuits', function ($q) use ($circuit) { $q->where('circuit_id', $circuit); })->withWhereHas('preacher')->where('surname',$get('person.surname'))->get();
                                                if (count($similars)){
                                                    $msg="Note: the following similar preacher names already exist in this circuit:";
                                                    foreach ($similars as $similar){
                                                        $society=Society::find($similar->preacher->society_id);
                                                        $msg.= " " . $similar->title . " " . $similar->firstname . " " . $similar->surname . " (" . $society->society . "),";
                                                    }
                                                    return substr($msg,0,-1) . ".";
                                                }
                                            }),
                                        Select::make('person.title')
                                            ->selectablePlaceholder(false)
                                            ->options([
                                                '' => '',
                                                'Mr' => 'Mr',
                                                'Ms' => 'Ms',
                                                'Mrs' => 'Mrs',
                                                'Rev' => 'Rev'
                                            ]),
                                        TextInput::make('person.phone')
                                            ->tel(),
                                        FileUpload::make('person.image')
                                            ->image()
                                    ]),
                            ]),
                            Tab::make('Preacher details')->columns(2)->schema([
                                Section::make()
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('leadership')->label('Preacher leadership roles')
                                            ->multiple()
                                            ->options(array_combine(
                                                setting('preacher_leadership_roles'),
                                                setting('preacher_leadership_roles')
                                            )),
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
                                                return !in_array($get('status'), ['preacher', 'emeritus']);
                                            }),
                                ])
                            ])
                        ])
                    ])
                    ->using(function (array $data, RelationManager $livewire): Preacher {
                        $society = $livewire->getOwnerRecord();
                        $person = Person::create([
                            'firstname' => $data['person']['firstname'],
                            'surname' => $data['person']['surname'],
                            'title' => $data['person']['title'],
                            'phone' => $data['person']['phone'],
                            'image' => $data['person']['image']
                        ]);
                        $preacher = $person->preacher()->create([
                            'person_id' => $person->id,
                            'society_id' => $society->id,
                            'status' => $data['status'],
                            'leadership' => json_encode($data['leadership']),
                            'number' => $data['number'],
                            'induction' => $data['induction'],
                            'active' => $data['active']
                        ]);
                        $circuit=Circuit::find($society->circuit_id);
                        if ($preacher->status=="guest"){
                            $status[]="Guest";
                            DB::table('circuit_person')->insert(
                                ['person_id' => $person->id, 'circuit_id' => $circuit->id, 'status' => json_encode($status)]
                            );
                        } else {
                            $status[]="";
                            DB::table('circuit_person')->insert(
                                ['person_id' => $person->id, 'circuit_id' => $circuit->id, 'status' => json_encode($status), 'is_preacher' => 1]
                            );
                        }
                        return $preacher;
                    })
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
