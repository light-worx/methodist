<x-layouts.web pageName="Lectionary: {{$lects['liturgical_day']}}">
    <h3>Lectionary readings</h3>
    <livewire:service-details :service="$lects" />
</x-layouts.web>