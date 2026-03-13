<x-web pageName="MCSA Connexion">
    This site has been updated. For a time, the old site will be available at <a href="https://mcsa.church.net.za">https://mcsa.church.net.za</a>, but please explore the new site! If you were an authorised user on the previous system, please <a href="{{ url('/admin') }}">log in</a> from the menu using your old credentials and let us know if you have any issues.<br><br>

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