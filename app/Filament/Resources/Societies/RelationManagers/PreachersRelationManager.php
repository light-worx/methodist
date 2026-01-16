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
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
                Section::make('Personal details')
                    ->columnSpanFull()
                    ->columns(3)
                    ->relationship('person', condition: function (string $operation){
                        if ($operation=="edit"){
                            return true;
                        } else {
                            return false;
                        }
                    })
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
                    ]),
                Fieldset::make('Preacher details')
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
                        Toggle::make('active')
                            ->onColor('success'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('person'))
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
                    ->using(function (array $data, RelationManager $livewire): Preacher {
                        $society = $livewire->getOwnerRecord();
                        dd($data['person']);
                        $person = Person::create([

                        ]);
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
                        return $preacher;
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
