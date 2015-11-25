<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Input;
use App\Models\Job;
use App\Models\PreEstimation;
use App\Models\Estimation;
use App\Models\PlumbingJobs;
use App\Models\PatchingJobs;
use App\Models\Customer;
use App\Models\RoleUser;
use View;
use URL;
use Auth;
use DB;
use DateTime;

class CalendarController extends Controller
{
    public $data = array('master_title'=>'Jobs calendar');

    public function getEventData(){
      
        $job_id = Input::get('event_id');
        $job_id = explode('_',$job_id);
        $job_id = $job_id[1];

        $job = Job::find($job_id);
        $customer = Customer::where('id',$job->customer_id)->first();
        $return = array('redirect_url_pdf'=>URL::to('job/PreestimationPDF?job_id='.$job->idjob),'redirect_url'=>URL::to('estimation/show?job_id='.$job->idjob),'job_id'=> $job->idjob, 'customer_data' => $customer, 'job_data'=>$job ); 
        return $return;
    }

    public function getAllJobPlumbingEvents(){

         $return = array();
         $crew_job = PlumbingJobs::all();
         foreach($crew_job as $job){
             
              $return[] = array(
                  "id" => 'event_'.$job->job_id,
                  "title"=> 'Job#'.$job->job_id,
                  "start"=> date('c',strtotime($job->date_start)),
                  "end"=> date('c',strtotime($job->date_end)+3600),
                  //"allDay"=> FALSE,
                  'overlap'=>FALSE,
                  "backgroundColor"=> "#00c0ef", //Info (aqua)
                  "borderColor"=>"#00c0ef" //Info (aqua)
              );
        }
        return $return;

    }


     public function getAllJobPatchingEvents(){

         $return = array();
         $crew_job = PatchingJobs::all();
         foreach($crew_job as $job){
             
              $return[] = array(
                  "id" => 'event_'.$job->job_id,
                  "title"=> 'Job#'.$job->job_id,
                  "start"=> date('c',strtotime($job->date_start)),
                  "end"=> date('c',strtotime($job->date_end)+3600),
                  //"allDay"=> FALSE,
                  'overlap'=>FALSE,
                  "backgroundColor"=> "#00c0ef", //Info (aqua)
                  "borderColor"=>"#00c0ef" //Info (aqua)
              );
        }
        return $return;

    }



    public function getAllPreEstimationEvents(){
        $return = array();
        $date_start = Input::get('start');
        $date_end = Input::get('end');
        $estimator = Input::get('estimator');        
        $jobs = Job::where('status','preestimate')
            ->where('estimator_id',$estimator)
            ->where('preestimation.date','>=',$date_start)
            ->where('preestimation.date','<=',$date_end)
            ->join('preestimation','preestimation.job_id','=','job.idjob')
            ->join('customer','customer.id','=','job.customer_id')
            ->select('first_name','last_name','preestimation.date','job_id','job.city')
            ->get();

        foreach($jobs as $job){
             
              $return[] = array(
                  "id" => 'event_'.$job->job_id,
                  "title"=> 'Job#'.$job->job_id." - $job->city \n $job->first_name $job->last_name",
                  "start"=> date('c',strtotime($job->date)),
                  "end"=> date('c',strtotime($job->date)+3600),
                  "allDay"=> FALSE,
                  'overlap'=>FALSE,
                  "backgroundColor"=> "#00c0ef", //Info (aqua)
                  "borderColor"=>"#00c0ef" //Info (aqua)
              );
        }
        return $return;
    }

    public function showJustCalendarEstimator(){

        $master_data = $this->data;
        $master_data['master_title'] = "Estimator calendar";
        $master_data['estimators'] = RoleUser::where('role_id',2)->join('users','role_user.user_id','=','users.id')->get();
        return View::make('estimation.calendar',$master_data);

    }


    public function showjobInPreForm(){

        $master_data = $this->data;
        $master_data['master_title'] = "Add jobs in calendar";
        $master_data['jobs'] = Job::where('status','preestimate')->where('assign_estimator',0)->get();
        $master_data['estimators'] = RoleUser::where('role_id',2)->join('users','role_user.user_id','=','users.id')->get();
        return View::make('pre_estimation.addJob_calendar',$master_data);
    }

    public function jobPatchingCalendar(){

        $return = array(
        'error_num' => 0,
        'error_text'=> ''
        );

        $job_id = Input::get('event_id');
        $job_id = explode('_',$job_id);
        $job_id = $job_id[1];
        $find_job = Job::where('idjob',$job_id)->first();
        $estimate_days = Estimation::where('job_id',$job_id)->select('patching_days')->first();
        $estimate_days = $estimate_days->patching_days;
        // Clean date
        $input_date = Input::get('date_start');
        $input_date_end = Input::get('date_end');
        $format = 'Y-m-d H:i:s';
        $date_start = date_format(date_create($input_date), $format);
        $date_end = date_format(date_create($input_date_end), $format);
        
        $dateTime = new DateTime($date_start);
        $dateTime->modify('+1 minutes');
        $date_start = $dateTime->format('Y-m-d H:i:s');
       /*
        $dateTime = new DateTime($date_end);
        $dateTime->modify('-1 minutes');
        $date_end = $dateTime->format('Y-m-d H:i:s');
        */

        $crew = Input::get('crew');
        $check =  DB::select( DB::raw("SELECT count(*) as nona FROM patching_jobs WHERE date_start BETWEEN '$date_start' and '$date_end' ") );
        
        if($check[0]->nona>0){
            $return['error_num'] = 1;
            $return['error_text'] = 'Job overlap with another';
            return $return; 
        }

        $check =  DB::select( DB::raw("SELECT count(*) as nona FROM patching_jobs WHERE date_end BETWEEN '$date_start' and '$date_end' ") );
        if($check[0]->nona>0){
            $return['error_num'] = 1;
            $return['error_text'] = 'Job overlap with another';   
            return $return; 
        }

        if($find_job){

            $crew_job = PatchingJobs::where('job_id',$job_id)->first();
            
            if(!$crew_job){
                $crew_job = new PatchingJobs();
                $crew_job->job_id = $job_id;
                $crew_job->patching_id = $crew;
                $crew_job->date_start = $date_start;
                $crew_job->date_end = $date_end;
                $crew_job->estimator_id = $crew;
                $crew_job->save();
                
            }else{
                $crew_job->date_start = $date_start;
                $crew_job->date_end = $date_end;
                $crew_job->estimator_id = $crew;
                $crew_job->save();
            }
            
            

        }else
        {
            $return['error_num'] = 1;
            $return['error_text'] = 'Job not found in system';            
        }
        return $return;
    }

