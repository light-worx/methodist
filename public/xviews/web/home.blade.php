<x-web pageName="MCSA Connexion">
    <ul class="list-unstyled">
        @foreach ($districts as $district)
            @if ($district->active)
                <li><a href="{{url('/' . $district->slug)}}">{{$district->district}}</a></li>
            @else
                <li>{{$district->district}}</li>
            @endif
        @endforeach
    </ul>
</x-web>