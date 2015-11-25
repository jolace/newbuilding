<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\PatchingJobs;
use App\Models\PlumbingJobs;
use App\Models\ContractExtra;
use App\Models\ContractInspection;
use App\Models\ContractMaterials;
use App\Models\ContractPermit;
use App\Models\ContractPatching;
use App\Models\ContractFinal;
use App\Models\ContractFinalCorrection;
use View;
use Input;
use Datatables;

class ContractController extends Controller
{
    public $data = array('master_title'=>'Contract');
    
    public function index()
    {

        $job_id = Input::get('job_id');
        $contract = Contract::where('job_id',$job_id)->first();
        if($contract){        
            $this->data['data'] =  $this->getContractData($job_id);  
            $this->data['plumbing_crew'] = PlumbingJobs::where('job_id',$job_id)->join('plumbing_crew','plumbing_crew.idpcrew','=','plumbing_jobs.plumbing_id')->first();
            $this->data['job_id'] = Input::get('job_id');            
            //dd($this->data);
            return View::make('contract.editForm',$this->data);
        }
        else{
            $this->data['job_id'] = Input::get('job_id');
            $this->data['plumbing_crew'] = PlumbingJobs::where('job_id',$job_id)->join('plumbing_crew','plumbing_crew.idpcrew','=','plumbing_jobs.plumbing_id')->first();
            return View::make('contract.createForm',$this->data);
        }

    }

    public function showTable(){

        return View::make('contract.showTable',$this->data);
    }

    public function create()
    {        
        return View::make('contract.createForm',$this->data);
    }

    public function edit()
    {        
        $this->data['data'] =  $this->getContractData($job_id);
        return View::make('contract.ediForm',$this->data);
    }

    public function getAllContracts(){

        $contract = Contract::join('job','contract.job_id','=','job.idjob')
                            ->join('customer','customer.id','=','job.customer_id')
                            ->select('contract_id','job.idjob','first_name','last_name','job.address','job.city','job.state','amount_paid')
                            ->get();

        return Datatables::of($contract)
                ->add_column('action','lola')            
                ->make();
    }

    private function getContractData($job_id){
        
        $array = array();
        $array['contract'] = Contract::where('job_id',$job_id)->first();
        $array['contract_materials'] = ContractMaterials::where('job_id',$job_id)->get();
        $array['contract_extra'] = ContractExtra::where('job_id',$job_id)->get();
        $array['contract_permit'] = ContractPermit::where('job_id',$job_id)->get();        
        $array['contract_inspection'] = ContractInspection::where('job_id',$job_id)->get();
        $array['contract_patching'] = ContractPatching::where('job_id',$job_id)->first();
        $array['contract_final'] = ContractFinal::where('job_id',$job_id)->first();
        $array['contract_final_correction'] = ContractFinalCorrection::where('job_id',$job_id)->get();
        return $array;
    }


