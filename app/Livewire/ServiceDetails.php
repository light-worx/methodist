<?php

namespace App\Livewire;

use Livewire\Component;

class ServiceDetails extends Component
{
    public array $service = [];

    /**
     * Mount the component with service data
     */
    public function mount(array $service)
    {
        $this->service = $service;
    }

    public function render()
    {
        return view('livewire.service-details');
    }
}
