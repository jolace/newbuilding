<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use View;
use Input;
use Auth;
use Session;
use Datatables;
use DB;
use App\Models\Estimation;
use App\Models\Job;
use App\Models\PlumbingCrew;
use App\Models\PatchingCrew;

class EstimationController extends Controller
{
    
    public $data = array('master_title'=>'Estimation form');
    public function index()
    {
        $this->data['job_id'] = Input::get('job_id');
        return View::make('estimation.show',$this->data);
    }

   
    public function store()
    {
        
        $days = Input::get('jobs_day');
        $job_id = Input::get('jobs_id');
        if(!empty($days) && !empty($job_id)){

            // zapisi gi days vo estimation tabela i other data
            $est = Estimation::where('job_id',$job_id)->first();
            if(empty($est)){
                $est = new Estimation();
                $est->date = date('Y-m-d h:i:s');
                $est->job_id = $job_id;
                $est->estimator_id = Auth::user()->id;
            }
            $est->numbers_days = $days;
            $est->save();
            //smeni vo jobs od pre vo estimation
            $job = Job::where('idjob',$job_id)->first();
            $job->status = 'pending';
            $job->save();
            //redirectiraj do kalendar za da odberi
            return redirect('jobs/plumbing')->with('redirect_jobid',$job->idjob);; 


        }
    }

    public function plumbing(){
        
        $this->data['master_title'] = "Add job - Plumbing crew";
        if(Session::has('redirect_jobid')){
            $this->data['job_id'] = Session::get('redirect_jobid');
            $this->data['job_id_days'] = Estimation::where('job_id',$this->data['job_id'])->first();
        }
        $this->data['plumbing'] = PlumbingCrew::all();
        $jobs_ids_array = Job::where('status','estimate')->join('plumbing_jobs','job.idjob','=','plumbing_jobs.job_id')
                                                        ->select('job_id')->get();

        $jobs = Job::where('status','estimate')->leftJoin('estimation','job.idjob','=','estimation.job_id')
                                              ->leftJoin('plumbing_jobs','job.idjob','=','plumbing_jobs.job_id')
                                              ->whereNotIn('estimation.job_id',$jobs_ids_array);
        if(Session::has('redirect_jobid'))
            $jobs->where('idjob','!=',Session::get('redirect_jobid'));
        $this->data['jobs'] =  $jobs->get();
        return View::make('estimation.plumbing_calendar',$this->data);
    }

    public function patching(){
        
        $this->data['master_title'] = "Add job - Patching crew";
        if(Session::has('redirect_jobid')){
            $this->data['job_id'] = Session::get('redirect_jobid');
            $this->data['job_id_days'] = Estimation::where('job_id',$this->data['job_id'])->first();
        }
        $this->data['patching'] = PatchingCrew::all();
        $jobs_ids_array = Job::where('status','estimate')->join('patching_jobs','job.idjob','=','patching_jobs.job_id')
                                                        ->select('job_id')->get();
                                                        
        $jobs = Job::where('status','estimate')->leftJoin('estimation','job.idjob','=','estimation.job_id')
                                              ->leftJoin('patching_jobs','job.idjob','=','patching_jobs.job_id')
                                              ->whereNotIn('estimation.job_id',$jobs_ids_array);
        if(Session::has('redirect_jobid'))
            $jobs->where('idjob','!=',Session::get('redirect_jobid'));
        $this->data['jobs'] =  $jobs->get();
        return View::make('estimation.patching_calendar',$this->data);
    }

    public function showJobsTable(){
        $this->data['count'] = Estimation::count();
        return View::make('estimation.showTable',$this->data);
    }
    
    public function showEstimationJobs(){
        $pre =  Job::join('estimation','job.idjob','=','estimation.job_id')
                ->join('customer','job.customer_id','=','customer.id')
                ->join('users','users.id','=','estimation.estimator_id')
                ->where('job.status','estimate')
                ->select('idjob','customer_id','status','estimation.date',
                        DB::raw('CONCAT(users.first_name, " ", users.last_name) AS full_name'),
                        DB::raw('(select count(*) from patching_jobs where job_id=idjob) as patching_num'),
                        DB::raw('(select count(*) from plumbing_jobs where job_id=idjob) as plumbing_num'),
                        DB::raw('(select count(*) from contract where job_id=idjob) as contract_num')
                        )
                ->get();
      
        return Datatables::of($pre)

        ->add_column('action','<a target="_blank" title="Add patching crew" href="{{{ URL::to(\'jobs/patching?job_id=\' . $idjob  ) }}}">
            <span class="fa   fa-user-secret"></span>
            </a>
            <a target="_blank" title="Add plumbing crew" href="{{{ URL::to(\'jobs/plumbing?job_id=\' . $idjob  ) }}}">
            <span class="fa  fa-truck"></span>
            </a>
            <a target="_blank" title="Create contract" href="{{{ URL::to(\'contract/create?job_id=\' . $idjob  ) }}}">
            <span class="fa fa-navicon"></span>
            </a>
            ') 
        ->edit_column('patching_num','@if($patching_num==1)<span style="color:blue"> Yes </span>@else<span style="color:red"> No </span>@endif')
        ->edit_column('plumbing_num','@if($plumbing_num==1)<span style="color:blue"> Yes </span>@else<span style="color:red"> No </span>@endif')
        ->edit_column('contract_num','@if($contract_num==1)<span style="color:blue"> Yes </span>@else<span style="color:red"> No </span>@endif')
        //->add_column('new_status','@if($date!="") <span style="color:blue;">Waiting to be active</span> @else {{$status}} @endif')
        ->remove_column('idpreestimation')
        ->make();
    }

}
