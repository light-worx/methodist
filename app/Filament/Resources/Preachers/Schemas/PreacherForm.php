<?php

namespace App\Filament\Resources\Preachers\Schemas;

use App\Models\Person;
use App\Models\Society;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Livewire\Livewire;

class PreacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Preacher tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Personal details')
                            ->columnSpanFull()
                            ->schema([
                                Section::make()->columnSpanFull()->columns(2)
                                    ->relationship('person')->schema([
                                        Hidden::make('circuit_id')->default(fn () => request()->query('circuit_id')),
                                        TextInput::make('surname')->required()->columnSpan(1)->live(),
                                        TextInput::make('firstname')->required()->columnSpan(1)->label('First name'),
                                        TextEntry::make('namecheck')->hiddenLabel(true)
                                            ->columnSpanFull()
                                            ->state(function (Get $get){
                                                $circuit =$get('circuit_id');
                                                $similars = Person::whereHas('circuits', function ($q) use ($circuit) { $q->where('circuit_id', $circuit); })->where('surname',$get('surname'))->withWhereHas('preacher')->get();
                                                if (count($similars)){
                                                    $msg="Note: the following similar preacher names already exist in this circuit:";
                                                    foreach ($similars as $similar){
                                                        $society=Society::find($similar->preacher->society_id);
                                                        $msg.= " " . $similar->title . " " . $similar->firstname . " " . $similar->surname . " (" . $society->society . "),";
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
                                        TextInput::make('phone')->tel(),
                                        FileUpload::make('image')
                                            ->image()
                                    ])
                            ])->columns(2),
                        Tab::make('Preacher details')
                            ->columnSpanFull()
                            ->schema([
                                Hidden::make('person_id')->required(),
                                Hidden::make('circuit_id')->default(fn () => request()->query('circuit_id')),
                                Select::make('society_id')->label('Society')
                                    ->selectablePlaceholder(false)
                                    ->options(function (Get $get, $record){
                                        if (!$get('circuit_id')) {
                                            $soc = Society::find($record->society_id);
                                            $circuitId = $soc->circuit_id;
                                        } else {
                                            $circuitId =$get('circuit_id');
                                        }
                                        return Society::where('circuit_id', $circuitId)->orderBy('society')->pluck('society','id');
                                    }),
                                Select::make('status')
                                    ->required()
                                    ->selectablePlaceholder(false)
                                    ->options([
                                        'note' => 'Preacher on note',
                                        'trial' => 'Preacher on trial',
                                        'preacher' => 'Local preacher',
                                        'emeritus' => 'Emeritus preacher',
                                        'guest' => 'Guest preacher'
                                    ]),
                                Select::make('leadership')->label('Preacher leadership roles (if applicable)')
                                    ->multiple()
                                    ->options(array_combine(setting('preacher_leadership_roles'),setting('preacher_leadership_roles'))),
                                TextInput::make('induction')->label('Year of induction'),
                                TextInput::make('number')->label('Preacher number (optional)'),
                                Toggle::make('active')
                            ])->columns(2)
                    ])        
            ]);
    }
}
