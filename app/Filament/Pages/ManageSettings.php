<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use App\Filament\Clusters\Settings\SettingsCluster;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;

class ManageSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = GeneralSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mapbox_token'),
                TextInput::make('presiding_bishop')
                    ->label('Presiding Bishop'),
                TextInput::make('general_secretary'),
                TextInput::make('service_types'),
                TextInput::make('map_location'),
                TextInput::make('circuit_leadership_roles'),
                TextInput::make('preaching_leadership_roles'),
                TextInput::make('minister_leadership_roles'),
                TextInput::make('deepseek_api'),
            ]);
    }
}
