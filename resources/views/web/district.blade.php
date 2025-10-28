<x-layouts.web pageName="{{$district->district}} District">
    <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-district" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">District</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-circuits" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Circuits</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-ministers" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Ministers</button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-district" role="tabpanel" aria-labelledby="pills-district-tab">
            @if ($bishop and $bishop->image)
                <a href="{{url('/ministers/' . $bishop->id)}}">
                    <img class="rounded" width="100px" src="{{url('/storage/public/' . $bishop->image)}}">
                </a>
            @elseif ($bishop)
                <a href="{{url('/ministers/' . $bishop->id)}}">
                    <img class="rounded" width="100px"  src="{{url('/methodist/images/blank.png')}}">
                </a>
            @endif
            <h6 class="mt-3"><span class="bg-dark badge text-white text-small">District Bishop</span> {{$bishop->name ?? ''}}</h6>
            <span class="bg-dark badge text-white text-small">District Office</span>
            {!!$district->contact!!}
            <div style="height:400px;" id="map" class="mb-3"></div>
            <script>
                var map = L.map('map').setView([{{$district->latitude}}, {{$district->longitude}}], 15);
                L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                    maxZoom: 18,
                    id: 'mapbox/streets-v11',
                    tileSize: 512,
                    zoomOffset: -1,
                    accessToken: 'pk.eyJ1IjoiYmlzaG9wbSIsImEiOiJjanNjenJ3MHMwcWRyM3lsbmdoaDU3ejI5In0.M1x6KVBqYxC2ro36_Ipz_w'
                }).addTo(map);
                var marker = L.marker([{{$district->latitude}}, {{$district->longitude}}]).bindPopup('District Office').addTo(map);
            </script>        
        </div>
        <div class="tab-pane fade" id="pills-circuits" role="tabpanel" aria-labelledby="pills-circuits-tab">
            <ul class="list-unstyled">
                @foreach ($district->circuits->sortBy('reference') as $circuit)
                    @if ($circuit->active)
                        <li><a href="{{url('/' . $district->slug . '/' . $circuit->slug)}}">{{$circuit->reference}} {{$circuit->circuit}}</a></li>
                    @else
                        <li>{{$circuit->reference}} {{$circuit->circuit}}</li>
                    @endif
                @endforeach
            </ul>
        </div>
        <div class="tab-pane fade" id="pills-ministers" role="tabpanel" aria-labelledby="pills-ministers-tab">
            <div class="row">
                @foreach ($ministers as $minister)
                    @if ($minister->minister->active)
                        <div class="col text-center">
                            <a href="{{url('/ministers/' . $minister->id)}}">
                                @if ($minister->image)
                                    <img class="rounded" width="100px" src="{{url('/storage/public/' . $minister->image)}}">
                                @else 
                                    <img class="rounded" width="100px" src="{{url('/methodist/images/blank.png')}}">
                                @endif
                                <br>
                                <small>{{$minister->firstname}} {{$minister->surname}}</small>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.web>