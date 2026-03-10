<?php

namespace App\Filament\Resources\People\Schemas;

use App\Models\Person;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('surname')
                    ->live()
                    ->required(),
                TextInput::make('firstname')->label('First name')
                    ->required(),
                TextEntry::make('namecheck')->hiddenLabel(true)
                    ->hiddenOn('edit')
                    ->columnSpanFull()
                    ->markdown()
                    ->state(function (Get $get){
                        $circuit=$get('circuit_id');
                        $similars = Person::with('circuits')->whereDoesntHave('minister')->where('surname',$get('surname'))->get();
                        if (count($similars)){
                            $msg="The following similar names already exist in the database (if the person is already in our database, rather edit the existing record):";
                            foreach ($similars as $similar){
                                $circ=" (Circuit ";
                                foreach ($similar->circuits as $circuit){
                                    $circ.= $circuit->reference . ", ";
                                }
                                $circ=substr($circ,0,-2) . ")";
                                $msg.= " <a href='" . route('filament.admin.resources.people.edit', ['resource' => 'people', 'record' => $similar->id, 'circuit_id' => $circuit]) . "'>" . $similar->title . " " . $similar->firstname . " " . $similar->surname . $circ . "</a>,";
                            }
                            return substr($msg,0,-1) . ".";
                        }
                    }),
                Hidden::make('circuit_id')->default(fn () => request()->query('circuit_id')),
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
                    ->image(),
                Select::make('status')
                    ->label('Circuit role')
                    ->multiple()
                    ->options([
                        'Circuit Steward' => 'Circuit Steward',
                        'Circuit Secretary' => 'Circuit Secretary',
                        'Circuit Treasurer' => 'Circuit Treasurer',
                    ])
                    ->saveRelationshipsUsing(function ($record, $state, Get $get) {
                        $record->circuitroles()
                            ->updateOrCreate(
                                ['circuit_id' => $get('circuit_id')],
                                ['status' => $state]
                            );
                    })
                
            ]);
    }
}
