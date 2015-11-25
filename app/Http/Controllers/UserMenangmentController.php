<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use View;
use Datatables;
use Bican\Roles\Models\Role;
use App\Models\RoleUser;
use Validator;
use Input;
use Hash;
use URL;

class UserMenangmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public $data = array('master_title'=>'User menangment');

    public function index()
    {
        return View::make("users.showUsers",$this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['roles'] = Role::all();
        return View::make("users.createUsers",$this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store()
    {
        $rules = array(            
            'email' => 'required|email|unique:users',
            'password' => 'Required|min:5',
            'firstName' => 'Required',
            'lastName' => 'Required'
        );
     
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes())
        {
            
            $user = new User();
            $user->email = Input::get('email');
            $user->first_name = Input::get('firstName');
            $user->last_name = Input::get('lastName');
            $user->password = Hash::make(Input::get('password'));
            $user->image = 'system/img/default_profile.jpg';
            $user->save();

            $user->attachRole(Input::get('role'));

            return redirect('users/showUsers')->with('banner_success', 'User created successfuly');
        }else
            return redirect('users/createForm')->with('banner_fail', 'Please fill all data');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show()
    {
        $user_id = Input::get('user_id');
        if(!empty($user_id)){
            $this->data['user'] = User::where('users.id',$user_id)->join('role_user','role_user.user_id','=','users.id')
                                    ->join('roles','role_user.role_id','=','roles.id')
                                    ->select('users.id','first_name','last_name','email','password','name')
                                    ->first();

            $this->data['roles'] = Role::all();
            return View::make('users.editUser',$this->data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit()
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
    public function update()
    {
        $user_id = Input::get('user_id');
        $user = User::where('id',$user_id)->first();
        if($user){

            $user->email = Input::get('email');
            $user->first_name = Input::get('firstName');
            $user->last_name = Input::get('lastName');

            if(Input::has('password'))
                $user->password = Hash::make(Input::get('password'));

            $user->save();
            $user->detachAllRoles();
            $user->attachRole(Input::get('role'));
            return redirect('users/showUsers')->with('banner_success', 'User updated successfuly');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy()
    {
        $user_id = Input::get('user_id');
        $user = User::where('id',$user_id)->first();
        if($user){
                
            if($user->is('admin')){
                //count admin and if not last admin remove user
                $count_admin = RoleUser::where('role_id',1)->count();
                if($count_admin > 1){
                    $user->detachAllRoles();
                    $user->delete();
                }else
                    return redirect('users/showUsers')->with('banner_fail', 'User is last admin. You have not permission to remove it');
            }else{
                    //if other user just remove it
                    $user->detachAllRoles();
                    $user->delete();
            }
        }else
            return redirect('users/showUsers');            
    }

    public function showUsersTable(){
        $users = User::select('users.id','first_name','last_name','email','roles.name')
        ->join('role_user','role_user.user_id','=','users.id')
        ->join('roles','role_user.role_id','=','roles.id')
        ->get();
        
        return Datatables::of($users)
        ->add_column('action','<a title="Edit user" href="{{{ URL::to(\'users/edit?user_id=\' . $id  ) }}}">
            <span class="fa fa-edit"></span></a>
            <a title="Remove user" href="{{{ URL::to(\'users/delete?user_id=\' . $id  ) }}}">
            <span class="fa fa-remove"></span>
            </a>')
        ->remove_column('id')
        ->make();
    }
}
