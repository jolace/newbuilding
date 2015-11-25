<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use View;
use Input;
use Datatables;
use App\Models\PatchingCrew;
use App\Models\PlumbingCrew;

 
class StaffController extends Controller
{
    
    public $data = array('master_title'=>"Staff Menangment");

    public function createPlumbingCrewForm(){

        return View::make('staff.createPlumbing',$this->data);

    }

    public function editPlumbingCrewForm(){

        return View::make('staff.editPlumbing',$this->data);

    }

    public function storePlumbingCrewForm(){

        $name = Input::get('plumb_name');
        $pc = PlumbingCrew::where('plumb_name',$name)->first();
        if(empty($pc)){

            $pc = new PlumbingCrew();
            $pc->plumb_name = $name;
            $pc->plumb_firstName = Input::get('plumb_firstName');
            $pc->plumb_lastName = Input::get('plumb_lastName');
            $pc->plumb_phone = Input::get('plumb_phone');
            $pc->plumb_other = Input::get('plumb_other');
            $pc->plumb_details = Input::get('plumb_details');
            $pc->save();
            
            return redirect('plumbing/show')->with('banner_success', 'Crew created successfuly');
        }else{
            return redirect('plumbing/createForm')->with('banner_fail', 'Please fill all data');
        
        }
        
    }

    public function showPlumbingCrewForm(){

        $this->data['p_count'] = PlumbingCrew::all()->count();
        return View::make('staff.showPlumbing',$this->data);

    }


    public function showTablePlumbing(){

        $pl = PlumbingCrew::all();
        
        return Datatables::of($pl)

        ->add_column('action','<a title="Edit customer" href="{{{ URL::to(\'plumbing/show?customer_id=\' . $idpcrew  ) }}}">
            <span class="fa fa-edit"></span></a>

            <a title="Edit customer" href="{{{ URL::to(\'plumbing/show?customer_id=\' . $idpcrew  ) }}}">
            <span class="fa fa-plus-square-o"></span></a>
            
            <a title="Remove customer" href="{{{ URL::to(\'plumbing/show?customer_id=\' . $idpcrew  ) }}}">
            <span class="fa fa-remove"></span>
            </a>') 
        ->remove_column('idpcrew')
        ->make();

    }


    public function createPatchingCrewForm(){

        return View::make('staff.createPatching',$this->data);

    }

    public function editPatchingCrewForm(){

        return View::make('staff.editPatching',$this->data);

    }

    public function storePatchingCrewForm(){

        $name = Input::get('patching_name');
        $pc = PatchingCrew::where('patching_name',$name)->first();
        if(empty($pc)){

            $pc = new PatchingCrew();
            $pc->patching_name = $name;
            $pc->patching_firstName = Input::get('patching_firstName');
            $pc->patching_lastName = Input::get('patching_lastName');
            $pc->patching_phone = Input::get('patching_phone');
            $pc->patching_other = Input::get('patching_other');
            $pc->patching_details = Input::get('patching_details');
            $pc->save();            
            return redirect('patching/show')->with('banner_success', 'Crew created successfuly');
        }else
            return redirect('patching/createForm')->with('banner_fail', 'Please fill all data');
    }

     public function showPatchingCrewForm(){

        $this->data['p_count'] = PatchingCrew::all()->count();;
        return View::make('staff.showPatching',$this->data);

    }

    public function showTablePatching(){

        $pl = PatchingCrew::all();
        
        return Datatables::of($pl)

        ->add_column('action','<a title="Edit customer" href="{{{ URL::to(\'patching/show?customer_id=\' . $idpacrew  ) }}}">
            <span class="fa fa-edit"></span></a>

            <a title="Edit customer" href="{{{ URL::to(\'patching/show?customer_id=\' . $idpacrew  ) }}}">
            <span class="fa fa-plus-square-o"></span></a>
            
            <a title="Remove customer" href="{{{ URL::to(\'patching/show?customer_id=\' . $idpacrew  ) }}}">
            <span class="fa fa-remove"></span>
            </a>') 
        ->remove_column('idpacrew')
        ->make();

    }

}
