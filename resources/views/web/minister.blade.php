<x-layouts.web pageName="{{$minister->title}} {{$minister->firstname}} {{$minister->surname}}">
    @if ($minister->image)
        <img class="rounded" width="100px" src="{{url('/storage/public/' . $minister->image)}}">
    @else 
        <img class="rounded" width="100px" src="{{url('/methodist/images/blank.png')}}">
    @endif
    @if ($minister->minister->ordained)
        <p><b>Ordained:</b> {{$minister->minister->ordained}}</p>
    @endif
    @if ($minister->minister->leadership)
        @foreach ($minister->minister->leadership as $lead)
            <span class="bg-dark badge text-white text-small mx-3">{{$lead}}</span>
        @endforeach
    @endif
        
    @foreach ($minister->circuitroles as $circuit)
        @if (in_array('Minister',$circuit->status) or in_array('Superintendent',$circuit->status))
            <p>
                <a href="{{url('/' . $circuit->circuit->district->slug . '/' . $circuit->circuit->slug)}}">{{$circuit->circuit->circuit}} {{$circuit->circuit->reference}}</a>
                @if (count($societies))
                    ({{implode(", ",$societies[$circuit->circuit_id])}})
                @endif
            </p>
        @endif
    @endforeach
</x-layouts.web>