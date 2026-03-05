<x-filament::page>
    <form wire:submit="register" class="space-y-6 max-w-lg mx-auto">
        {{ $this->form }}
        <br>
        <x-filament::button type="submit">
            Complete Registration
        </x-filament::button>
    </form>
</x-filament::page>