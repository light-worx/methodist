<div class="max-w-3xl mx-auto">
    <h5 class="text-xl font-bold mb-2">
        {{date('d F',strtotime('Sunday'))}} ({{ $service['sunday'][date('Y-m-d',strtotime('Sunday'))]['service'] ?? 'Unnamed Service' }})
    </h5>
    @if(!empty($service['sunday']))
        <div class="mb-6">
            <ul class="list-unstyled">
                @foreach ($service['sunday'][date('Y-m-d',strtotime('Sunday'))] as $key => $reading)
                    @if (in_array($key,['ot','psalm','nt','gospel']))
                        @if (str_contains($reading, ' or '))
                            @php
                                $readings = explode(' or ', $reading);
                            @endphp
                            <li>
                                @foreach($readings as $ndx=>$subReading)
                                    <a target="_blank" href="https://www.biblegateway.com/passage/?search={{ $subReading }}">{{ $subReading }}</a>
                                    @if ($ndx<count($readings)-1)
                                        or
                                    @endif
                                @endforeach
                            </li>
                        @else
                            <li>
                                <a target="_blank" href="https://www.biblegateway.com/passage/?search={{ $reading }}">{{ $reading }}</a>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Midweek Readings --}}
    @if(!empty($service['midweek']['readings']))
        <div class="mb-6">
            <h4 class="text-lg font-semibold mb-2">Midweek Services</h4>
            <ul class="list-unstyled">
                @foreach($service['midweek']['readings'] as $midweek)
                    <li class="pt-1"><strong>{{ ucfirst($midweek['day_name']) }}</strong></li>
                    @foreach ($midweek['readings'] as $key => $reading)
                        @if (str_contains($reading, ' or '))
                            @php
                                $readings = explode(' or ', $reading);
                            @endphp
                            <li>
                                @foreach($readings as $ndx=>$subReading)
                                    <a target="_blank" href="https://www.biblegateway.com/passage/?search={{ $subReading }}">{{ $subReading }}</a>
                                    @if ($ndx<count($readings)-1)
                                        or
                                    @endif
                                @endforeach
                            </li>
                        @else
                            <li>
                                <a target="_blank" href="https://www.biblegateway.com/passage/?search={{ $reading }}">{{ $reading }}</a>
                            </li>
                        @endif
                    @endforeach
                @endforeach
            </ul>
        </div>
    @endif
</div>
