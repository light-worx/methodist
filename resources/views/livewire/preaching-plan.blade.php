<div>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden; /* prevent page scroll */
        }

        .plan-table-wrap {
            height: 100vh;   /* fill the screen */
            overflow: auto;  /* only this scrolls */
        }

        /* sticky headers */
        .plan-table-wrap thead th {
            position: sticky;
            z-index: 2;            /* keep above body cells */
        }

        /* first header row sticks at the very top */
        .plan-table-wrap thead tr:first-child th {
            top: 0;
            z-index: 3;
            background: #fff;      /* ensure it doesn't show rows underneath */
        }

        /* second header row sticks below the first */
        .plan-table-wrap thead tr:nth-child(2) th {
            top: 2rem;             
        }

        .cell-spinner {
            z-index: 10;
            pointer-events: none; /* lets the user still interact with selects once they appear */
        }
    </style>
    <div class="table-responsive plan-table-wrap">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="bg-white">
                    <a class="text-black text-decoration-none" href="{{ url('/admin/circuits/' . $circuit->id) }}" title="Back to circuit page">
                        <i class="text-black bi bi-house"></i> Home
                    </a>
                    </th>
                    <th class="bg-white text-center" colspan="100%">
                    {{ $circuit->circuit }} Circuit {{ $circuit->reference }} Preaching plan ({{ $period }})
                    </th>
                </tr>
                <tr>
                    <th class="bg-dark text-white" colspan="2">
                    <a href="{{ route('filament.admin.resources.circuits.plan', ['record' => $circuit->id, 'today' => date('Y-m-d',strtotime($firstday . '- 3 months'))]) }}">
                        <i class="text-white bi bi-arrow-left h4"></i>
                    </a>
                    <a href="/plan/{{ $circuit->slug }}/{{ $today }}" class="mx-3 btn btn-sm btn-secondary">View PDF</a>
                    <a href="{{ route('filament.admin.resources.circuits.plan', ['record' => $circuit->id, 'today' => date('Y-m-d',strtotime($firstday . '+ 3 months'))]) }}">
                        <i class="text-white bi bi-arrow-right h4"></i>
                    </a>
                    </th>
                    @foreach($dates as $date)
                    <th class="bg-dark text-white text-center">
                        {{ date('j M', strtotime($date)) }}
                        @php $mw = array_search($date, $midweeks); @endphp
                        <div class="text-sm text-center">{{ $mw }}</div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($services as $society=>$times)
                    @foreach ($times as $time=>$service)
                        <tr>
                            @if(in_array($service['id'], $authorisedServices))
                                <td>{{ $society }}</td><td>{{ $time }}</td>
                            @else
                                <td style="background-color: #ccc;">{{ $society }}</td><td style="background-color: #ccc;">{{ $time }}</td>
                            @endif
                            @foreach($dates as $thisd)
                                @php $date = $thisd; @endphp
                                @if(in_array($service['id'], $authorisedServices))
                                    <td class="items-center text-xs" wire:key="cell-{{ $service['id'] }}-{{ $date }}">
                                @else
                                    <td style="background-color: #ccc;" class="items-center text-xs" wire:key="cell-{{ $service['id'] }}-{{ $date }}">
                                @endif
                                    @if($editingCell === "{$service['id']}-{$date}")
                                        <div 
                                            x-data="{}"
                                            data-cell-id="{{ $service['id'] }}-{{ $date }}"
                                            class="flex flex-col"
                                        >
                                            {{-- Spinner overlay for this cell --}}
                                            <div wire:loading.delay.longer 
                                                wire:target="startEditing, saveAndClose" 
                                                class="cell-spinner position-absolute top-50 start-50 translate-middle">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>

                                            <select wire:model="selectedServiceType" wire:change="saveAndClose" class="form-select form-select-sm">
                                                @foreach($serviceTypes as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <select wire:model="selectedPreacherId" wire:change="saveAndClose" class="form-select form-select-sm">
                                                <option value="">-- Select --</option>
                                                @foreach($preachers as $cat=>$preachertype)
                                                    <optgroup label="{{$cat}}">
                                                        @foreach ($preachertype as $preacher)
                                                            <option value="{{ $preacher['id'] }}">{{ $preacher['name'] }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div 
                                        x-data="{}"
                                        @if(in_array($service['id'], $authorisedServices))
                                            @click="$wire.startEditing('{{ $service['id'] }}', '{{ $date }}')" 
                                            class="cursor-pointer rounded text-center"
                                            title="Click to edit"
                                        @else
                                            class="rounded text-center" 
                                            title="You don't have permission to edit this service"
                                        @endif
                                        >
                                            @if(isset($schedule[$service['id']][$date]))
                                                <div class="flex flex-col text-center">
                                                    @if(!empty($schedule[$service['id']][$date]['servicetype']))
                                                        <span class="items-center text-xs">
                                                            {{ $schedule[$service['id']][$date]['servicetype'] }}
                                                        </span>
                                                    @endif
                                                    @if(!empty($schedule[$service['id']][$date]['preacher_name']))
                                                        <span class="items-center text-sm">{{ $schedule[$service['id']][$date]['preacher_name'] }}</span>
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                            @else
                                                —
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @empty
                    <tr><td class="text-center" colspan="100%">This table is empty because you need to add societies to your circuit and services to your societies.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>