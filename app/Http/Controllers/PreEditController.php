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

class PreEditController extends Controller
{
  
    public function index()
    {	//dd(Input::all());
    	$job_id = Input::get('job_id');
    	if(!empty($job_id)){
            //building
        	$this->building_desc($job_id);
            // main service line
            $this->main_service_line($job_id);
            //kitchen
        	$this->kitchen($job_id);
            //laundry
            $this->laundry($job_id);
            //bar
            $this->bar($job_id);
            //water heater
            $this->hose_bibb($job_id);
            $this->insulation($job_id);
            $this->insulation_tubing($job_id);
            //miss
            $this->waterHeater($job_id);
            $this->WaterHeaterTank($job_id);
            $this->WaterHeaterTankless($job_id);
            //water softne
            $this->w_softner($job_id);
            //electrical
            $this->electrical($job_id);
            //permit
            $this->permit($job_id);
            //patching
            $this->patching($job_id);
            
        	//return redirect('estimation/show')->with('job_id',$job_id)->with("banner_success","Successfully updated");
		 }
    }

     private function insulation_tubing($job_id){
        
        $ins = InsulationPipes::where('job_id',$job_id)->where('type','tubing')->first();
        if(empty($ins))
            $ins = new InsulationPipes();
        $ins->value = Input::get('turn_shut_off_yn');
        $ins->job_id = $job_id;
        $ins->save();

    }

    private function insulation($job_id){
        
        $check = Input::get('insulate_pipes');

        if($check=='yes'){

            $ins = InsulationPipes::where('job_id',$job_id)->where('type','!=','tubing')->first();
            if(empty($ins))
                $ins = new InsulationPipes();
            $ins->type = Input::get('instal_pipes_check');
            $ins->job_id = $job_id;
            $ins->save();

        }else{
            InsulationPipes::where('job_id',$job_id)->where('type','!=','tubing')->delete();
        }
        

    }

    private function hose_bibb($job_id){
        
        $uqy = Input::get('hose_bibbs_unregquantity');
        $qu = Input::get('hose_bibbs_regquantity');
        $hb = HoseBibbs::where('job_id',$job_id)->first();
        if(empty($hb))
            $hb = new HoseBibbs();
        $hb->regulated = $qu;            
        $hb->unregulated = $uqy;
        $hb->job_id = $job_id;
        $hb->save();
        
    }