    public function update(){

        $job_id = Input::get('job_id');
//      CONTRACT table        
        $contract = Contract::where('job_id',$job_id)->first();
        $contract->contract_amount = Input::get('total_cont_amount');
        $contract->amount_paid = Input::get('balance');
        $contract->save();

//      CONTRACT MATERIALS        ///

        $cm = ContractMaterials::where('job_id',$job_id)->get();
        $i = 0;
        $vendor_array = Input::get('vendor');
        $invoice_num_array = Input::get('invoice_num');
        $amount_array = Input::get('amount');
                
        foreach($cm as $contract_materials){    
            
            $contract_materials->vendor = $vendor_array[$i];
            $contract_materials->invoice = $invoice_num_array[$i];
            $contract_materials->amount = $amount_array[$i];            
            $contract_materials->save();
            $i++;
        }

        if(empty($cm)){
            $i=0;
            foreach($vendor_array as $ven){ 
                $contract_materials = new ContractMaterials();
                $contract_materials->vendor = $ven;
                $contract_materials->invoice = $invoice_num_array[$i];
                $contract_materials->amount = $amount_array[$i];    
                $contract_materials->job_id = Input::get('job_id');        
                $contract_materials->save();
                $i++;
            }
        }

        // other contract materials
        $vendor_other = array();
        if(Input::has('vendor_other'))
            $vendor_other = Input::get('vendor_other');
        if(Input::has('invoice_num_other'))
            $invoice_other = Input::get('invoice_num_other');
        if(Input::has('amount_other'))
            $amount_other = Input::get('amount_other');
        $i=0;
         
        foreach ($vendor_other as $vendor) {
                
                $contract_materials = new ContractMaterials();
                $contract_materials->vendor = $vendor;
                $contract_materials->invoice = $invoice_other[$i];
                $contract_materials->amount = $amount_other[$i];
                $contract_materials->job_id = Input::get('job_id');
                $contract_materials->save();
                $i++;            
        }

//   END  CONTRACT MATERIALS        ///

//   EXTRA 
        $extra_check = Input::get('extra');
        if($extra_check == 'yes'){
            $extra_desc = Input::get('extra_desc');
            $extra_amount = Input::get('extra_amount');
            //dd(Input::all());
            $cm = ContractExtra::where('job_id',$job_id)->get();
            $i=0;
            foreach($cm as $contract_extra){               
                $contract_extra->description = $extra_desc[$i];
                $contract_extra->amount = $extra_amount[$i];
                $contract_extra->save();
                $i++;
            }  

            if(empty($cm)){
                $i=0;
                foreach($extra_amount as $am){ 
                    $contract_extra = new ContractExtra();
                    $contract_extra->description = $extra_desc[$i];
                    $contract_extra->amount = $am;                    
                    $contract_materials->job_id = Input::get('job_id');        
                    $contract_extra->save();    
                    $i++;
                }
            }          
            // other contract extra
            $description_other = array();
            if(Input::has('extra_desc_other'))
                $description_other = Input::get('extra_desc_other');
            $amount_other = Input::get('extra_amount_other');
            $i=0;             
            foreach ($description_other as $desc) {
                    
                    $contract_extra = new ContractExtra();
                    $contract_extra->description = $desc;
                    $contract_extra->amount = $amount_other[$i];
                    $contract_extra->job_id = Input::get('job_id');
                    $contract_extra->save();
                    $i++;
            }
        }else{
            // DELETE ALL EXTRA DATA FOR THIS CONTRACT
            ContractExtra::where('job_id',$job_id)->delete();
        }

// END EXTRA

//   PERMIT 
 
        $permit_check = Input::get('permit');
        if($permit_check == 'yes'){
            
            $cm = ContractPermit::where('job_id',$job_id)->get();
            $pulled_by = Input::get('pulled_from');
            $permit_date = Input::get('permit_pull');
            $premit_amount = Input::get('permit_amount');
            $payment_type = Input::get('paymant_via');
            $check_num = Input::get('check_num');
            $phone = Input::get('CC_phone_num');
            $request_line = Input::get('inspection_rl');
            $permit_number = Input::get('permit_num');
            $i=0;
            foreach($cm as $contract_permit){
                $contract_permit->pulled_by = $pulled_by[$i];
                $contract_permit->permit_date = $permit_date[$i];
                $contract_permit->premit_amount = $premit_amount[$i];
                $contract_permit->payment_type = $payment_type[$i];
                $contract_permit->check_num = $check_num[$i];
                $contract_permit->phone = $phone[$i];
                $contract_permit->request_line = $request_line[$i];
                $contract_permit->permit_number = $permit_number[$i];
                $contract_permit->save();
                $i++;
            }

            if(empty($cm)){
                $i=0;
                foreach($permit_number as $pm){ 
                    $contract_permit = new ContractPermit();
                    $contract_permit->pulled_by = $pulled_by[$i];
                    $contract_permit->permit_date = $permit_date[$i];
                    $contract_permit->premit_amount = $premit_amount[$i];
                    $contract_permit->payment_type = $payment_type[$i];
                    $contract_permit->check_num = $check_num[$i];
                    $contract_permit->phone = $phone[$i];
                    $contract_permit->request_line = $request_line[$i];
                    $contract_permit->permit_number = $pm;                                      
                    $contract_permit->job_id = Input::get('job_id');        
                    $contract_permit->save();    
                    $i++;
                }
            } 



            // multiple permit
            $pulled_from_other = Input::get('pulled_from_other');
            $permit_pull_other = Input::get('permit_pull_other');
            $permit_amount_other = Input::get('permit_amount_other');
            $paymant_via_other = Input::get('paymant_via_other');
            $check_num_other = Input::get('check_num_other');
            $CC_phone_num_other = Input::get('CC_phone_num_other');
            $inspection_rl_other = Input::get('inspection_rl_other');
            $permit_num_other = Input::get('permit_num_other');
            $i=0;
           
            foreach ($pulled_from_other as $po) {
                    
                $contract_permit = new ContractPermit();
                $contract_permit->pulled_by = $po;
                $contract_permit->permit_date = $permit_pull_other[$i];
                $contract_permit->premit_amount = $permit_amount_other[$i];
                $contract_permit->payment_type = $paymant_via_other[$i];
                $contract_permit->check_num = $check_num_other[$i];
                $contract_permit->phone = $CC_phone_num_other[$i];
                $contract_permit->request_line = $inspection_rl_other[$i];
                $contract_permit->permit_number = $permit_num_other[$i];
                $contract_permit->job_id = Input::get('job_id');
                $contract_permit->save();
                $i++;
            }
        }else
            ContractPermit::where('job_id',$job_id)->delete();

// END PERMIT
 
// contract patching
        $check_patching = Input::get('patching');
        if($check_patching=='yes'){
            $contract_patching = ContractPatching::where('job_id',$job_id)->first();
            if(!$contract_patching)
                $contract_patching = new ContractPatching();
            $contract_patching->notify_customer = Input::get('notify_dc');
            $contract_patching->done_by = Input::get('patching_done');
            $contract_patching->notify_patcher = Input::get('notify_cpi');
            $contract_patching->job_id = Input::get('job_id');
            $contract_patching->save();
        }

 // contract_inspection
        $ci_name = Input::get('inspection');
        $ci_date = Input::get('insp');
        $ci_notify = Input::get('notify_cus_op');
        $ci_status = Input::get('notify_cus',array());
        $ci_correction = Input::get('corect');
        $i = 0;
        $contract_inspections = ContractInspection::where('job_id')->get();
        foreach($contract_inspections as $contract_inspection){

            $contract_inspection->name = $ci_name[$i];
            $contract_inspection->inspection_date = $ci_date[$i];
            $contract_inspection->notify_customer = $ci_notify[$i];
            $contract_inspection->status = $ci_status[$i];
            $contract_inspection->correction = $ci_correction[$i];            
            $contract_inspection->job_id = Input::get('job_id');
            $contract_inspection->save();
            $i++;
        }

        if(empty($contract_inspections)){
                $i=0;
                foreach($ci_notify as $pm){ 
                    $contract_inspection = new ContractInspection();
                    $contract_inspection->name = $ci_name[$i];
                    $contract_inspection->inspection_date = $ci_date[$i];
                    $contract_inspection->notify_customer = $ci_notify[$i];
                    $contract_inspection->status = $ci_status[$i];
                    $contract_inspection->correction = $ci_correction[$i];            
                    $contract_inspection->job_id = Input::get('job_id');
                    $contract_inspection->save();
                    $i++;
                }
            } 

// contract final
        $contract_final = ContractFinal::where('job_id',$job_id)->first();
        if(!$contract_final)
                $contract_final = new ContractFinal();
        $contract_final->notify_customer = Input::get('notify_cus_date');
        $contract_final->status = Input::get('pass_nopass2');        
        $contract_final->correction_details  = Input::get('f_corect');
        if(Input::has('f_re_inspection'))
            $reinsert_status = Input::get('f_re_inspection');
        else
            $reinsert_status = array();

        $reinsert_details = Input::get('f_corect2');
        $reinsert_date = Input::get('reinsp_data');

        foreach ($reinsert_status as $rs) {
            
            $contract_final2 = new ContractFinalCorrection();
            $contract_final2->status = $rs;
            $contract_final2->correction_date = $reinsert_date[$i];
            $contract_final2->correction_details = $reinsert_details[$i];
            $contract_final2->job_id = Input::get('job_id');
            $contract_final2->save();
            $i++;
        }
        $contract_final->save(); 
    }

