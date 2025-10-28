<x-layouts.web pageName="{{$circuit->circuit}} Circuit {{$circuit->reference}}">
    <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-circuit-tab" data-bs-toggle="pill" data-bs-target="#pills-circuit" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Circuit</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-societies-tab" data-bs-toggle="pill" data-bs-target="#pills-societies" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Societies</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-preaching-tab" data-bs-toggle="pill" data-bs-target="#pills-preaching" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Preaching</button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-circuit" role="tabpanel" aria-labelledby="pills-circuit-tab">
            <div style="height:400px" id="map"></div>
            <script>
                var map = L.map('map');
                L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                    maxZoom: 18,
                    id: 'mapbox/streets-v11',
                    tileSize: 512,
                    zoomOffset: -1,
                    accessToken: 'pk.eyJ1IjoiYmlzaG9wbSIsImEiOiJjanNjenJ3MHMwcWRyM3lsbmdoaDU3ejI5In0.M1x6KVBqYxC2ro36_Ipz_w'
                }).addTo(map);
                @foreach ($circuit->societies as $soc)
                    var marker = L.marker([{{$soc->latitude}}, {{$soc->longitude}}]).bindPopup('<a href="{{url()->current() . '/' . $soc->id}}">{{$soc->society}}</a>').addTo(map);
                @endforeach
                var markers = [
                @foreach ($circuit->societies as $soc)
                    [{{$soc->latitude}}, {{$soc->longitude}}],
                @endforeach
                ];
                var bounds = new L.LatLngBounds(markers);
                map.fitBounds(bounds, {padding: [25,25]});
            </script>
            <h3 class="text-md-start text-center">Ministers</h3>
            <div class="row">
                @foreach ($ministers as $minister)
                    @if ((in_array('Minister',json_decode($minister->pivot->status))) or (in_array('Superintendent',json_decode($minister->pivot->status))))
                        <div class="rounded col text-small text-md-start text-center">
                            <a href="{{url('/ministers/' . $minister->id)}}">
                                @if ($minister->image)
                                    <img class="rounded" width="100px" src="{{url('/storage/public/' . $minister->image)}}">
                                @else 
                                    <img class="rounded" width="100px" src="{{url('/methodist/images/blank.png')}}">
                                @endif
                                <br>
                                <small>{{$minister->firstname}} {{$minister->surname}}
                                    @if (in_array('Superintendent',json_decode($minister->pivot->status)))
                                        <br><span class="bg-dark badge text-white text-small">Superintendent</span>
                                    @endif
                                </small>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
            <table class="table">
                @foreach ($leaders as $category=>$persons)
                    @if ($category <> "Guest")
                        <tr class="table-primary">
                            <th class="text-center">{{$category}}@if(count($persons)>1)s @endif</th>
                        </tr>
                        <tr>
                            <td>
                            @foreach ($persons as $person)
                                {{$person->title}} {{substr($person->firstname,0,1)}}. {{$person->surname}}@if(!$loop->last), @else.@endif
                            @endforeach
                            </td>
                        </tr>
                    @endif
                @endforeach
            </table>
        </div>
        <div class="tab-pane fade" id="pills-societies" role="tabpanel" aria-labelledby="pills-societies-tab">
            <ul class="list-unstyled">
                @foreach ($circuit->societies->sortBy('society') as $society)
                    <li><a href="{{url('/' . $circuit->district->slug . '/' . $circuit->slug . '/' . $society->slug)}}">{{$society->society}}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="tab-pane fade" id="pills-preaching" role="tabpanel" aria-labelledby="pills-preaching-tab">
            <h3 class="text-md-start text-center"><a target="_blank" href="{{url('/') . '/plan/' . $circuit->slug . '/' . date('Y-m-d') }}">Preaching plan</a></h3>
            <h3 class="text-md-start text-center">Lectionary readings</h3>
            <livewire:service-details :service="$lects" />
        </div>
    </div>
</x-layouts.web>

