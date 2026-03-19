<?php

namespace App\Filament\Resources\Ministers\Schemas;

use App\Models\Circuit;
use App\Models\Log;
use App\Models\Society;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
                Section::make('Personal details')
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
                        Repeater::make('circuitroles')
                            ->compact()
                            ->label('Circuit roles')
                            ->relationship()
                            ->schema([
                                Select::make('circuit_id')
                                    ->label('Circuit')
                                    ->searchable()
                                    ->options(
                                        Circuit::orderBy('reference')
                                            ->get()
                                            ->mapWithKeys(fn ($circuit) => [
                                                $circuit->id => $circuit->reference . ' ' . $circuit->circuit,
                                            ])
                                    ),
                                Select::make('status')
                                    ->multiple()
                                    ->options([
                                        'Guest' => 'Guest preacher',
                                        'Deacon' => 'Circuit deacon',
                                        'Minister' => 'Circuit minister',
                                        'Retired' => 'Retired deacon',
                                        'Superintendent' => 'Superintendent minister',
                                        'Supernumerary' => 'Supernumerary minister'
                                    ])
                            ])->columns(2),
                        TextEntry::make('log_details')
                            ->hiddenLabel(true)
                            ->state(function ($record){
                                $log = Log::where('model','Person')->where('action','Created')->where('model_id',$record->id)->orderBy('created_at','desc')->first();
                                if ($log) {
                                    return "Added by " . $log->user->name . " on " . $log->created_at->format('d/m/Y');
                                }
                            })->hiddenOn('create'),
                    ])->columns(2),
                Select::make('leadership')->label('District leadership roles')
                    ->multiple()
                    ->options(setting('district_leadership_roles')),
                TextInput::make('ordained')->numeric(),
            ])->columns(2);
    }
}
