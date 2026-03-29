<?php

namespace App\Filament\Resources\Circuits\Pages;

use App\Filament\Pages\PreachingPlan;
use App\Filament\Resources\Circuits\CircuitResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditCircuit extends EditRecord
{
    protected static string $resource = CircuitResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);        
        if (!$this->record->setup){
            $this->record->midweeks = DB::table('midweeks')->select('midweek')->orderBy('midweek')->groupBy('midweek')->get()->pluck('midweek')->toArray();
            $this->record->servicetypes = setting('default_service_types');
            $this->record->setup = true;
            $this->record->save();
            $this->fillForm();
        } 
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Preaching plan')
                ->url(fn (): string => PreachingPlan::getUrl([
                    'record' => $this->record,
                    'today' => date('Y-m-d'),
                ])),
            DeleteAction::make(),
        ];
    }
}
