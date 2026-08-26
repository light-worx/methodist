<?php

namespace App\Livewire;

use App\Models\Circuit;
use App\Models\Midweek;
use Livewire\Component;
use App\Models\Plan;
use App\Models\Society;
use App\Models\Person;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PreachingPlan extends Component
{
    public $schedule = [];
    public $services = [];
    public $midweeks = [];
    public $dates = [];
    public $preachers = [];
    public $editingCell = null;
    public $selectedPreacherId = null;
    public $serviceids;
    public $selectedServiceType = null;
    public $circuit;
    public $period;
    public $firstday;
    public $today;
    public $home_link;

    // Fill-quarter state
    public $fillingService = null;

    // service_id => ['society_id' => .., 'time' => 'HH:MM'] lookup, used for clash detection
    public $serviceMeta = [];

    // person_ids that clash with the service/date currently being edited
    public $clashingPreacherIds = [];

    // Service type options
    public $serviceTypes = [];
    public $authorisedServices = [];
    //protected $listeners = ['clickedOutside' => 'saveAndClose'];

    public function mount($record, $today=null)
    {
        if (!$today){
            $today=date('Y-m-d');
        }
        $this->today=$today;;
        //$this->serviceTypes=array_merge([''=>''],$settings->service_types);
        $this->circuit=Circuit::find($record);
        if ($this->circuit->servicetypes){
            $this->serviceTypes=array_merge($this->serviceTypes,$this->circuit->servicetypes);
        }
        ksort($this->serviceTypes);
        // Get all societies
        $societies = Society::withWhereHas('services')->where('circuit_id',$record)->orderBy('society')->get();
        foreach ($societies as $society){
            foreach ($society->services as $service){
                $this->services[$society->society][$service->servicetime]['id']=$service->id;
                $this->services[$society->society][$service->servicetime]['time']=$service->servicetime;
                $this->serviceids[]=$service->id;
                $this->serviceMeta[$service->id] = [
                    'society_id' => $society->id,
                    'time' => $service->servicetime,
                ];
            }
            ksort($this->services[$society->society]);
        }
        // Get all preachers
        $persons=$this->circuit->persons->sortBy(['surname','firstname']);
        $this->preachers['Circuit Ministers']=array();
        $this->preachers['Local Preachers']=array();
        $this->preachers['Supernumerary Ministers']=array();
        $this->preachers['Guest Preachers']=array();
        foreach ($persons as $person){
            if ((in_array("Minister",json_decode($person->pivot->status))) or (in_array("Superintendent",json_decode($person->pivot->status)))){
                $this->preachers['Circuit Ministers'][$person->id]=['name' => substr($person->firstname,0,1) . " " . $person->surname,'id' => $person->id];
            } elseif (in_array("Guest",json_decode($person->pivot->status))){
                $this->preachers['Guest Preachers'][$person->id]=['name' => substr($person->firstname,0,1) . " " . $person->surname,'id' => $person->id];
            } elseif (in_array("Supernumerary",json_decode($person->pivot->status))){
                $this->preachers['Supernumerary Ministers'][$person->id]=['name' => substr($person->firstname,0,1) . " " . $person->surname,'id' => $person->id];
            }  elseif (json_decode($person->pivot->is_preacher)==1) {
                if ($person->preacher){
                    if ($person->preacher->status == "guest"){
                        $this->preachers['Guest Preachers'][$person->id]=['name' => substr($person->firstname,0,1) . " " . $person->surname,'id' => $person->id];
                    } else {
                        $this->preachers['Local Preachers'][$person->id]=['name' => substr($person->firstname,0,1) . " " . $person->surname,'id' => $person->id];
                    }
                }
            }
        }
        // Generate the upcoming 13 Sundays
        $this->generateSundays();
        
        // Load the current schedule
        $this->loadSchedule();
        $this->authorisedServices = $this->getUserAuthorisedServices();
    }

    private function getUserAuthorisedServices()
    {
        $allsocieties=Society::where('circuit_id',$this->circuit->id)->get()->pluck('id');
        $this->home_link="/admin";
        if (auth()->user()->hasRole('super_admin')){
            return Service::whereIn('society_id',$allsocieties)->get()->pluck('id')->toArray();
        } else if (auth()->user()->circuits){
            if (in_array($this->circuit->id,auth()->user()->circuits)){
                $this->home_link="/admin/circuits/" . $this->circuit->id . "/edit";
                return Service::whereIn('society_id',$allsocieties)->get()->pluck('id')->toArray();
            } else {
                return [];
            }
        } else if (auth()->user()->societies){
            $services = Service::whereIn('society_id',auth()->user()->societies)->get()->pluck('id')->toArray();
            $authorised=[];
            foreach ($services as $service){
                if (!in_array($service,auth()->user()->exclude_services ?? [])){
                    $authorised[]=$service;
                }
            }
            return $authorised;
        } else {
            return [];
        }
    }
    
    public function generateSundays()
    {
        $thismonth=intval(date('n',strtotime($this->today)));
        $thisyear=intval(date('Y',strtotime($this->today)));
        $yy=$thisyear;
        if ($this->circuit->plan_month==3){
            $plans[0]=[3,4,5];
            $plans[1]=[6,7,8];
            $plans[2]=[9,10,11];
            $plans[3]=[12,1,2];
            if ($thismonth<3){
                $yy=$thisyear-1;
            }
        } elseif ($this->circuit->plan_month==2){
            $plans[0]=[2,3,4];
            $plans[1]=[5,6,7];
            $plans[2]=[8,9,10];
            $plans[3]=[11,12,1];
            if ($thismonth<2){
                $yy=$thisyear-1;
            }
        } else {
            $plans[0]=[1,2,3];
            $plans[1]=[4,5,6];
            $plans[2]=[7,8,9];
            $plans[3]=[10,11,12];
        }
        foreach ($plans as $kk=>$pp){
            if (in_array($thismonth,$pp)){
                $plan=$plans[$kk];
            }
        }
        if ($plan[0]<10){
            $firstday = $yy . '-0' . $plan[0] . '-01';
        } else {
            $firstday = $yy . '-' . $plan[0] . '-01';
        }
        $lastday=date('Y-m-d',strtotime($firstday . " + 3 months"));
        $dow=intval(date('N',strtotime($firstday)));
        if ($dow==7){
            $firstsunday=$firstday;
        } else {
            $firstsunday=date("Y-m-d",strtotime($firstday)+86400*(7-$dow));
        }
        $dates[]=$firstsunday;
        for ($w=1;$w<15;$w++){
            if (in_array(intval(date('n',strtotime($firstsunday)+86400*7*$w)),$plan)){
                $dates[$w]=date("Y-m-d",strtotime($firstsunday)+86400*7*$w);
            }
        }
        $this->midweeks = $this->calculate_midweeks($firstday,$lastday);
        if (count($this->midweeks)){
            $dates=array_merge($dates,$this->midweeks);
        }
        sort($dates);
        $this->dates=$dates;
        $this->firstday=$firstday;
        $this->period = date("j F Y",strtotime($firstday)) . " - " . date("j F Y",strtotime($lastday . '- 1 day'));
    }

    public function calculate_midweeks($start,$end){
        $years=array();
        $years[]=date('Y',strtotime($start));
        $years[]=date('Y',strtotime($end));
        array_unique($years);
        $mws = Midweek::whereIn('midweek',$this->circuit->midweeks)->get();
        $dates = array();
        foreach ($mws as $mw){
            if ($mw->type=="fixed"){
                foreach ($years as $yr){
                    $temp=date('Y-m-d',strtotime($yr . '-' . $mw->month . '-' . $mw->day));
                    if (($temp>=$start) and ($temp<=$end) and (date('w',strtotime($temp)>0) and (!in_array($temp,$dates)))){
                        $dates[$mw->midweek]=$temp;
                    }
                }
            } else {
                foreach ($years as $yr){
                    $easter = DB::table('eastersundays')
                        ->whereYear('eastersunday', $yr)
                        ->value('eastersunday');
                    $temp=Carbon::parse($easter)->addDays($mw->offset)->format('Y-m-d');
                    if (($temp>=$start) and ($temp<=$end) and (!in_array($temp,$dates))){
                        $dates[$mw->midweek]=$temp;
                    }
                }
            }
        }
        return $dates;
    }
    
    public function loadSchedule()
    {
        $this->schedule = [];
        foreach ($this->services as $society){
            foreach ($society as $service){
                $this->schedule[$service['id']] = [];
                foreach ($this->dates as $date) {
                    $this->schedule[$service['id']][$date] = null;
                }
            }
        }
        
        // Load actual schedule data
        if ($this->serviceids){
            $scheduleData = Plan::with('person')
                ->whereIn('service_id', $this->serviceids)
                ->whereIn('servicedate', $this->dates)
                ->where(function ($query) {
                    $query->where('person_id', '>', 0)
                          ->orWhere('servicetype', '<>', '');
                })
                ->get();
            
            foreach ($scheduleData as $item) {
                if (($item->person_id) and ($item->person)){
                    $preachername=substr($item->person->firstname,0,1) . " " . $item->person->surname;
                } else {
                    $preachername="";
                }
                $this->schedule[$item->service_id][$item->servicedate] = [
                    'preacher_id' => $item->person_id,
                    'preacher_name' => $preachername,
                    'servicetype' => $item->servicetype ?? ''
                ];
            }
        } else {
            $this->schedule=array();
        }
    }
    
    /**
     * How close together (in minutes) two services at different societies
     * need to be, on the same date, to count as a clash for one preacher.
     */
    private function clashWindowMinutes()
    {
        return (int) ($this->circuit->clash_window ?? 90);
    }

    /**
     * Turn a free-text servicetime (e.g. "08:30") into minutes-since-midnight.
     * Returns null if it can't be parsed, so callers can skip the check
     * gracefully rather than false-positive on odd data.
     */
    private function minutesFromTimeString($time)
    {
        if (!$time) {
            return null;
        }
        $ts = strtotime($time);
        if ($ts === false) {
            return null;
        }
        return ((int) date('H', $ts) * 60) + (int) date('i', $ts);
    }

    /**
     * Which preachers (person_ids) cannot be assigned to $service_id on
     * $date because they already have a service at a DIFFERENT society
     * on that date whose start time falls within the clash window.
     * Services at the same society are always exempt.
     *
     * Checked system-wide (not just this circuit) since guest/travelling
     * preachers can clash with bookings in other circuits' plans too.
     */
    public function getClashingPreacherIds($service_id, $date)
    {
        $service_id = (int) $service_id;

        if (!isset($this->serviceMeta[$service_id])) {
            return [];
        }

        $targetSocietyId = $this->serviceMeta[$service_id]['society_id'];
        $targetMinutes = $this->minutesFromTimeString($this->serviceMeta[$service_id]['time']);

        if ($targetMinutes === null) {
            return [];
        }

        $window = $this->clashWindowMinutes();

        $otherPlans = Plan::query()
            ->join('services', 'services.id', '=', 'plans.service_id')
            ->where('plans.servicedate', $date)
            ->where('plans.service_id', '<>', $service_id)
            ->whereNotNull('plans.person_id')
            ->select('plans.person_id', 'services.society_id', 'services.servicetime')
            ->get();

        $clashing = [];

        foreach ($otherPlans as $row) {
            if ((int) $row->society_id === (int) $targetSocietyId) {
                continue; // same society - not a clash, however close together
            }

            $otherMinutes = $this->minutesFromTimeString($row->servicetime);
            if ($otherMinutes === null) {
                continue;
            }

            if (abs($targetMinutes - $otherMinutes) < $window) {
                $clashing[] = $row->person_id;
            }
        }

        return array_values(array_unique($clashing));
    }

    public function startEditing($service_id, $date)
    {
        // Check if user is authorized to edit this service
        if (!in_array($service_id, $this->authorisedServices)) {
            // Optional: Flash a message or log the attempt
            session()->flash('message', 'You do not have permission to edit this service.');
            return;
        }

        $this->editingCell = "$service_id-$date";
        $this->selectedPreacherId = $this->schedule[$service_id][$date]['preacher_id'] ?? null;
        $this->selectedServiceType = $this->schedule[$service_id][$date]['servicetype'] ?? '';
        $this->clashingPreacherIds = $this->getClashingPreacherIds($service_id, $date);

        // Dispatch browser event to set up outside click detection
        $this->dispatch('cell-editing-started', ['cellId' => $this->editingCell]);
    }
    
    public function updatedSelectedPreacherId()
    {
        if ($this->editingCell) {
            // Auto-save after selection change
            $this->saveChanges();
        }
    }
    
    public function updatedSelectedServiceType()
    {
        if ($this->editingCell) {
            // Auto-save after selection change
            $this->saveChanges();
        }
    }

    public function saveChanges()
    {
        if (!$this->editingCell) {
            return;
        }
        $service_id=substr($this->editingCell,0,strpos($this->editingCell,"-"));
        $date=substr($this->editingCell,1+strpos($this->editingCell,"-"));

        if ($this->selectedPreacherId) {
            $clashes = $this->getClashingPreacherIds($service_id, $date);
            if (in_array((int) $this->selectedPreacherId, $clashes)) {
                session()->flash('message', 'That preacher already has a service at another society within the clash window on this date, so they can\'t be assigned here.');
                // Revert to whatever was actually saved before, don't touch the database
                $this->selectedPreacherId = $this->schedule[$service_id][$date]['preacher_id'] ?? null;
                return;
            }
        }

        $del=Plan::where('service_id',$service_id)->where('servicedate',$date)->delete();
        // Update the database
        if ($this->selectedPreacherId and $this->selectedServiceType){
            Plan::Create( ['service_id' => $service_id, 'servicedate' => $date, 'person_id' => $this->selectedPreacherId, 'servicetype' => $this->selectedServiceType]);
        } elseif ($this->selectedServiceType){
            Plan::Create( ['service_id' => $service_id, 'servicedate' => $date, 'person_id' => null, 'servicetype' => $this->selectedServiceType]);
        } elseif ($this->selectedPreacherId){
            Plan::Create( ['service_id' => $service_id, 'servicedate' => $date, 'person_id' => $this->selectedPreacherId, 'servicetype' => null]);
        } 
        
        // Update the local data
        if ($this->selectedPreacherId) {
            if (isset($this->preachers['Circuit Ministers'][$this->selectedPreacherId])){
                $preacher = $this->preachers['Circuit Ministers'][$this->selectedPreacherId];
            } elseif (isset($this->preachers['Local Preachers'][$this->selectedPreacherId])){
                $preacher = $this->preachers['Local Preachers'][$this->selectedPreacherId];
            } elseif (isset($this->preachers['Supernumerary Ministers'][$this->selectedPreacherId])){
                $preacher = $this->preachers['Supernumerary Ministers'][$this->selectedPreacherId];
            } elseif (isset($this->preachers['Guest Preachers'][$this->selectedPreacherId])){
                $preacher = $this->preachers['Guest Preachers'][$this->selectedPreacherId];
            }
            
            $this->schedule[$service_id][$date] = [
                'preacher_id' => $this->selectedPreacherId,
                'preacher_name' => $preacher['name'] ?? 'Unknown',
                'servicetype' => $this->selectedServiceType
            ];
        } else {
            // If no preacher selected, set to null
            if (!$this->selectedServiceType){
                $this->schedule[$service_id][$date] = null;
            } else {
                $this->schedule[$service_id][$date] = null;
                $this->schedule[$service_id][$date]['servicetype'] = $this->selectedServiceType;
            }
        }
    }

    public function saveAndClose()
    {
        if ($this->editingCell) {
            $this->saveChanges();
            $this->editingCell = null;
            $this->selectedPreacherId = null;
            $this->selectedServiceType = null;
            $this->clashingPreacherIds = [];
        }
    }

    /**
     * Whether this service row still has no preacher assigned on any date
     * in the current quarter, and is therefore eligible for a bulk fill.
     */
    public function rowIsFillable($service_id)
    {
        if (!isset($this->schedule[$service_id])) {
            return true;
        }

        foreach ($this->schedule[$service_id] as $entry) {
            if (!empty($entry['preacher_id'])) {
                return false;
            }
        }

        return true;
    }

    public function startFillQuarter($service_id)
    {
        // wire:click passes this in as a string; cast to int so strict
        // comparisons against $service['id'] elsewhere (int, from the DB)
        // match correctly.
        $service_id = (int) $service_id;

        if (!in_array($service_id, $this->authorisedServices)) {
            session()->flash('message', 'You do not have permission to edit this service.');
            return;
        }

        if (!$this->rowIsFillable($service_id)) {
            // Row already has assignments - bulk fill is no longer available
            return;
        }

        // Close any open single-cell editor first
        $this->editingCell = null;

        $this->fillingService = $service_id;
    }

    public function cancelFillQuarter()
    {
        $this->fillingService = null;
    }

    public function applyFillQuarter($preacherId, $serviceType = null)
    {
        $preacherId = (int) $preacherId;

        if (!$this->fillingService || !$preacherId) {
            return;
        }

        if (!in_array($this->fillingService, $this->authorisedServices)) {
            session()->flash('message', 'You do not have permission to edit this service.');
            $this->cancelFillQuarter();
            return;
        }

        if (!$this->rowIsFillable($this->fillingService)) {
            session()->flash('message', 'This service already has some preachers assigned, so it can no longer be bulk-filled.');
            $this->cancelFillQuarter();
            return;
        }

        $service_id = $this->fillingService;

        // Find the preacher's display name from the already-loaded lists
        $preacherName = 'Unknown';
        foreach ($this->preachers as $group) {
            if (isset($group[$preacherId])) {
                $preacherName = $group[$preacherId]['name'];
                break;
            }
        }

        $filledCount = 0;
        $clashSkippedCount = 0;

        foreach ($this->dates as $date) {
            // Belt-and-braces: never overwrite a date that already has a preacher
            if (!empty($this->schedule[$service_id][$date]['preacher_id'])) {
                continue;
            }

            // Skip (don't fill) any date where this preacher already has a
            // service at another society within the clash window
            $clashes = $this->getClashingPreacherIds($service_id, $date);
            if (in_array($preacherId, $clashes)) {
                $clashSkippedCount++;
                continue;
            }

            $thisServiceType = $serviceType
                ?: ($this->schedule[$service_id][$date]['servicetype'] ?? null);

            Plan::where('service_id', $service_id)->where('servicedate', $date)->delete();

            Plan::create([
                'service_id'  => $service_id,
                'servicedate' => $date,
                'person_id'   => $preacherId,
                'servicetype' => $thisServiceType,
            ]);

            $this->schedule[$service_id][$date] = [
                'preacher_id'   => $preacherId,
                'preacher_name' => $preacherName,
                'servicetype'   => $thisServiceType,
            ];

            $filledCount++;
        }

        $this->cancelFillQuarter();

        if ($clashSkippedCount > 0) {
            session()->flash(
                'message',
                "Filled {$filledCount} date(s). Skipped {$clashSkippedCount} date(s) where {$preacherName} already has a service at another society within the clash window - please fill those manually with someone else."
            );
        }
    }
    
    public function render()
    {
        return view('livewire.preaching-plan');
    }    
}