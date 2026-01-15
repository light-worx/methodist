<?php

namespace App\Filament\Resources\Societies\RelationManagers;

use App\Models\Circuit;
use App\Models\Person;
use App\Models\Preacher;
use App\Models\Society;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class PreachersRelationManager extends RelationManager
{
    protected static string $relationship = 'preachers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal details')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('person.firstname')->label('First name')
                            ->required(),
                        TextInput::make('person.surname')
                            ->required(),
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
                            ->image(),
                        Select::make('preacherstatus')
                            ->visibleOn('create')
                            ->label('Status')
                            ->options([
                                'note' => 'Preacher on note',
                                'trial' => 'Preacher on trial',
                                'preacher' => 'Local preacher',
                                'emeritus' => 'Emeritus preacher',
                                'guest' => 'Guest preacher'
                            ]),
                    ]),
                Section::make('Preacher details')
                    ->hiddenOn('create')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('leadership')->label('Preacher leadership roles')
                            ->multiple()
                            ->options(array_combine(setting('preacher_leadership_roles'),setting('preacher_leadership_roles'))),
                        Select::make('society_id')->label('Society')
                            ->options(function ($livewire){
                                return Society::where('circuit_id',$livewire->getOwnerRecord()->circuit_id)->orderBy('society')->get()->pluck('society','id');
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('person.surname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('person.firstname')
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
                    ->using(function (array $data, string $model, RelationManager $livewire): Person {
                        $society = $livewire->getOwnerRecord();
                        $person = $model::create($data);
                        $status=array();
                        $preacher = $person->preacher()->create([
                            'person_id' => $person->id,
                            'society_id' => $society->id,
                            'status' => $data['preacherstatus'],
                            'active' => 1
                        ]);
                        $circuit=Circuit::find($society->circuit_id);
                        if ($person->status=="guest"){
                            $status[]="Guest";
                        } else {
                            $status[]="Preacher";
                        }
                        DB::table('circuit_person')->insert(
                            ['person_id' => $person->id, 'circuit_id' => $circuit->id, 'status' => json_encode($status)]
                        );
                        return $person;
                    })
            ])
            ->recordActions([
                EditAction::make(),
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