    private function WaterHeaterTankless($job_id){

            if(!Input::has('water_heater_tankless')){
                WaterHeaterTankless::where('job_id',$job_id)->delete();
            }else{

                WaterHeaterTankless::where('job_id',$job_id)->first();
                if(empty($ws))
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

    private function WaterHeaterTank($job_id){
            
            if(!Input::has('water_heater_tank')){
                WaterHeaterTank::where('job_id',$job_id)->delete();
            }else{

                WaterHeaterTank::where('job_id',$job_id)->first();
                if(empty($ws))
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
    }

    private function waterHeater($job_id){
        
        $ws = WaterHeater::where('job_id',$job_id)->first();
        if(empty($ws))
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

    }

    private function w_softner($job_id){

        $ws = WaterSoftner::where('job_id',$job_id)->first();
        if(empty($ws))
            $ws = new WaterSoftner();
        
        $ws->type = Input::get('ws_wall_exposed');
        $ws->patching = Input::get('ws_nr_opening');
        $ws->side = Input::get('ws_opening_side');
        $ws->condition = Input::get('ws_new_existing');
        $ws->comment = Input::get('ws_comment');
        $ws->job_id = $job_id;
        $ws->save();

    }

    private function electrical($job_id){

        $du = Duties::where('duty_type','electrical_ground')->where('job_id',$job_id)->first();
        if(empty($du)){
            $du = new Duties();
            $du->duty_type = 'electrical_ground';
        }
        $du->value = Input::get('electrical_ground');
        $du->contractor_date = date("Y-m-d H:i:s");
        $du->job_id = $job_id;
        $du->save();

    }

    private function permit($job_id){

        $du = Duties::where('duty_type','permit')->where('job_id',$job_id)->first();
        if(empty($du)){
            $du = new Duties();
            $du->duty_type = 'permit';
        }
        
        $du->value = Input::get('permit_option');
        $du->contractor_date = date("Y-m-d H:i:s");
        $du->job_id = $job_id;
        $du->save();

    }

    private function patching($job_id){

        $du = Duties::where('duty_type','patching')->where('job_id',$job_id)->first();
        if(empty($du)){
            $du = new Duties();
            $du->duty_type = 'patching';
        }
        
        $du->value = Input::get('patching_option');
        $du->contractor_date = date("Y-m-d H:i:s");
        $du->job_id = $job_id;
        $du->save();

    }

    private function building_desc($job_id){

      
        $building_desc = BuildingDescription::where('job_id',$job_id)->first();

        if(!empty($building_desc)){
            
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
	        }else{
	        	$building_desc->units = 1;
	        }
	        $building_desc->job_id = $job_id;
	        $building_desc->save();
    	}
    }

    private function main_service_line($job_id){

       
        $main_ser = MainService::where('job_id',$job_id)->first();
        if($main_ser){

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
	        }else{
	        	$main_ser->psi = '';
	            $main_ser->pressure_regulator = '';
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
    }

    private function kitchen($job_id){
    	$all_types = array('sink','dishwasher','refrigerator','WaterFilter');
        $kitchen_types = Input::get('kitchen_type');
        
        if(empty($kitchen_types)) $kitchen_types = array();
        foreach($kitchen_types as $kitchen_type){
            
            if($kitchen_type == 'sink'){
                
                $kitchen = SinkKitchen::where('job_id',$job_id)->first();
                if(empty($kitchen))
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
                
                $kitchen =  OtherKitchen::where('job_id',$job_id)->where('type','dishwasher')->first();
                if(empty($kitchen))
                	$kitchen = new OtherKitchen();
                $kitchen->type =  'dishwasher';  
                //$kitchen->condition_type = Input::get('kitchen_dishwasher_y');                  
                $kitchen->comment = Input::get('kitchen_dishwasher_comment');       
                $kitchen->job_id = $job_id;
                $kitchen->save();

            }elseif($kitchen_type == 'refrigerator'){
                
                $kitchen =  OtherKitchen::where('job_id',$job_id)->where('type','refrigerator')->first();
                if(empty($kitchen))
                	$kitchen = new OtherKitchen();
                $kitchen->type = 'refrigerator';  
                //$kitchen->condition_type = Input::get('ref_new_ex');
                $kitchen->recess_box = Input::get('kitchen_refrige_regular_recess');      
                $kitchen->comment = Input::get('kitchen_refrige_comment');       
                $kitchen->job_id = $job_id;
                $kitchen->save();

            }elseif($kitchen_type == 'WaterFilter'){
              
                $kitchen =  OtherKitchen::where('job_id',$job_id)->where('type','water_filter')->first();
                
                if(empty($kitchen))
                	$kitchen = new OtherKitchen();
                $kitchen->type ='water_filter';  
               // dd($kitchen);
                //$kitchen->condition_type = Input::get('kitchen_waterfilter_ne');                  
                $kitchen->comment = Input::get('kitchen_water_filter_comment');       
                $kitchen->job_id = $job_id;                
                $kitchen->save();

            }
        }

        foreach($all_types as $at){
        	if(!in_array($at, $kitchen_types)){
        		if($at=='sink'){
        			SinkKitchen::where('job_id',$job_id)->delete();
        		}elseif($at=='WaterFilter'){
        			OtherKitchen::where('job_id',$job_id)->where('type','water_filter')->delete();
        		}else
        			OtherKitchen::where('job_id',$job_id)->where('type',$at)->delete();

        	}
        }
    }


    private function laundry($job_id){

        $laundry_types = Input::get('landry_type');
        if(empty($laundry_types)) $laundry_types = array();
        foreach($laundry_types as $laundry_type){

            if($laundry_type == 'laundry'){
                
                $laundry = Laundry::where('job_id')->where('laundry_type',$laundry_type)->first();
                if(empty($laundry)) 
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
               
                $laundry = Laundry::where('job_id')->where('laundry_type',$laundry_type)->first();
                if(empty($laundry))
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

    private function bar($job_id){
        $bar_sink = Input::get('bar_sink');
        if(!empty($bar_sink)){
           
            $bar = Bar::where('job_id',$job_id)->first();
            if(empty($bar))
                $bar = new Bar();
            $bar->type = Input::get('barsink_wall_exposed');
            $bar->patching = Input::get('barsink_number_patching');
            $bar->side = Input::get('barsink_wall_openings');
            $bar->deck_wall = Input::get('barsink_deck_wall');
            $bar->comment = Input::get('barsink_comment');
            $bar->job_id = $job_id;
            $bar->save();       

        }else{
            Bar::where('job_id',$job_id)->delete();
        }

    }

}
