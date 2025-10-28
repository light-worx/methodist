<div class="max-w-3xl mx-auto">
    <h5 class="text-xl font-bold mb-2">
        {{ \Carbon\Carbon::parse($service['sunday'])->toFormattedDateString() }} ({{ $service['liturgical_day'] ?? 'Unnamed Service' }})
    </h5>
    @if(!empty($service['sunday_readings']))
        <div class="mb-6">
            <ul class="list-unstyled">
                @foreach ($service['sunday_readings'] as $key => $reading)
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
            </ul>
        </div>
    @endif

    {{-- Midweek Readings --}}
    @if(!empty($service['midweek_readings']))
        <div class="mb-6">
            <h4 class="text-lg font-semibold mb-2">Midweek Services</h4>
            <ul class="list-unstyled">
                @foreach($service['midweek_readings'] as $midweek)
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
