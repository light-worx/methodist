{{-- resources/views/filament/pages/manage-settings.blade.php --}}
<x-filament-panels::page>
    <div class="w-full flex flex-col lg:flex-row gap-6 items-start">
        {{-- Sidebar --}}
        <x-settings-sidebar class="flex-shrink-0">
            <a href="#general" class="block px-3 py-2 rounded hover:bg-gray-100">General</a>
            <a href="#appearance" class="block px-3 py-2 rounded hover:bg-gray-100">Appearance</a>
            <a href="#email" class="block px-3 py-2 rounded hover:bg-gray-100">Email</a>
        </x-settings-sidebar>

        {{-- Main content --}}
        <div class="flex-1 w-full">
            <x-filament::section>
                {{ $this->form }}
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
