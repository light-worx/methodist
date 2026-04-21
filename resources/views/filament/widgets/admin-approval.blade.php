<x-filament-widgets::widget>
    <x-filament::section>
        @if (count($this->getData()['societies']))
            <b>Updated Society locations</b>
            @foreach($this->getData()['societies'] as $society)
                <p><a href="{{ route('filament.admin.resources.societies.edit', ['record' => $society->id]) }}" class="text-blue-500 hover:underline"><u>{{ $society->society }}</u></a></p>
            @endforeach
            <br>
        @endif
        @if (count($this->getData()['ideas']))
            <b>New ministry ideas</b>
            @foreach($this->getData()['ideas'] as $idea)
                <p><u><a href="{{ route('filament.admin.resources.ideas.edit', ['record' => $idea->id]) }}" class="text-blue-500 hover:underline">{{ $idea->idea }}</a></u></p>
            @endforeach
        @endif
    </x-filament::section>
</x-filament-widgets::widget>