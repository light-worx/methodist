<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Resources\Roles\Pages\ListRoles;
use BackedEnum;
use Bishopm\Hub\Filament\Clusters\Settings\Resources\UserResource\Pages\ListUsers;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TagsInput;

class Settings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = GeneralSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mapbox_token'),
                TextInput::make('presiding_bishop')
                    ->label('Presiding Bishop'),
                TextInput::make('general_secretary')
                    ->label('General Secretary'),
                KeyValue::make('service_types'),
                Map::make('map_location'),
                TagsInput::make('circuit_leadership_roles'),
                TagsInput::make('preaching_leadership_roles'),
                TagsInput::make('minister_leadership_roles'),
                TextInput::make('deepseek_api'),
            ]);
    }
}
