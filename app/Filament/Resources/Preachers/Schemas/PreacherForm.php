<?php

namespace App\Filament\Resources\Preachers\Schemas;

use App\Models\Society;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

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
                                    TextInput::make('firstname')->required()->columnSpan(1),
                                    TextInput::make('surname')->required()->columnSpan(1),
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
                                Select::make('society_id')->label('Society')
                                    ->selectablePlaceholder(false)
                                    ->options(function ($record){
                                        $circuitId = $record->society->circuit_id;
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
