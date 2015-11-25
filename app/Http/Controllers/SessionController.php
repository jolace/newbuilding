<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use View;
use Input;
use Validator;
use Auth;
class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public $data = array();

    public function index()
    {
        return View::make('session.login');
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store()
    {
        $email = Input::get('email'); 
        $device_id = Input::get('device_id');
        $device_type = Input::get('type'); 
     
        $field = 'email';
        $rules = array(     
        'email' => 'required|email',
        'password' => 'Required|min:5',     
        );
         

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes())                
        {   
            if (Auth::attempt(array('email' => Input::get('email'), 'password' => Input::get('password'))))            
            {
                return redirect('/');
            }else
                echo "nona";
        }
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
}
