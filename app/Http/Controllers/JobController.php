<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\PreEstimation;
use App\Models\Estimation;
use App\Models\PatchingJobs;
use App\Models\PlumbingJobs;
use App\Models\Job;
use Datatables;
use DB;
use View;
use Input;
use Auth;

class JobController extends Controller
{
    public $data = array('master_title'=>'Jobs');

    public function showAllJobs(){

        $this->data['count'] = Job::all()->count();
        return View::make('job_tables.allJobs',$this->data);

    }

    public function showActiveJobs(){

        $this->data['count'] = Job::where('status','active')->count();
        return View::make('job_tables.activeJobs',$this->data);

    }


    public function getJobDetails(){

        $return = array();
        $job_id = Input::get('job_id');
        $first_job = Job::where('idjob',$job_id)->leftJoin('users','users.id','=','job.created_by')->select('source','job.date','first_name','last_name')->first();
        $pre_job = PreEstimation::where('job_id',$job_id)->leftJoin('users','users.id','=','preestimation.estimator_id')->select('date','first_name','last_name')->first();
        $est_job = Estimation::where('job_id',$job_id)->first();
        $patching = PatchingJobs::where('job_id',$job_id)->select(DB::raw('DATE_FORMAT(date_start,"%Y-%m-%d")  as date_start'))->first();
        $plumbing = PlumbingJobs::where('job_id',$job_id)->select(DB::raw('DATE_FORMAT(date_start,"%Y-%m-%d")  as date_start'))->first();
        $return = array('job'=>$first_job,'pre'=>$pre_job,'est'=>$est_job,'patching'=>$patching,"plumbing"=>$plumbing);
        return $return;

    }



    public function activeJobs(){

        $jobs = Job::where('status','active')
                ->join('customer','job.customer_id','=','customer.id') 
                ->leftJoin('patching_jobs','job.idjob','=','patching_jobs.job_id')
                ->leftJoin('plumbing_jobs','job.idjob','=','plumbing_jobs.job_id')
                ->select('idjob','customer_id','first_name','last_name','job.address','job.city','job.state','job.status',
                    DB::raw('DATE_FORMAT(patching_jobs.date_start,"%Y-%m-%d")  as pdate'),
                    DB::raw('DATE_FORMAT(plumbing_jobs.date_start,"%Y-%m-%d")  as ldate'))            
                ->get();

        return Datatables::of($jobs)
        
        //->add_column('contract','Contract date')  
        ->add_column('action','<a href="#">Add</a>&nbsp;<a href="#">Show</a>')  
        ->make();
    }


    public function allJobs()
    {   
        $job_status = Input::get('job_status');
        $jobs = Job::join('customer','job.customer_id','=','customer.id')
                ->leftJoin('preestimation','preestimation.job_id','=','job.idjob')
                ->leftJoin('estimation','estimation.job_id','=','job.idjob');
        
        if($job_status != '')
            $jobs->where('job.status',$job_status);
        
        $jobs->select('idjob','customer_id','first_name','last_name','job.address','job.city','job.state','job.status')
              ->get();
        

        return Datatables::of($jobs)        
        ->add_column('action','<a target="_blank" title="See pre-estimation form" href="{{{ URL::to(\'preestimation/show?job_id=\' . $idjob  ) }}}">
            <span class="fa   fa-edit"></span>
            </a>
            @if($status=="estimate" || $status=="active")
            <a target="_blank" title="Show estimation form" href="{{{ URL::to(\'estimation/show?job_id=\' . $idjob  ) }}}">
            <span class="fa fa-newspaper-o"></span>
            </a>
            <a target="_blank" title="Contract" href="{{{ URL::to(\'contract/show?job_id=\' . $idjob  ) }}}">
            <span class="fa fa-navicon"></span>
            </a>
            @endif
            <a href="#" title="Details" onclick="showDetails({{$idjob}})">
            <span class="fa fa-book"></span>
            </a>
            ')  
        ->edit_column('status','<span style="color:blue">{{$status}}</span>')
        ->make();
    }  
}
