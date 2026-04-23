@extends('vendor.pwa.layouts.app')

@section('content')
    
    <link rel="stylesheet" href="{{ asset('css/leaflet.css') }}">
    <script src="{{ asset('js/leaflet.js') }}"></script>
    @if(!$society->latitude || !$society->longitude)
        <div class="alert alert-info">
            We don't have a location for this society yet. If you know the society's location, please add it by clicking the spot on the map below. You can zoom in and out, move around and change to satelite image (using the control in the top right hand corner) to help you find the location.
        </div>
    @endif
    <div style="height:400px" id="map"></div>

    <script>
        // 1. Define Tile Layers
        var accessToken = 'pk.eyJ1IjoiYmlzaG9wbSIsImEiOiJjanNjenJ3MHMwcWRyM3lsbmdoaDU3ejI5In0.M1x6KVBqYxC2ro36_Ipz_w';

        var streets = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
            attribution: 'Map data &copy; OpenStreetMap, Imagery © Mapbox',
            maxZoom: 18,
            id: 'mapbox/streets-v11',
            tileSize: 512,
            zoomOffset: -1,
            accessToken: accessToken
        });

        var satellite = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
            attribution: 'Map data &copy; OpenStreetMap, Imagery © Mapbox',
            maxZoom: 18,
            id: 'mapbox/satellite-streets-v11', // Mapbox Satellite with labels
            tileSize: 512,
            zoomOffset: -1,
            accessToken: accessToken
        });

        // 2. Initialize Variables
        var lat = {{ $society->latitude ?? 'null' }};
        var lng = {{ $society->longitude ?? 'null' }};
        var map;
        var markerLayer = L.layerGroup(); // Group to hold our marker(s)

        // 3. Setup Map Logic
        if (lat && lng) {
            map = L.map('map', {
                center: [lat, lng],
                zoom: 15,
                layers: [streets, markerLayer] // Load streets and markers by default
            });
            L.marker([lat, lng]).addTo(markerLayer);
        } else {
            map = L.map('map', {
                center: [{{$dlat}}, {{$dlon}}],
                zoom: 13,
                layers: [streets, markerLayer]
            });
            
            // Attempt to find user location, but fallback to $dlat/$dlon
            map.locate({setView: true, maxZoom: 15});

            map.on('click', function(e) {
                var newLat = e.latlng.lat;
                var newLng = e.latlng.lng;

                // Clear existing temp markers in the layer group before adding a new one
                markerLayer.clearLayers();

                var tempMarker = L.marker([newLat, newLng]).addTo(markerLayer)
                    .bindPopup("Saving location...")
                    .openPopup();

                saveLocation(newLat, newLng, tempMarker);
            });
        }

        // 4. Add Layer Control
        var baseMaps = {
            "Street View": streets,
            "Satellite": satellite
        };

        var overlayMaps = {
            "Location Marker": markerLayer
        };

        L.control.layers(baseMaps, overlayMaps).addTo(map);

        // 5. Ajax Save Function
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
                    map.off('click'); // Prevent further clicks after success
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