    public function jobPlumbingCalendar(){

        $return = array(
        'error_num' => 0,
        'error_text'=> ''
        );
        // Clean date
        $input_date = Input::get('date_start');
        $input_date_end = Input::get('date_end');
        $format = 'Y-m-d';
        $date_start = date_format(date_create($input_date), $format);
        $date_end = date_format(date_create($input_date_end), $format);

        $dateTime = new DateTime($date_start);
        $dateTime->modify('+1 minutes');
        $date_start = $dateTime->format('Y-m-d H:i:s');
        //$date_end = Input::get('date_end');
        // get job id
        $job_id = Input::get('event_id');
        $job_id = explode('_',$job_id);
        $job_id = $job_id[1];
        $find_job = Job::where('idjob',$job_id)->first();
        $crew = Input::get('crew');
        $check = PlumbingJobs::where('date_start','>=',$date_start)->where('date_end','<=',$date_end)->count();
        if($check>0){
            $return['error_num'] = 1;
            $return['error_text'] = 'Job overlap with another';   
            return $return; 
        }

        $check =  DB::select( DB::raw("SELECT count(*) as nona FROM plumbing_jobs WHERE date_start BETWEEN '$date_start' and '$date_end' ") );
        
        if($check[0]->nona>0){
            $return['error_num'] = 1;
            $return['error_text'] = 'Job overlap with another';
            return $return; 
        }

        $check =  DB::select( DB::raw("SELECT count(*) as nona FROM plumbing_jobs WHERE date_end BETWEEN '$date_start' and '$date_end' ") );
        if($check[0]->nona>0){
            $return['error_num'] = 1;
            $return['error_text'] = 'Job overlap with another';   
            return $return; 
        }




        if($find_job){

            $crew_job = PlumbingJobs::where('job_id',$job_id)->first();
            
            if(!$crew_job){
                $crew_job = new PlumbingJobs();
                $crew_job->job_id = $job_id;
                $crew_job->plumbing_id = $crew;
                $crew_job->date_start = $date_start;
                $crew_job->date_end = $date_end;
                $crew_job->estimator_id = $crew;
                $crew_job->save();
                
            }else{
                $crew_job->date_start = $date_start;
                $crew_job->date_end = $date_end;
                $crew_job->estimator_id = $crew;
                $crew_job->save();
            }

            $find_job->status = 'active';
            $find_job->save();
            
        }else
        {
            $return['error_num'] = 1;
            $return['error_text'] = 'Job not found in system';            
        }
        return $return;
    }


    public function jobInPreForm(){
        $return = array(
        'error_num' => 0,
        'error_text'=> ''
        );
        // Clean date
        $input_date = Input::get('date');
        $format = 'Y-m-d H:i:s';
        $date = date_format(date_create($input_date), $format);
        // get job id
        $job_id = Input::get('event_id');
        $job_id = explode('_',$job_id);
        $job_id = $job_id[1];
        $find_job = Job::where('idjob',$job_id)->first();
        $estimator = Input::get('estimator');
        $dateTime = new DateTime($date);
        $dateTime->modify('+59 minutes');
        $date_end = $dateTime->format('Y-m-d H:i:s');


        $check = DB::select( DB::raw("SELECT count(*) as nona FROM preestimation WHERE assign_estimator = '0' and estimator_id = $estimator and preestimation.date BETWEEN '$date' and '$date_end' ") );
        
        if($check[0]->nona>0){
            $return['error_num'] = 1;
            $return['error_text'] = 'Job overlap with another';   
            return $return; 
        }
        if($find_job){

            $preestimation = PreEstimation::where('job_id',$job_id)->first();
            
            if(!$preestimation){
                $preestimation = new PreEstimation();
                $preestimation->job_id = $job_id;
                $preestimation->date = $date;
                $preestimation->date_end = $date_end;
                $preestimation->estimator_id = $estimator;                
                $preestimation->save();
                $find_job->assign_estimator = 1;
                $find_job->save();
            }else{
                $preestimation->estimator_id = $estimator;
                $preestimation->date = $date;
                $preestimation->date_end = $date_end;
                $preestimation->save();
            }

            $job = Job::where('idjob',$job_id)
            ->join('customer','customer.id','=','job.customer_id')
            ->select('first_name','last_name','idjob','job.city')
            ->first();

            $return['title'] = "Job#$job->idjob - $job->city \n $job->first_name $job->last_name";
            $return['end'] = date('c',strtotime($preestimation->date)+3600);
        }else
        {
            $return['error_num'] = 1;
            $return['error_text'] = 'Job not found in system';            
        }
        return $return;
    }
}
