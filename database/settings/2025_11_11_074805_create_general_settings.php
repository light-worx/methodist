<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', '');
        $this->migrator->add('general.presiding_bishop', '');
        $this->migrator->add('general.general_secretary', '');
        $this->migrator->add('general.mapbox_token', '');
        $this->migrator->add('general.service_types', '');
        $this->migrator->add('general.map_location', '');
        $this->migrator->add('general.circuit_leadership_roles', '');
        $this->migrator->add('general.preaching_leadership_roles', '');
        $this->migrator->add('general.minister_leadership_roles', '');
        $this->migrator->add('general.deepseek_api', '');
    }
};
