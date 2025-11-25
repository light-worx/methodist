<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public ?string $mapbox_token;
    public ?string $presiding_bishop;
    public ?string $general_secretary;
    public ?array $service_types;
    public ?string $map_location;
    public ?array $circuit_leadership_roles;
    public ?array $preaching_leadership_roles;
    public ?array $minister_leadership_roles;
    public ?string $deepseek_api;
       
    public static function group(): string
    {
        return 'general';
    }
}