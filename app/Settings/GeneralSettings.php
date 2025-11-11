<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public ?string $mapbox_token;
    public ?string $presiding_bishop;
    public ?string $general_secretary;
    public ?string $service_types;
    public ?string $map_location;
    public ?string $circuit_leadership_roles;
    public ?string $preaching_leadership_roles;
    public ?string $minister_leadership_roles;
    public ?string $deepseek_api;
       
    public static function group(): string
    {
        return 'general';
    }
}