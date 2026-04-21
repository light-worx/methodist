@extends('vendor.pwa.layouts.app')

@section('content')
    
    <link rel="stylesheet" href="{{ asset('css/leaflet.css') }}">
    <script src="{{ asset('js/leaflet.js') }}"></script>
    <div style="height:400px" id="map"></div>

    <script>
        // 1. Initialize Map Variables
        var lat = {{ $society->latitude ?? 'null' }};
        var lng = {{ $society->longitude ?? 'null' }};
        var map;

        if (lat && lng) {
            // Display existing location
            map = L.map('map').setView([lat, lng], 15);
            L.marker([lat, lng]).addTo(map);
        } else {
            // Default view or user location
            map = L.map('map').setView([{{$dlat}}, {{$dlon}}], 13);
            map.locate({setView: true, maxZoom: 15});

            // 2. Add Click Listener for New Locations
            map.on('click', function(e) {
                var newLat = e.latlng.lat;
                var newLng = e.latlng.lng;

                // Add a temporary marker
                var tempMarker = L.marker([newLat, newLng]).addTo(map)
                    .bindPopup("Saving location...")
                    .openPopup();

                // 3. Send data to Laravel
                saveLocation(newLat, newLng, tempMarker);
            });
        }

        // Tile Layer
        L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            maxZoom: 18,
            id: 'mapbox/streets-v11',
            tileSize: 512,
            zoomOffset: -1,
            accessToken: 'pk.eyJ1IjoiYmlzaG9wbSIsImEiOiJjanNjenJ3MHMwcWRyM3lsbmdoaDU3ejI5In0.M1x6KVBqYxC2ro36_Ipz_w'
        }).addTo(map);

        function saveLocation(latitude, longitude, marker) {
            fetch('{{ route("society-location", $society->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    latitude: latitude,
                    longitude: longitude
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    marker.setPopupContent("Thank you for providing the location! We will review it soon.").openPopup();
                    // Optionally disable the click listener so they don't spam markers
                    map.off('click');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                marker.setPopupContent("Upload failed. Please try again.").openPopup();
            });
        }
    </script>
    <table class="table">
        <tr>
            <th class="bg-dark text-white">Address</th><td>{{$society->address}}</td>
        </tr>
        <tr>
            <th class="bg-dark text-white">Website</th><td><a href="{{$society->website}}" target="_blank">{{substr($society->website,strpos($society->website,'//')+2)}}</a></td>
        </tr>
        <tr>
            <th class="bg-dark text-white">Services</th><td>
                @foreach ($society->services as $service)
                    <span class="bg-dark badge text-white mx-1 py-1">{{$service->servicetime}}</span>
                @endforeach
            </td>
        </tr>
        <tr>
            <th class="bg-dark text-white">Preachers</th>
            <td>
                @foreach ($society->preachers->sortBy('person.surname') as $preacher)
                   {{$preacher->person->title}} {{substr($preacher->person->firstname,0,1)}} {{$preacher->person->surname}}@if(!$loop->last), @else.@endif
                @endforeach
            </td>
        </tr>       
        <tr>
            @if (count($ministers)>1)
                <th class="bg-dark text-white">Ministers</th>
            @else 
                <th class="bg-dark text-white">Minister</th>
            @endif
            <td>
                @foreach ($ministers as $minister)
                    {{$minister->title}} {{substr($minister->firstname,0,1)}} {{$minister->surname}}@if(!$loop->last), @else.@endif
                @endforeach
            </td>
        </tr>       
    </table>
    @if ($plans)
        <h4 class="text-center">Upcoming services</h4>
        <table class="table">
            <tr>
                <th class="bg-secondary text-white"></th>
                <th class="bg-secondary text-white">{{date('d M',strtotime($sundays[0]))}}</th>
                <th class="bg-secondary text-white">{{date('d M',strtotime($sundays[1]))}}</th>
            </tr>
            @foreach ($upcoming as $service=>$plan)
                <tr>
                    <td class="bg-secondary text-white">{{$service}}</td>
                    <td>{{$plan[$sundays[0]]}}</td>
                    <td>{{$plan[$sundays[1]]}}</td>
                </tr>
            @endforeach
        </table>
    @endif
@endsection