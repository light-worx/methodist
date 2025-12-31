<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Classes\LectionaryService;
use App\Classes\tFPDF;
use App\Models\Person;
use App\Models\Circuit;
use App\Models\District;
use App\Models\Idea;
use App\Models\Meeting;
use App\Models\Midweek;
use App\Models\Plan;
use App\Models\Society;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public $circuit;
    public $plandate;
    public $dates;
    public $preachers;
    public $midweeks;
    public $ministers;
    public $supernumeraries;
    public $localpreachers;

    public function __construct()
    {
        //
    }

    public function circuit($district, $circuit){
        $data['circuit']=Circuit::with('district','societies','persons')->whereSlug($circuit)->first();
        $data['leaders']=array();
        $data['ministers']=array();
        if ($data['circuit']){
            foreach ($data['circuit']->persons as $person){
                if ($person->minister){
                    $data['ministers'][$person->surname.$person->firstname]=$person;
                } elseif ($person->pivot->status){
                    foreach (json_decode($person->pivot->status) as $lead){
                        if ($lead <> "Preacher"){
                            $data['leaders'][$lead][$person->surname.$person->firstname]=$person;
                        }
                    }
                } 
            }
        }
        foreach ($data['leaders'] as $ll=>$vv){
            ksort($data['leaders'][$ll]);
        }
        ksort($data['ministers']);
        $data['lects']=$this->get_lectionary();
        return view('web.circuit',$data);
    }

    public function district($district){
        $data['district']=District::with('circuits.societies')->withWhereHas('circuits.persons.minister')->whereSlug($district)->first();
        $data['bishop']=Person::find($data['district']->bishop);
        $data['ministers']=array();
        foreach ($data['district']->circuits as $circ){
            foreach ($circ->persons as $person){
                if ((isset($person->minister)) and ($person->minister->status=="Minister") and ($person->id <> $data['district']->bishop)){
                    $data['ministers'][$person->surname . $person->firstname . $person->id] = $person;
                }
            }
        }
        ksort($data['ministers']);
        $data['pageName'] = $data['district']->district . ' District';
        return view('web.district',$data);
    }

    public function editplan($record,$today=""){
        $data['today']=$today;
        $data['record']=$record;
        return view('preaching-plan',$data);
    }

    public function home()
    {
        $data['districts']=District::orderBy('id')->get();
        $data['lects']=$this->get_lectionary();
        return view('web.home',$data);
    }

    public function ideas(){
        return view('web.ideas');
    }

    public function storeidea(Request $request)
    {
        $validated = $request->validate([
            'circuit_id' => 'required|exists:circuits,id',
            'tags' => 'required|array|min:1',
            'tags.*' => 'string',
            'description' => 'required|string|min:10',
            'email' => 'required|email|max:199',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'circuit_id.required' => 'Please select a circuit.',
            'circuit_id.exists' => 'The selected circuit is invalid.',
            'tags.required' => 'Please select at least one subject.',
            'tags.min' => 'Please select at least one subject.',
            'description.required' => 'Please provide a description of your ministry idea.',
            'description.min' => 'The description must be at least 10 characters.',
            'email.required' => 'Please provide your email address.',
            'email.email' => 'Please provide a valid email address.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPEG, PNG, JPG, or GIF file.',
            'image.max' => 'The image must not be larger than 2MB.',
        ]);

        // Handle image upload
        $imagePath = $request->hasFile('image') 
            ? $request->file('image')->store('ministry-ideas', 'public') 
            : null;

        // Create the idea (unpublished by default)
        $idea = Idea::create([
            'circuit_id' => $validated['circuit_id'],
            'description' => $validated['description'],
            'email' => $validated['email'],
            'image' => $imagePath,
            'published' => false,
        ]);

        $tagIds = [];

        foreach ($validated['tags'] as $tagInput) {
            // Detect new tags prefixed with "new:"
            if (str_starts_with($tagInput, 'new:')) {
                $tagName = trim(substr($tagInput, 4));

                // Create or find existing tag by slug
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                );

                $tagIds[] = $tag->id;
            } else {
                // Existing tag IDs from the select
                $tagIds[] = $tagInput;
            }
        }

        // Attach all tags to the idea via the taggables table
        if (!empty($tagIds)) {
            $idea->tags()->sync($tagIds);
        }

        return redirect()
            ->route('ideas')
            ->with('success', 'Thank you! Your ministry idea has been submitted and will be reviewed by our team before being published.');
    }

    public function lectionary($sunday=""){
        $data['lects']=$this->get_lectionary($sunday);
        return view('web.lectionary',$data);
    }

    private function get_lectionary(){
        $lects=new LectionaryService();
        return $lects->getReadings(date('Y-m-d'));
    }

    public function pdf($circuit,$plandate){
        $this->plandate=$plandate;
        $this->circuit=Circuit::whereSlug($circuit)->first();
        $this->getdates();
        $rows=$this->getrows($this->circuit->id,$this->dates);
        $pdf = new tFPDF();
        $pdf->AddPage('L');
        $imagepath=base_path('/resources/images/mcsa.png');
        $pdf->Image($imagepath,10,5,19);
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->text(35,11,"THE METHODIST CHURCH OF SOUTHERN AFRICA");
        $startdate = date('F Y',strtotime($this->dates[0]));
        $startday = date('Y-m-d',strtotime($plandate));
        $enddate=date('F Y',strtotime($this->dates[count($this->dates)-1]));
        $endday=date('Y-m-31',strtotime($plandate . '+ 2 months'));
        if (substr($startdate,-4)==substr($enddate,-4)){
            $startdate=substr($startdate,0,-5);
        }
        $title=$this->circuit->circuit . " Circuit " . $this->circuit->reference . " Preaching Plan";
        $filename=$this->circuit->reference . "plan_" . date('M',strtotime($this->dates[0])) . date('MY',strtotime($enddate));
        $pdf->SetFont('Helvetica', '', 15);
        $pdf->text(35,17.5,$title);   
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->text(35,23,$startdate . " - " . $enddate . "");
        $pdf->SetTitle($title);

        // Legend
        $yadd=0;
        $defaultservicetypes=setting('default_service_types');
        if ($this->circuit->servicetypes){
            $stypes=$this->circuit->servicetypes;    
        } else {
            if (count($defaultservicetypes)){
                $stypes=$defaultservicetypes;
            } else {
                $stypes=array();
            }
        }
        if (count($stypes)){
            if (count($defaultservicetypes)){
                foreach ($defaultservicetypes as $kk=>$st){
                    if (!isset($stypes[$kk])){
                        $stypes[$kk]=$st;
                    }
                }
            }
            ksort($stypes);
            $i=1;
            if (count($stypes) % 2 == 0){
                $stheight=count($stypes)*2;
            } else {
                $stheight=(count($stypes)+1)*2;
            }
            $pdf->rect(200,5,87,$stheight);
            foreach ($stypes as $key=>$val){
                if ($i % 2 == 0){
                    $xadd=43;
                } else {
                    $yadd=$yadd+3;
                    $xadd=0;
                }
                $pdf->SetFont('Helvetica', 'B', 8);
                $pdf->text(201+$xadd,5+$yadd,$key);
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->text(209+$xadd,5+$yadd,$val);
                $i++;
            }
        }
        $pdf->SetAutoPageBreak(true, 0);
        $pdf->SetFont('Helvetica', 'B', 9);
        $xx=51;
        $x=$xx;
        $yy=33;
        $y=$yy;
        $xgap = (287-$xx)/count($this->dates);
        foreach ($this->dates as $col){
            if (date('w',strtotime($col))=="0"){
                $pdf->setxy($xx,$yy-3);
            } else {
                $tmw=Midweek::where('servicedate',$col)->first();
                $pdf->setxy($xx,$yy-2);
                $font=8;
                $size="unknown";
                do {
                    $pdf->SetFont('Helvetica', '', $font);
                    $width=$pdf->GetStringWidth($tmw->midweek);
                    if ($width < $xgap){
                        $pdf->cell($xgap+1,0,$tmw->midweek,0,0,'C');
                        $size="known";
                        $font=8;
                    } else {
                        $font=$font-0.5;
                    }
                } while ($size=="unknown");
                $pdf->SetFont('Helvetica', 'B', 9);                    
                $pdf->setxy($xx,$yy-6);
            }
            $pdf->cell($xgap,0,date('j M',strtotime($col)),0,0,'C');
            $xx=$xx + $xgap;
        }
        $maxx=$xx;
        $ycount=count($rows);
        foreach ($rows as $rr){
            $ycount=$ycount+count($rr)-1;
        }
        if ($ycount>0){
            $ygap = (190-$yy)/$ycount;
        } else {
            $ygap = 25;
        }
        if ($ygap > 12){
            $ygap=12;
        }
        foreach ($rows as $soc=>$row){
            $pdf->line(10,$yy,$maxx,$yy);
            $pdf->text(12,1+$yy+$ygap/2*count($row),$soc);
            $first=true;
            foreach ($row as $service=>$plans){
                if (!$first){
                    $pdf->line(35,$yy,$maxx,$yy);
                } else {
                    $first=false;
                }
                $pdf->text($x-12,1+$yy+$ygap/2,$service);
                $xp=$x;
                foreach ($plans as $plan){
                    $font=8;
                    $size="unknown";
                    $pdf->SetFont('Helvetica', '', 8);
                    if ($plan['servicetype']==""){
                        $pdf->setxy($xp,$yy + $ygap/2);
                    } else {
                        $pdf->setxy($xp,$yy+ $ygap*3/4);
                    }
                    if ($plan['preacher']<>""){
                        do {
                            $pdf->SetFont('Helvetica', '', $font);
                            $width=$pdf->GetStringWidth($this->getpreacher($plan['preacher']));
                            if ($width < $xgap){
                                $pdf->cell($xgap,0,$this->getpreacher($plan['preacher']),0,0,'C');
                                $size="known";
                                $font=8;
                            } else {
                                $font=$font-0.5;
                            }
                        } while ($size=="unknown");
                    }
                    $pdf->SetFont('Helvetica', 'B', 9);
                    $pdf->setxy($xp,1+$yy+$ygap/4);
                    $pdf->cell($xgap,0,$plan['servicetype'],0,0,'C');
                    $xp=$xp+$xgap;
                }
                $yy=$yy+$ygap;
                $pdf->SetFont('Helvetica', 'B', 9);
            }
        }
        $maxy=$yy;
        $pdf->line(10,$yy,$maxx,$yy);
        $pdf->line(10,$maxy,10,$y);
        $pdf->line(35,$maxy,35,$y);
        foreach ($this->dates as $c2){
            $pdf->line($x,$maxy,$x,$y);
            $x=$x+$xgap;
        }
        $pdf->line($x,$maxy,$x,$y);
        // Second page
        $pdf->AddPage('L');
        $pdf->Image($imagepath,10,5,19);
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->text(35,11,"THE METHODIST CHURCH OF SOUTHERN AFRICA");
        $pdf->SetFont('Helvetica', '', 15);
        $pdf->text(35,17.5,$title);   
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->text(35,23,$startdate . " - " . $enddate . "");
        $yy=32;
        $xx=10;
        
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->text($xx,$yy,"Presiding Bishop: " . setting('presiding_bishop'));
        $pdf->text($xx,$yy+4.5,"General Secretary: " . setting('general_secretary'));
        $bishop=Person::find($this->circuit->district->bishop);
        $pdf->text($xx,$yy+9,"District Bishop: " . $bishop->name);
        $yy=$yy+20;
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->text($xx,$yy-4.5,"Circuit Ministers");
        $pdf->SetFont('Helvetica', '', 9);
        if (isset($this->ministers)){
            foreach ($this->ministers as $minister){
                $sup="";
                if ($minister->phone<>"" and $this->circuit->showphone) {
                    $sup.= " (" . $minister->phone . ")";
                }
                if (is_array(json_decode($minister->pivot->status)) and (in_array("Superintendent",json_decode($minister->pivot->status)))){
                    $sup.= " (Supt)";
                }
                $pdf->text($xx,$yy,$minister->title . " " . substr($minister->firstname,0,1) . " " . $minister->surname . $sup);
                $yy=$yy+4.5;
                if ($yy>199) {
                    $yy=36;
                    $xx=$xx+70;
                }
            }
        }
        if (isset($this->supernumeraries)){
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->text($xx,$yy+2,"Supernumerary Ministers");
            $yy=$yy+6;
            $pdf->SetFont('Helvetica', '', 9);
            foreach ($this->supernumeraries as $super){
                $sup="";
                if ($super->phone<>"" and $this->circuit->showphone){
                    $sup.= " (" . $super->phone . ")";
                }
                $pdf->text($xx,$yy,$super->title . " " . substr($super->firstname,0,1) . " " . $super->surname . $sup);
                $yy=$yy+4.5;
                if ($yy>199) {
                    $yy=36;
                    $xx=$xx+70;
                }
            }   
        }
        // Lay leaders
        $roles = setting('circuit_leadership_roles');
        foreach ($roles as $role){
            $leaders=DB::table('persons')->join('circuit_person','persons.id','=','circuit_person.person_id')->where('circuit_person.circuit_id',$this->circuit->id)->whereJsonContains('status',$role)->orderBy('surname')->get();
            if (count($leaders)){
                $pdf->SetFont('Helvetica', 'B', 10);
                if (count($leaders)>1){
                    $pdf->text($xx,$yy+2,$role . "s");
                } else {
                    $pdf->text($xx,$yy+2,$role);
                }
                $yy=$yy+6;
                $pdf->SetFont('Helvetica', '', 9);    
                foreach ($leaders as $leader){
                    $sup="";
                    if ($leader->phone<>"" and $this->circuit->showphone){
                        $sup.= " (" . $leader->phone . ")";
                    }
                    $pdf->text($xx,$yy,$leader->title . " " . substr($leader->firstname,0,1) . " " . $leader->surname . $sup);
                    $yy=$yy+4.5;
                    if ($yy>199) {
                        $yy=36;
                        $xx=$xx+70;
                    }
                }   
            }
        }
        $preachers=array();
        if (isset($this->localpreachers)){
            foreach ($this->localpreachers as $ps){
                if ($ps->preacher){
                    if ($ps->title <>""){
                        $tp=$ps->title . " " . substr($ps->firstname,0,1) . " " . $ps->surname;
                    } else {
                        $tp=substr($ps->firstname,0,1) . " " . $ps->surname;
                    }
                    $pn=array(
                        'fname'=>$tp,
                        'induction'=>$ps->preacher->induction,
                        'phone'=>$ps->phone
                    );
                    if (isset($ps->preacher->society)){
                        $preachers[$ps->preacher->society->society][$ps->preacher->status][]=$pn;
                    }
                }
            }
            ksort($preachers);
        }
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->text($xx,$yy+2,"LOCAL PREACHERS");
        $yy=$yy+4.5;

        // Preacher leaders
        $roles = setting('preaching_leadership_roles');
        foreach ($roles as $role){
            $leaders=Person::whereHas('circuits',function ($q) { $q->where('circuits.id',$this->circuit->id); })->withWhereHas('preacher', function($q) use($role) { $q->whereJsonContains('leadership',$role); })->orderBy('surname')->get();
            if (count($leaders)){
                $pdf->SetFont('Helvetica', 'B', 10);
                if (count($leaders)>1){
                    $pdf->text($xx,$yy+2,$role . "s");
                } else {
                    $pdf->text($xx,$yy+2,$role);
                }
                $yy=$yy+6;
                $pdf->SetFont('Helvetica', '', 9);    
                foreach ($leaders as $leader){
                    $sup="";
                    if ($leader->phone<>"" and $this->circuit->showphone){
                        $sup.= " (" . $leader->phone . ")";
                    }
                    $pdf->text($xx,$yy,$leader->title . " " . substr($leader->firstname,0,1) . " " . $leader->surname . $sup);
                    $yy=$yy+4.5;
                    if ($yy>199) {
                        $yy=36;
                        $xx=$xx+70;
                    }
                }   
            }
        }
        //    $yy=$yy+2.5;
        $pdf->SetFont('Helvetica', '', 9);
        $psociety="";
        foreach ($preachers as $psoc=>$statuses){
            if (($psoc <> $psociety) and ((isset($statuses['preacher'])) or (isset($statuses['trial'])) or (isset($statuses['emeritus'])))){
                $pdf->SetFont('Helvetica', 'B', 10);
                $pdf->text($xx,$yy+2,$psoc);
                $yy=$yy+6;
                $psoc=$psociety;
                $pdf->SetFont('Helvetica', '', 9);
            }
            foreach ($statuses as $stat=>$group) {
                foreach ($group as $preacher){
                    if ($stat <> 'guest'){
                        $fin=$preacher['fname'];
                        if ($preacher['phone'] <> "" and $this->circuit->showphone){
                            $fin.=" (" . $preacher['phone'] . ")";
                        }
                        if ($stat=="trial"){
                            $fin.=" [Trial]";
                        } elseif ($stat=="note"){
                            $fin.=" [Note]";
                        } elseif ($preacher['induction'] <> ""){
                            $fin.= " [" . $preacher['induction'] . "]";
                        }
                        if ($stat=="emeritus"){
                            $fin.="*";
                        }
                        $pdf->text($xx,$yy,$fin);
                        $yy=$yy+4.5;
                        if ($yy>199) {
                            $yy=32;
                            $xx=$xx+70;
                        }
                    }
                }
            }
        }
        $nstartday=date('Y-m-d',strtotime($startday . " - 3 months"));
        $nendday=$startday;
        $pendday=date('Y-m-d',strtotime($endday . " + 3 months"));
        $pstartday=date('Y-m-d',strtotime($endday . " + 1 day"));
        $meetings=Meeting::where('circuit_id',$this->circuit->id)->with('society')
            ->where(function($q)use ($startday,$endday) {
                $q->where('meetingdate','>=',$startday)->where('meetingdate','<=',$endday)->where('quarter','current');
            })
            ->orWhere(function($q2)use ($pstartday,$pendday) {
                $q2->where('meetingdate','>=',$pstartday)->where('meetingdate','<=',$pendday)->where('quarter','previous');
            })->orWhere(function($q3)use ($nstartday,$nendday) {
                $q3->where('meetingdate','>=',$nstartday)->where('meetingdate','<=',$nendday)->where('quarter','next');
            })->orderBy('meetingdate')->get();
        if (count($meetings)){
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->text($xx,$yy+2,"CIRCUIT MEETINGS");
            $yy=$yy+6;
            $pdf->SetFont('Helvetica', '', 9);
            foreach ($meetings as $meeting){
                if ($meeting->circuit_id == $this->circuit->id){
                    $sup="";
                    if ($meeting->society_id<>""){
                        $sup.= " (" . $meeting->society->society . ")";
                    }
                    $pdf->text($xx,$yy,date('d M H:i',strtotime($meeting->meetingdate)) . " " . $meeting->description . $sup);
                    $yy=$yy+4.5;
                    if ($yy>199) {
                        $yy=36;
                        $xx=$xx+70;
                    }
                }
            }   
        }
        $pdf->Output('I',$filename);
        exit;
    }

    private function getpreacher($id){
        $preacher = Person::find($id);
        if ($preacher){
            return substr($preacher->firstname,0,1) . " " . $preacher->surname;
        }
    }

    private function getrows(){
        $circuit=Circuit::with('societies.services','persons.preacher.society')->where('id',$this->circuit->id)->first();
        $persons=$circuit->persons->sortBy(['surname','firstname']);
        foreach ($persons as $person){
            if ((in_array("Minister",json_decode($person->pivot->status))) or (in_array("Superintendent",json_decode($person->pivot->status)))){
                $this->ministers[]=$person;
            } elseif (in_array("Supernumerary",json_decode($person->pivot->status))){
                $this->supernumeraries[]=$person;
            } elseif (in_array("Preacher",json_decode($person->pivot->status))){
                $this->localpreachers[]=$person;
            }
            $this->preachers[$person->id] = ['name' => substr($person->firstname,0,1) . " " . $person->surname,'id' => $person->id];
        }
        $data=array();
        foreach ($circuit->societies as $soc){
            if (isset($soc->services[0])){
                foreach ($soc->services as $service){
                    foreach ($this->dates as $week){
                        $plan = Plan::where('service_id',$service->id)->where('servicedate',$week)->first();
                        if ($plan){
                            $data[$soc->society][date('H:i',strtotime($service->servicetime))][$week]['preacher']=$plan->person_id;
                            $data[$soc->society][date('H:i',strtotime($service->servicetime))][$week]['servicetype']=$plan->servicetype;
                        } else {
                            $data[$soc->society][date('H:i',strtotime($service->servicetime))][$week]['preacher']="";
                            $data[$soc->society][date('H:i',strtotime($service->servicetime))][$week]['servicetype']="";
                        }
                    }
                }
                ksort($data[$soc->society]);
            }
        }
        ksort($data);
        return $data;
    }

    public function getDates()
    {
        $thismonth=intval(date('n',strtotime($this->plandate)));
        $thisyear=intval(date('Y',strtotime($this->plandate)));
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
        $this->midweeks=Midweek::where('servicedate','>=',$firstday)->where('servicedate','<',$lastday)->orderBy('servicedate','ASC')->get()->pluck('servicedate','midweek')->toArray();
        foreach ($this->midweeks as $desc=>$mw){
            if (isset($this->circuit->midweeks)){
                if (in_array($desc,$this->circuit->midweeks)){
                    $dates[]=$mw;
                }
            }
        }
        sort($dates);
        $this->dates=$dates;
    }

    public function minister($id){
        $data['minister']=Person::with('minister','circuitroles.circuit')->whereId($id)->first();
        $circuitroles=$data['minister']->circuitroles;
        foreach ($circuitroles as $role){
            $societies=array();
            if (isset($role->societies)){
                foreach ($role->societies as $soc){
                    $societies[] = Society::find($soc)->society;
                }
                $data['societies'][$role->circuit_id]=$societies;
            } else {
                $data['societies']=[];
            }
        }
        return view('web.minister',$data);
    }

    public function offline(){
        return view('web.offline');
    }

    public function society($district, $circuit, $society){
        $district_id=District::whereSlug($district)->first()->id;
        $circuit_id=Circuit::whereSlug($circuit)->where('district_id',$district_id)->first()->id;
        $data['society']=Society::with('circuit','services','preachers.person')->where('circuit_id',$circuit_id)->whereSlug($society)->first();
        $data['services']=$data['society']->services->pluck('id','servicetime')->toArray();
        $jsonid=json_encode($data['society']->id);
        $data['ministers']=DB::table('circuit_person')
            ->join('persons', 'circuit_person.person_id', '=', 'persons.id')
            ->whereJsonContains('societies', [$jsonid])->get();
        $data['sundays']=array();
        $data['sundays'][]=date('Y-m-d',strtotime('Sunday'));
        $data['sundays'][]=date('Y-m-d',604800+strtotime('Sunday'));
        $plans=Plan::with('service','person')->whereIn('service_id',$data['services'])->whereIn('servicedate',$data['sundays'])->get();
        if (count($plans)){
            $data['plans']=true;
        } else {
            $data['plans']=false;
        }
        foreach ($data['services'] as $time=>$val){
            foreach ($data['sundays'] as $sunday){
                $data['upcoming'][$time][$sunday]="";
            }
        }
        ksort($data['upcoming']);
        foreach ($plans as $plan){
            $data['upcoming'][$plan->service->servicetime][$plan->servicedate]=$plan->person->title . " " . substr($plan->person->firstname,0,1) . " " . $plan->person->surname;
        }
        return view('web.society',$data);
    }

}
