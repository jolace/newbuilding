<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Permision;
use App\Models\UserPermission;
use App\Models\RolePermission;
use Bican\Roles\Models\Role;
use App\User;
use View;
use Input;
use Datatables;
use Response;

class PermisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public $data = array('master_title'=>'Permmisions menangment');
    public function index()
    {
        $this->data['permissions'] = Permision::all();
        return View::make('permisions.showPermisions',$this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $perm = Permision::all();
        $return_perm = array();
        $all = array();
        foreach ($perm as $p) {
            $temp = array();
            $temp['title'] = $p->name;
            $temp['value'] = $p->id;
            $return_perm[$p->model][] = $temp;
            $all[] = $p->id; 
            
        }
        //dd($return_perm);
        $this->data['permissions'] = $return_perm;
        $this->data['roles'] = Role::all();
        $this->data['all'] = json_encode($all);
        return View::make('permisions.assignPermisions',$this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store()
    {
        $role_id = Input::get('role_id');
        $perm = Input::get('permission');
        if(!empty($role_id)){
            if(empty($perm)) $perm = array();
            RolePermission::where('role_id',$role_id)->delete();
            foreach ($perm as $p) {            
                $obj = new RolePermission();
                $obj->role_id = $role_id; 
                $obj->permission_id = $p;
                $obj->save();
            }
            return redirect('permission/assign')->with('banner_success', 'Permissions assigned successfuly');
        }else
            return redirect('permission/assign')->with('banner_fail', 'Please choose role and assign permissions');
        
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function getUserPermission(){

        $id = Input::get('role_id');
        $return = array();
        $user_perm = RolePermission::where('role_id',$id)->get();
        foreach ($user_perm as  $val) {
           $return[]= $val->permission_id;
        }
        return Response::json(array('perm'=>$return));
    }

    public function showPermisionTable(){
         
        $permisions = Permision::select('name','slug','description','model')        
        ->orderBy('model')
        ->get();
        
        return Datatables::of($permisions)
        ->make();
    }
}