    public function store(){
        
        $job_id = Input::get('job_id');
        //dd(Input::all());
        //contract table
        $contract = new Contract();
        $contract->contract_amount = Input::get('total_cont_amount');
        $contract->amount_paid = Input::get('contract_amount');
        $contract->balance = Input::get('balance');
        $contract->final_inspection = Input::get('fin_inspection');
        $contract->job_id = Input::get('job_id');
        $contract->save();

        // contract materials table
        
        $contract_materials = new ContractMaterials();
        $contract_materials->vendor = Input::get('vendor');
        $contract_materials->invoice = Input::get('invoice_num');
        $contract_materials->amount = Input::get('amount');
        $contract_materials->job_id = Input::get('job_id');
        $contract_materials->save();

        // other contract materials
        if(Input::has('vendor_other'))
            $vendor_other = Input::get('vendor_other');
        else
            $vendor_other = array();

        $invoice_other = Input::get('invoice_num_other');
        $amount_other = Input::get('amount_other');
        $i=0;
         
        foreach ($vendor_other as $vendor) {
                            
                $contract_materials = new ContractMaterials();
                $contract_materials->vendor = $vendor;
                $contract_materials->invoice = $invoice_other[$i];
                $contract_materials->amount = $amount_other[$i];
                $contract_materials->job_id = Input::get('job_id');
                $contract_materials->save();
                $i++;
            
        }
        

        // contract extra
        $extra_check = Input::get('extra');
        if($extra_check == 'yes'){
            
            $contract_extra = new ContractExtra();
            $contract_extra->description = Input::get('extra_desc');
            $contract_extra->amount = Input::get('extra_amount');
            $contract_extra->job_id = Input::get('job_id');
            $contract_extra->save();

            // other contract extra
            if(Input::has('extra_desc_other'))
                $description_other = Input::get('extra_desc_other');
            else
                $description_other = array();
            $amount_other = Input::get('extra_amount_other');
            $i=0;
             
            foreach ($description_other as $desc) {
                if(!empty($desc) && !empty($amount_other[$i])){
                    
                    $contract_extra = new ContractExtra();
                    $contract_extra->description = $desc;
                    $contract_extra->amount = $amount_other[$i];
                    $contract_extra->job_id = Input::get('job_id');
                    $contract_extra->save();
                    $i++;
                }
            }
        }
 
        // permit
        $permit_check = Input::get('permit');
        if($permit_check == 'yes'){
            
            $contract_permit = new ContractPermit();
            $contract_permit->pulled_by = Input::get('pulled_from');
            $contract_permit->permit_date = Input::get('permit_pull');
            $contract_permit->premit_amount = Input::get('permit_amount');
            $contract_permit->payment_type = Input::get('paymant_via');
            $contract_permit->check_num = Input::get('check_num');
            $contract_permit->phone = Input::get('CC_phone_num');
            $contract_permit->request_line = Input::get('inspection_rl');
            $contract_permit->permit_number = Input::get('permit_num');
            $contract_permit->job_id = Input::get('job_id');
            $contract_permit->save();

            // multiple permit
            if(Input::has('pulled_from_other'))
                $pulled_from_other = Input::get('pulled_from_other');
            else
                $pulled_from_other = array();

            $permit_pull_other = Input::get('permit_pull_other');
            $permit_amount_other = Input::get('permit_amount_other');
            $paymant_via_other = Input::get('paymant_via_other');
            $check_num_other = Input::get('check_num_other');
            $CC_phone_num_other = Input::get('CC_phone_num_other');
            $inspection_rl_other = Input::get('inspection_rl_other');
            $permit_num_other = Input::get('permit_num_other');
            $i=0;
           
            foreach ($pulled_from_other as $po) {
                    
                $contract_permit = new ContractPermit();
                $contract_permit->pulled_by = $po;
                $contract_permit->permit_date = $permit_pull_other[$i];
                $contract_permit->premit_amount = $permit_amount_other[$i];
                $contract_permit->payment_type = $paymant_via_other[$i];
                $contract_permit->check_num = $check_num_other[$i];
                $contract_permit->phone = $CC_phone_num_other[$i];
                $contract_permit->request_line = $inspection_rl_other[$i];
                $contract_permit->permit_number = $permit_num_other[$i];
                $contract_permit->job_id = Input::get('job_id');
                $contract_permit->save();
                $i++;
            }
        }

        
        // contract_inspection
        $ci_name = Input::get('inspection');
        $ci_date = Input::get('insp');
        $ci_notify = Input::get('notify_cus_op');
        $ci_status = Input::get('notify_cus',array());
        $ci_correction = Input::get('corect');
        $i = 0;

        foreach($ci_status as $st){

            $contract_inspection = new ContractInspection();
            $contract_inspection->name = $ci_name[$i];
            $contract_inspection->inspection_date = $ci_date[$i];
            $contract_inspection->notify_customer = $ci_notify[$i];
            $contract_inspection->status = $st;
            $contract_inspection->correction = $ci_correction[$i];            
            $contract_inspection->job_id = Input::get('job_id');
            $contract_inspection->save();
            $i++;
        }

        // contract patching
        $check_patching = Input::get('patching');
        if($check_patching=='yes'){
            $contract_patching = ContractPatching::where('job_id',$job_id)->first();
            if(!$contract_patching)
                $contract_patching = new ContractPatching();
            $contract_patching->notify_customer = Input::get('notify_dc');
            $contract_patching->done_by = Input::get('patching_done');
            $contract_patching->notify_patcher = Input::get('notify_cpi');
            $contract_patching->job_id = Input::get('job_id');
            $contract_patching->save();
        }

        // contract final
        
        $contract_final = new ContractFinal();
        $contract_final->notify_customer = Input::get('notify_cus_date');
        $contract_final->status = Input::get('pass_nopass2');
        $contract_final->correction_details  = Input::get('f_corect');

        $reinsert_status = Input::get('f_re_inspection');
        $reinsert_details = Input::get('f_corect2');
        $reinsert_date = Input::get('reinsp_data');
        $i=0;
        foreach ($reinsert_status as $rs) {
            
            $contract_final2 = new ContractFinalCorrection();
            $contract_final2->status = $rs;
            $contract_final2->correction_date = $reinsert_date[$i];
            $contract_final2->correction_details = $reinsert_details[$i];
            $contract_final2->job_id = Input::get('job_id');
            $contract_final2->save();
            $i++;
        }


        $contract_final->job_id = Input::get('job_id');
        $contract_final->save();       

    }
    
}
