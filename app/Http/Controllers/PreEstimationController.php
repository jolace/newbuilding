<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use View;
use Input;
use Auth;
use App\Models\Job;
use App\Models\BuildingDescription;
use App\Models\MainService;
use App\Models\SinkKitchen;
use App\Models\OtherKitchen;
use App\Models\Laundry;
use App\Models\Bar;
use App\Models\RecirculationSystem;
use App\Models\HoseBibbs;
use App\Models\InsulationPipes;
use App\Models\WaterSoftner;
use App\Models\Duties;
use App\Models\WaterHeater;
use App\Models\WaterHeaterTank;
use App\Models\Customer;
use App\Models\RoleUser;
use App\Models\PreEstimation;
use App\Models\Estimation;
use App\Models\WaterHeaterTankless;
use App\Models\BathroomType;
use App\Models\BathroomShower;
use Session;
use Datatables;
use DB;
use PDF;

class PreEstimationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
   public $data = array('master_title'=>'Preestimation form');

    public function index()
    {
        $create_customerid = Input::get('customer_id');
        if(!empty( $create_customerid )){
            $this->data['create_customerid'] = $create_customerid;
            $this->data['customer'] = Customer::where('id',$create_customerid)->first();
        }
        else
            return redirect('customers/show')->with('preForm',1)->with("banner_fail","Please create or select customer to continue with pre estimation form.");


        return View::make("pre_estimation.firstForm",$this->data);
    }

    public function show(){
        
        $job_id = Input::get('job_id');
        $job = Job::where('idjob',$job_id)->first();
        $this->data['create_customerid'] = $job->customer_id;
        $this->data['customer'] = Customer::where('id',$job->customer_id)->first();
        $this->data['form'] = $job;
        $this->data['job_id'] = $job_id;
        return View::make("pre_estimation.firstForm_edit",$this->data);
    }

    public function showEstimationForm(){
        
        $job_id = Input::get('job_id');
        $this->data['job_id'] = $job_id;
        $this->data['customer'] = Job::where('idjob',$job_id)->join('customer','customer.id','=','job.customer_id')
        ->select('job.idjob','customer_id','status','job.address','job.city','job.state','first_name','last_name','company_name','zip','primary_phone','other_phone','fax','email')
        ->first();
        $status = $this->data['customer']->status;
        if($status!='estimate') 
            return View::make("pre_estimation.show",$this->data);
        else{
            $this->returnPreEstimation($job_id);
            return View::make("pre_estimation.edit",$this->data);
        }
    }

    public function storePreRequest(){

         // NEW JOB
        $customer_id = Input::get('customer_id');
        
        $job = new Job();
        $job->status = 'preestimate';
        $job->customer_id = $customer_id;
        $job->created_by = Auth::user()->id;
        $job->date = Input::get('date_call');
        $job->source = Input::get('source');
        $job->building_type = Input::get('type_building');
        $job->units_number = Input::get('number_units');
        $job->bath_number = Input::get('number_bathrooms');
        $job->floor_number = Input::get('floor_number');
        $job->foundation = Input::get('foundation');
        $job->interested_in = serialize(Input::get('work_interested'));
        $job->additional_notes = Input::get('additional_notes');
        
        if($job->source == 'referral' || $job->source == 'other')
            $job->source_text = Input::get('source_text');
        

        $add_op = Input::get('job_address');

        if($add_op == 'other'){

            $job->address = Input::get('other_job_address');
            $job->city = Input::get('other_job_city');
            $job->state = Input::get('other_job_state');
            
        }else{

            $customer = Customer::where('id',$customer_id)->first();
            $job->address = $customer->address;
            $job->city = $customer->city;
            $job->state = $customer->state;            
        }
        $job->add_type = $add_op;
        $job->save();
        return redirect('job/addJobinprecalendar')->with('redirect_jobid',$job->idjob);

        
    }

    public function updatePreRequest(){

         // NEW JOB
        $customer_id = Input::get('customer_id');        
        $job_id = Input::get('job_id');;

        $job = Job::where('idjob',$job_id)->first();
        $job->date = Input::get('date_call');
        $job->source = Input::get('source');
        $job->building_type = Input::get('type_building');
        $job->units_number = Input::get('number_units');
        $job->bath_number = Input::get('number_bathrooms');
        $job->floor_number = Input::get('floor_number');
        $job->foundation = Input::get('foundation');
        $job->interested_in = serialize(Input::get('work_interested'));
        $job->additional_notes = Input::get('additional_notes');
        
        if($job->source == 'referral' || $job->source == 'other')
            $job->source_text = Input::get('source_text');
        else
            $job->source_text = '';
        

        $add_op = Input::get('job_address');

        if($add_op == 'other'){

            $job->address = Input::get('other_job_address');
            $job->city = Input::get('other_job_city');
            $job->state = Input::get('other_job_state');
            
        }else{

            $customer = Customer::where('id',$customer_id)->first();
            $job->address = $customer->address;
            $job->city = $customer->city;
            $job->state = $customer->state;            
        }
        $job->add_type = $add_op;
        $job->save();

        if($job->status == 'preestimate' && $job->assign_estimator == 0)
            return redirect('job/addJobinprecalendar')->with('redirect_jobid',$job->idjob);
        else
            return redirect('job/showEstimationJobTable');
    }

    private function returnPreEstimation($job_id){

        //$this->data['job'] = Job::where('idjob',$job_id)->join('customer','customer.id','=','job.customer_id')->first();
        
        // BUILDING DESCRIPTION
        $this->data['building_desc'] = BuildingDescription::where('job_id',$job_id)->get();

        // MAIN SERVICE LINE
        $this->data['main_service'] = MainService::where('job_id',$job_id)->get();

        //$this->data['bathrooms'] = BathroomType::where('job_id',$job_id)->get();

        // KITCHEN
        $this->data['otherKitchen'] = OtherKitchen::where('job_id',$job_id)->get();
        $this->data['sinkKitchen'] = SinkKitchen::where('job_id',$job_id)->get();

        //LAUNDRY
        $this->data['laundry'] = Laundry::where('job_id',$job_id)->get(); 


        //BAR
        $this->data['bar'] = Bar::where('job_id',$job_id)->first();

        //Recirculation System
        $this->data['recirculationSystem'] = RecirculationSystem::where('job_id',$job_id)->first();

        //Hose Bibbs
        $this->data['hose_bibb'] = HoseBibbs::where('job_id',$job_id)->first();

        // Insulation Pipes
        $this->data['insulationPipes'] = InsulationPipes::where('job_id',$job_id)->where('type','!=','tubing')->first();
        $this->data['tubing_pipes'] = InsulationPipes::where('job_id',$job_id)->where('type','tubing')->first();
        //water heater
        $this->data['water_heater'] = WaterHeater::where('job_id',$job_id)->first();

        //water heater
        $this->data['water_heater_tank'] = WaterHeaterTank::where('job_id',$job_id)->first();
         //water heater
        $this->data['water_heater_tankless'] = WaterHeaterTankless::where('job_id',$job_id)->first();
        // Water softner
        $this->data['water_softner'] = WaterSoftner::where('job_id',$job_id)->first();

        // Permit
        $duties = Duties::where('job_id',$job_id)->get();
        $this->data['electrical_ground'] = array();
        foreach($duties as $dut){
            
            if($dut->duty_type == 'electrical_ground')
                $this->data['electrical_ground'] = $dut;
            
            if($dut->duty_type == 'patching')
                $this->data['patching'] = $dut;
            
            if($dut->duty_type == 'permit')
                $this->data['permit'] = $dut;

        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function edit()
    {
        $job_id = Input::get('job_id');
        $this->data['job_id'] = $job_id;
        $this->data['job'] = Job::where('idjob',$job_id)->join('customer','customer.id','=','job.customer_id')
        ->select('job.idjob','customer_id','job.address','job.city','job.state','first_name','last_name','company_name','zip','primary_phone','other_phone','fax','email')
        ->first();
        $this->returnPreEstimation($job_id);               
        return View::make("pre_estimation.edit",$this->data);
    }

    public function PreestimationPDF(){


        $job_id = Input::get('job_id');
        $this->returnPreEstimation($job_id);
    //    dd($this->data);
        $html = view('pdf.preestimation.printForm',$this->data)->render();         
        return PDF::load($html)->show();
    }

    public function createBathrooms($job_id){

        $sink_bath = Input::get('sink1_bath');
        $sink_wall = Input::get('bathroom_sink1_wall_exp');
        $sink_open = Input::get('bathroom_sink1_openingside');
        $sink_comment = Input::get('bathroom_sink1_comment');
        
        $sink2_bath   = Input::get('sink2_bath');
        $sink2_wall = Input::get('bathroom_sink2_wall_exp');
        $sink2_open = Input::get('bathroom_sink2_openingside');
        $sink2_comment = Input::get('bathroom_sink2_comment');
        
        $bidet = Input::get('bidet');
        $bidet_wall = Input::get('bathroom_bidet_wall_exp');
        $bidet_open = Input::get('bathroom_bidet_openingside');
        $bidet_comment = Input::get('bathroom_bidet_comment');
        
        $toilet = Input::get('toilet');
        $toilet_wall = Input::get('bathroom_toilet_wall_exp');
        $toilet_open = Input::get('bathroom_toilet_openingside');
        $toilet_comment = Input::get('bathroom_toilet_comment');
        
        
        $tub = Input::get('tub');
        $tub_wall = Input::get('bathroom_tub_wall_exp');
        $tub_open = Input::get('bathroom_tub_openingside');
        $tub_new = Input::get('bathroom_tub_new_existing');
        $tub_by = Input::get('bathroom_tub_suppliedby');
        $tub_brand = Input::get('con_tub_brand');
        $tub_model = Input::get('con_tub_modeln');
        $tub_comment = Input::get('bathroom_tub_comment');
        
        $shower = Input::get('shower');
        $shower_wall = Input::get('bathroom_shower_wall_exp');
        $shower_open = Input::get('bathroom_shower_openingside');
        $shower_new = Input::get('bathroom_shower_new_existing');
        $shower_by = Input::get('bathroom_shower_suppliedby');        
        $shower_brand = Input::get('con_shower_brand');
        $shower_model = Input::get('con_shower_modeln');
        $shower_comment = Input::get('bathroom_shower_comment');

        $tubshower = Input::get('tubshower');
        $tubshower_wall = Input::get('bathroom_tub_shower_wall_exp');
        $tubshower_open = Input::get('bathroom_tub_shower_openingside');
        $tubshower_new = Input::get('bathroom_tub_shower_new_existing');
        $tubshower_by = Input::get('bathroom_tub_shower_suppliedby');
        $tubshower_brand = Input::get('con_tub_shower_brand');
        $tubshower_model = Input::get('con_tub_shower_modeln');
        $tubshower_comment = Input::get('bathroom_tub_shower_comment');

        $i = 0;

        foreach($sink_bath as $sb){
            
            // sink1
            if($sb == 'yes'){

                $db_sink1 = new BathroomType();
                $db_sink1->bath_type = "sink1";
                $db_sink1->type = $sink_wall[$i];
                if($sink_wall[$i]=='wall')
                    $db_sink1->side = $sink_open[$i];
                $db_sink1->comment = $sink_comment[$i];
                $db_sink1->group_field = $i; 
                $db_sink1->job_id = $job_id;
                $db_sink1->save();
                
            }

            //sink2
            if($sink2_bath[$i] == 'yes'){

                $db_sink1 = new BathroomType();
                $db_sink1->bath_type = "sink2";
                $db_sink1->type = $sink2_wall[$i];
                if($sink2_wall[$i]=='wall')
                    $db_sink1->side = $sink2_open[$i];
                
                $db_sink1->comment = $sink2_comment[$i];
                $db_sink1->group_field = $i; 
                $db_sink1->job_id = $job_id;
                $db_sink1->save();
                
            }

            // bidet
            if($bidet[$i] == 'yes'){

                $db_sink1 = new BathroomType();
                $db_sink1->bath_type = "bidet";
                $db_sink1->type = $bidet_wall[$i];
                if($bidet_wall[$i]=='wall')
                    $db_sink1->side = $bidet_open[$i];
                
                $db_sink1->comment = $bidet_comment[$i];
                $db_sink1->group_field = $i; 
                $db_sink1->job_id = $job_id;
                $db_sink1->save();
                
            }

            // toilet
            if($toilet[$i] == 'yes'){

                $db_sink1 = new BathroomType();
                $db_sink1->bath_type = "toilet";
                $db_sink1->type = $toilet_wall[$i];
                if($toilet_wall[$i]=='wall')
                    $db_sink1->side = $toilet_open[$i];
                
                $db_sink1->comment = $toilet_comment[$i];
                $db_sink1->group_field = $i; 
                $db_sink1->job_id = $job_id;
                $db_sink1->save();
                
            }

            if($tub[$i] == 'yes'){
                $db_sink1 = new BathroomShower();
                $db_sink1->shower_type = "tub";
                $db_sink1->type = $tub_wall[$i];
                if($tub_wall[$i]=='wall')
                    $db_sink1->side = $tub_open[$i];
                $db_sink1->condition_type = $tub_new[$i];
                $db_sink1->brand = $tub_brand[$i];
                $db_sink1->model = $tub_model[$i];
                $db_sink1->suppliedby = $tub_by[$i];
                $db_sink1->comment = $tub_comment[$i];
                $db_sink1->group_field = $i;
                $db_sink1->job_id = $job_id;
                $db_sink1->save();
            }

            if($shower[$i] == 'yes'){
                $db_sink1 = new BathroomShower();
                $db_sink1->shower_type = "shower";
                $db_sink1->type = $shower_wall[$i];
                if($shower_wall[$i]=='wall')
                    $db_sink1->side = $shower_open[$i];
                $db_sink1->condition_type = $shower_new[$i];
                $db_sink1->brand = $shower_brand[$i];
                $db_sink1->model = $shower_model[$i];
                $db_sink1->suppliedby = $shower_by[$i];
                $db_sink1->comment = $shower_comment[$i];
                $db_sink1->group_field = $i;
                $db_sink1->job_id = $job_id;
                $db_sink1->save();
            }

            if($tubshower[$i] == 'yes'){
                $db_sink1 = new BathroomShower();
                $db_sink1->shower_type = "tub_shower";
                $db_sink1->type = $tubshower_wall[$i];
                if($tubshower_wall[$i]=='wall')
                    $db_sink1->side = $tubshower_open[$i];
                $db_sink1->condition_type = $tubshower_new[$i];
                $db_sink1->brand = $tubshower_brand[$i];
                $db_sink1->model = $tubshower_model[$i];
                $db_sink1->suppliedby = $tubshower_by[$i];
                $db_sink1->comment = $tubshower_comment[$i];
                $db_sink1->group_field = $i;
                $db_sink1->job_id = $job_id;
                $db_sink1->save();
            }
            $i = $i + 1;
        }
 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store()
    {
       
       
        //Check or add customer
        $job_id = Input::get('job_id');        
        

        // BUILDING DESCRIPTION
        $this->building_desc($job_id);

        // MAIN SERVICE LINE
        $this->main_service_line($job_id);  

        // BATHROOMS
       // $this->createBathrooms($job_id);
        
        // KITCHEN
        $this->kitchen($job_id);  
        
        //LAUNDRY
        $this->laundry($job_id); 

        //BAR
        $this->bar($job_id);  

        //Recirculation System
       // $this->recirculation($job_id);  

        //Hose Bibbs
        $this->hose_bibb($job_id);  

        // Insulation Pipes
        $this->insulation($job_id);  

        // Insulation Pipes 2
        $this->insulation_yes_no($job_id);

        // Water softner
        $this->water_softner($job_id);  

        //water heater
        $this->water_heater($job_id);

        // Permit
        $permit_value = Input::get('permit_option');
        $this->duty('permit',$permit_value,$job_id);

        // Patching
        $patching_value = Input::get('patching_option');
        $this->duty('patching',$patching_value,$job_id);

        // Patching
        $patching_value = Input::get('electrical_ground');
        $this->duty('electrical_ground',$patching_value,$job_id);
        

        // Inspector
        //$ins_value = Input::get('inspector_option');
        //$this->duty('inspector',$ins_value,$job->idjob);
        
        $estimation = new Estimation();
        $estimation->job_id = $job_id;
        $estimation->plumbing_days = Input::get('plumbing_days');
        $estimation->patching_days = Input::get('patching_days');
        $estimation->date = date("Y-m-d H:i:s");
        $estimation->estimator_id = Auth::user()->id;
        $estimation->save();
        $job = Job::where('idjob',$job_id)->first();
        $job->status = 'estimate';
        $job->save();

        $preestimation = Preestimation::where('job_id',$job_id)->first();
        $preestimation->assign_estimator = 1;
        $preestimation->save();

        //Preestimation::where('job_id',$job_id)->delete();
        return redirect('estimation/showTable')->with('redirect_jobid',$job_id);

       
    }

    public function addDays($job_id){

        $job = Estimation::where('job_id',$job_id)->first();
        if(empty($job))
            $job = new Estimation();
        $job->plumbing_days = Input::get('plumbing_days');
        $job->plumbing_days = Input::get('patching_days');
        $job->save();
    }

    public function addInCalendar(){

        $master_data = $this->data;
        $master_data['master_title'] = "Add job in calendar";
        $master_data['job_id'] = Session::get('redirect_jobid');
        $master_data['jobs'] = array();
        if(empty($master_data['job_id'])) 
            return redirect('preestimation/create'); 
        $master_data['estimators'] = RoleUser::where('role_id',2)->join('users','role_user.user_id','=','users.id')->get();
        return View::make('pre_estimation.addJob_calendar',$master_data);
    }

    private function water_heater($job_id){

        $waterHeater_type = Input::get('water_heater_tankless');
        $waterHeater_tank = Input::get('water_heater_tank');

        $ws = new WaterHeater();
        $ws->type = Input::get('water_hts_wall_expose');
        $ws->patching = Input::get('water_hts_number_patching');
        $ws->side = Input::get('water_hts_opening_side');
        $ws->new_water = Input::get('water_hts_new_existing');
        $ws->relocate = Input::get('water_hts_relocate');
        $ws->owner_supplied = Input::get('water_hts_owner_sup');
        $ws->comment = Input::get('water_hts_comment');
        $ws->job_id = $job_id;
        $ws->save();

       

        if(!empty($waterHeater_tank)){

            $ws = new WaterHeaterTank();
            $ws->type = Input::get('water_ht_wall_expose');
            $ws->patching = Input::get('water_ht_patchingnr');
            $ws->side = Input::get('water_ht_opening_side');
            $ws->condition = Input::get('water_ht_new_existing');
            $ws->relocate = Input::get('water_ht_relocate');
            $ws->owner_supplied = Input::get('water_ht_owner_sup');
            $ws->make = Input::get('water_ht_make');
            $ws->model = Input::get('water_ht_modelnr');
            $ws->serial = Input::get('water_ht_serialnr');            
            $ws->strap = Input::get('water_ht_strap');
            $ws->temperature = Input::get('water_ht_temp_valve');
            $ws->pan = Input::get('water_ht_pan');
            $ws->gas = Input::get('water_ht_gas');
            $ws->size = Input::get('water_ht_size');
            $ws->slab = Input::get('water_ht_slab');
            $ws->stand = Input::get('water_ht_stand');
            $ws->shed = Input::get('water_ht_shed');
            $ws->vent = Input::get('water_ht_vent');
            $ws->comment = Input::get('water_ht_comment');
            $ws->job_id = $job_id;
            $ws->save();
        }

        if(!empty($waterHeater_type)){

            $ws = new WaterHeaterTankless();
            $ws->make = Input::get('water_hts_make');
            $ws->model = Input::get('water_hts_modelnr');
            $ws->serial = Input::get('water_hts_serialnr');
            $ws->gasline = Input::get('water_hts_gasline');
            $ws->gasline_length = Input::get('water_hts_lenght');
            $ws->gasline_size = Input::get('water_hts_size');
            $ws->vent = Input::get('water_hts_vent');
            $ws->job_id = $job_id;
            $ws->save();
        }




    }

    private function duty($type,$value,$job_id){

        $du = new Duties();
        $du->duty_type = $type;
        $du->value = $value;
        $du->contractor_date = date("Y-m-d H:i:s");
        $du->job_id = $job_id;
        $du->save();

    }

    private function water_softner($job_id){

            $ws = new WaterSoftner();
            $ws->type = Input::get('ws_wall_exposed');
            $ws->patching = Input::get('ws_nr_opening');
            $ws->side = Input::get('ws_opening_side');
            $ws->condition = Input::get('ws_new_existing');
            $ws->comment = Input::get('ws_comment');
            $ws->job_id = $job_id;
            $ws->save();      

    }

    private function insulation_yes_no($job_id){
        
        $ins = new InsulationPipes();
        $ins->type = 'tubing';
        $ins->value = Input::get('turn_shut_off_yn');
        $ins->job_id = $job_id;
        $ins->save();

    }

    private function insulation($job_id){
        
        $check = Input::get('insulate_pipes');

        if($check=='yes'){

            $ins = new InsulationPipes();
            $ins->type = Input::get('instal_pipes_check');
            $ins->job_id = $job_id;
            $ins->save();

        }
        

    }
    
    private function hose_bibb($job_id){
        
        $uqy = Input::get('hose_bibbs_unregquantity');
        $qu = Input::get('hose_bibbs_regquantity');
        $hb = new HoseBibbs();
        $hb->regulated = $qu;            
        $hb->unregulated = $uqy;
        $hb->job_id = $job_id;
        $hb->save();
        
    }

    private function recirculation($job_id){

        
        $rec = new RecirculationSystem();
        $rec->size =  Input::get('recsystem_pipe_size');
        $rec->condition =  Input::get('recsystem_new_existing');
        $rec->brand =  Input::get('recsystem_brand');
        $rec->number =  Input::get('recsystem_modelnr');
        $rec->job_id = $job_id;        
        $rec->save();

    }

    private function bar($job_id){
        $bar_sink = Input::get('bar_sink');
        if(!empty($bar_sink)){
           
            $bar = new Bar();
            $bar->type = Input::get('barsink_wall_exposed');
            $bar->patching = Input::get('barsink_number_patching');
            $bar->side = Input::get('barsink_wall_openings');
            $bar->deck_wall = Input::get('barsink_deck_wall');
            $bar->comment = Input::get('barsink_comment');
            $bar->job_id = $job_id;
            $bar->save();       

        }

    }
    private function laundry($job_id){

        $laundry_types = Input::get('landry_type');
        if(empty($laundry_types)) $laundry_types = array();
        foreach($laundry_types as $laundry_type){

            if($laundry_type == 'laundry'){
               
                $laundry = new Laundry();
                $laundry->type_laundry = $laundry_type;
                $laundry->type = Input::get('landry_wall_exposed');
                $laundry->side = Input::get('landry_wall_openning_side');
                $laundry->ls_deckwall = Input::get('landry_deck_wall');
                $temp = Input::get('new_faucet_op');
                if($temp=='Yes'){
                    $temp2 = Input::get('new_faucet_op_value');
                    $laundry->new_faucet = Input::get('new_faucet_op_value');
                    if($temp2=='Contractor'){
                        $laundry->laundry_brand = Input::get('laundry_sink_brand');
                        $laundry->laundry_model = Input::get('laundry_sink_modelnr');
                    }
                }
                $laundry->laundry_faucet = Input::get('new_faucet_op');
                $laundry->comment = Input::get('landry_comment');
                $laundry->job_id =  $job_id;
                $laundry->save();
            }
            if($laundry_type == 'washing'){
               
                $laundry = new Laundry();
                $laundry->type_laundry = $laundry_type;
                $laundry->type = Input::get('landry_wm_wall_exposed');
                $laundry->side = Input::get('landry_wm_wall_opening_side');
                $laundry->wm_recess_box = Input::get('landry_wm_reg_recessbox');
                $laundry->comment = Input::get('landry_wm_commnet');
                $laundry->job_id = $job_id;
                $laundry->save();
            }

        }

    }

    private function kitchen($job_id){

        $kitchen_types = Input::get('kitchen_type');
        if(empty($kitchen_types)) $kitchen_types = array();
        foreach($kitchen_types as $kitchen_type){
            
            if($kitchen_type == 'sink'){
                
                $kitchen = new SinkKitchen();
                $kitchen->type = Input::get('kitchen_sink_wall_exp');
                if($kitchen->type=="wall"){ 
                    $kitchen->side =    Input::get('kitchen_sink_openingside');
                }else
                    $kitchen->side = '';
                $kitchen->deck_wall =  Input::get('kitchen_sink_deck_wall');
                if($kitchen->deck_wall == 'wall'){
                    $kitchen->wall_type =  Input::get('kitchen_sink_wall_options');
                }else
                    $kitchen->wall_type = '';    
                $kitchen->comment =  Input::get('kitchen_sink_comment');     
                $kitchen->job_id =   $job_id;
                $kitchen->save();

            }elseif($kitchen_type == 'dishwasher'){
                
                $kitchen = new OtherKitchen();
                $kitchen->type =  'dishwasher';  
                //$kitchen->condition_type = Input::get('kitchen_dishwasher_y');                  
                $kitchen->comment = Input::get('kitchen_dishwasher_comment');       
                $kitchen->job_id = $job_id;
                $kitchen->save();

            }elseif($kitchen_type == 'refrigerator'){
                
                $kitchen = new OtherKitchen();
                $kitchen->type = 'refrigerator';  
                //$kitchen->condition_type = Input::get('ref_new_ex');
                $kitchen->recess_box = Input::get('kitchen_refrige_regular_recess');      
                $kitchen->comment = Input::get('kitchen_refrige_comment');       
                $kitchen->job_id = $job_id;
                $kitchen->save();

            }elseif($kitchen_type == 'WaterFilter'){
               
                $kitchen = new OtherKitchen();
                $kitchen->type ='water_filter';  
                //$kitchen->condition_type = Input::get('kitchen_waterfilter_ne');                  
                $kitchen->comment = Input::get('kitchen_water_filter_comment');       
                $kitchen->job_id = $job_id;
                $kitchen->save();

            }
        }
    }

    private function main_service_line($job_id){

       
        $main_ser = new MainService();
        
        $main_ser->existing_type = Input::get("main_ser_exist");
        $main_ser->existing_size = Input::get("main_ser_op_size");
        $tmp_repmain = Input::get("rep_main");
        $main_ser->replace_main_type = $tmp_repmain;
        // if replace main type yes then save size
      if($tmp_repmain == 'yes') $rmt = Input::get("replacemainsize_option"); else $rmt = '';
        $main_ser->replace_main_size =  $rmt;
        $main_ser->pipe_type = Input::get('type_pipe_option');
        $main_ser->pipe_copper_type = Input::get('main_type_cooper');
        $main_ser->length = Input::get('main_lenght_num');
        // check if pressure regulator is psi - enter psi number
        $rmt = Input::get('pressure_reg');
        $main_ser->pressure_regulator = $rmt;
        if($rmt == "yes"){
            $main_ser->psi = Input::get('pressure_reg_psi');
            $main_ser->pressure_regulator = Input::get('pressure_reg_opt');
        }         
        $main_ser->pressure_relief = Input::get('press_relief_yn');
        $main_ser->ball_value = Input::get('ball_valve');
        $main_ser->hose_bibb = Input::get('hose_bibb');
        $main_ser->sprinklers = Input::get('tie_ex_sprink');
        $main_ser->hydro = Input::get('hydro_boring_yes');
        $main_ser->cutting = Input::get('concrete_acutting');
        $main_ser->concrete_acutting_num = Input::get('concrete_acutting_num');
        $main_ser->job_id = $job_id;
        $main_ser->save();

    }

    private function building_desc($job_id){

        
        $building_desc = new BuildingDescription();
        
        $typeofbld = Input::get('type_bld_sel');
        $building_desc->type = $typeofbld;
        $building_desc->structure = Input::get('type_srt_sel');
        $building_desc->clearance = Input::get('bld_clearance');
        //levels must be from 1 to 20
        $levels = Input::get('number_levels_tb');
        if($levels>20) $levels = 20; 
        if($levels<1) $levels = 1; 
        $building_desc->levels = $levels;
        // if apartment then save units
        if($typeofbld == 'Apartment' || $typeofbld == 'Condo' || $typeofbld =='Townhouse'){
            $units = Input::get('number_units_tb');
            if($units>20) $units = 20; 
            if($units<1) $units = 1;
            $building_desc->units = $units;  
        }
        $building_desc->job_id = $job_id;
        $building_desc->save();

    }

    public function showJobsInTable(){

         $this->data['count'] = PreEstimation::count();         
         return View::make('pre_estimation.showJobTable',$this->data);
    }

    public function showJobsTableData(){

        $pre = Job::leftJoin('preestimation','job.idjob','=','preestimation.job_id')
                ->leftJoin('customer','job.customer_id','=','customer.id')
                ->leftJoin('users','users.id','=','preestimation.estimator_id')
                ->where('status','preestimate')
                ->select('idjob','job.assign_estimator','customer_id','customer.first_name as cfn','customer.last_name as cln','job.address','job.city','job.state',DB::raw('CONCAT(users.first_name, " ", users.last_name) AS full_name'),'preestimation.date','status')
                ->get();
      
        return Datatables::of($pre)
        ->add_column('job status','<span style="color:blue">pre-estimate</span>')
        ->add_column('action','<a  title="Edit preestimation form" href="{{{ URL::to(\'preestimation/show?job_id=\' . $idjob  ) }}}">
            <span class="fa fa-edit"></span>
            </a>
            @if($status=="preestimate" && $assign_estimator == 1)
            <a  title="Show estimation form" href="{{{ URL::to(\'estimation/show?job_id=\' . $idjob  ) }}}">
            <span class="fa fa-newspaper-o"></span>
            </a>
            @endif
            @if($status=="preestimate" && $assign_estimator == 0)
            <a  title="Assign in calendar" href="{{{ URL::to(\'job/addJobsInpreCalendar?job_id=\' . $idjob  ) }}}">
            <span class="fa fa-calendar"></span>
            </a>
            @endif') 

        ->remove_column('idpreestimation')
        ->remove_column('status')
        ->remove_column('assign_estimator')
        ->make();

    }

   


    
}
