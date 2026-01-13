<x-filament-widgets::widget>
    <x-filament::section>
        @if($this->getData()['is_super_admin'])
            <p>Welcome, <i>{{ $this->getData()['user_name'] }}</i>. You have full administrator rights.
        @else
            <p>Welcome, <i>{{ $this->getData()['user_name'] }}</i>. Your user has been set up with editing rights as follows (let your circuit or district admin know if this needs to be changed):</p><br>
            @if ($this->getData()['circuits'])
                <p><i>Circuits:</i> {!!implode(', ', $this->getData()['circuits'])!!}.</p>
            @endif
            @if ($this->getData()['societies'])
                <p><i>Societies:</i> {!!implode(', ', $this->getData()['societies'])!!}.</p>
            @endif
        @endif
    </x-filament::section>
</x-filament-widgets::widget>