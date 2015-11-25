<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Bican\Roles\Models\Role;
use View;
use Input;
use Datatables;
class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public $data = array('master_title'=>"Roles Menangment");

    public function index()
    {
         $data = $this->data;
         $data['roles'] = Role::all();
         return View::make('roles.showRoles',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {        
        return View::make('roles.createRoles',$this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store()
    {        
        $new = new Role();
        $new->name = Input::get('name');
        $new->slug = Input::get('slug');
        $new->description = Input::get('description');
        $new->save();
        return redirect('users/showRoles')->with('banner_success', 'Role  '.$new->name.' created successfuly');
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
    public function edit()
    {
        $role_id = Input::get('role_id');
        if(!empty($role_id)){
            $this->data['role'] = Role::where('id',$role_id)->first();
            return View::make('roles.editRoles',$this->data);
        }else
            return redirect('users/showRoles')->with('banner_fail', 'Role not found');
    
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update()
    {
        $role_id = Input::get('role_id');
        $role = Role::where('id',$role_id)->first();
        if($role){
            if(Input::has('name'))
                $role->name = Input::get('name');
            if(Input::has('slug'))
                $role->slug = Input::get('slug');
            if(Input::has('description'))
                $role->description = Input::get('description');

            $role->save();
            return redirect('users/showRoles')->with('banner_success', 'Role  updated successfuly');
        }else
            return redirect('users/showRoles')->with('banner_fail', 'Role not found');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy()
    {
        $role_id = Input::get('role_id');
        $role = Role::where('id',$role_id)->first();
        if($role){
            $role->delete();
            return redirect('users/showRoles')->with('banner_success', 'Role  updated successfuly');
        }else
            return redirect('users/showRoles')->with('banner_fail', 'Role not found');
    }

    public function showRolesTable(){
        $roles = Role::select('id','name','slug','description')        
        ->get();
        
        return Datatables::of($roles)
        ->add_column('action','<a title="Edit role" href="{{{ URL::to(\'users/editRole?role_id=\' . $id  ) }}}">
            <span class="fa fa-edit"></span></a>
            <a title="Remove role" href="{{{ URL::to(\'users/removeRole?role_id=\' . $id  ) }}}">
            <span class="fa fa-remove"></span>
            </a>')
        ->remove_column('id')
        ->make();
    }
}
