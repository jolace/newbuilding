<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Datatables;
use View;
use Validator;
use Input;
use Session;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public $data = array('master_title'=>'Customers');
    public function index()
    {
        $this->data['preForm'] = 0;
        
        if(Session::has('preForm'))
            $this->data['preForm'] = 1;

        $this->data['customer_count'] = Customer::all()->count();
        return View::make('customer.showCustomer',$this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['preForm'] = Input::get('preForm');    
        //Session::flush('bunner_fail',"You will be redirect to pre estimation form when you create customer");    
        return View::make('customer.createCustomer',$this->data);
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
            'firstName' => 'required',
            'lastName' => 'Required', 
            'address' => 'required',
            'company_name' => 'Required',
            'city' => 'required',
            'state' => 'Required',
            'zip' => 'required',
            'mobile_phone' => 'Required',
            'home_phone' => 'required',
            'business_phone' => 'Required',
            'fax' => 'required',
            'email' => 'Required',
            'referred_by' => 'required',
            'customer_type' => 'Required',    
        );
         

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes())                
        {   
            
            $customer = new Customer();
            $customer->first_name = Input::get("firstName");
            $customer->last_name = Input::get("lastName");
            $customer->address = Input::get("address");
            $customer->company_name = Input::get("company_name");
            $customer->city = Input::get("city");
            $customer->state = Input::get("state");
            $customer->zip = Input::get("zip");
            $customer->other_phone = Input::get("mobile_phone");
            $customer->primary_phone = Input::get("home_phone");
            //$customer->business_phone = Input::get("business_phone");
            $customer->fax = Input::get("fax");
            $customer->email = Input::get("email");
            $customer->referred_by = Input::get("referred_by");
            $customer->customer_type = Input::get("customer_type");
            $customer->save();
            $pre_form = Input::get('preForm');
            if(!empty($pre_form))
                return redirect('preestimation/create?customer_id='.$customer->id)->with('banner_success', 'Customer created successfuly. Please contiune with pre-estimation form');

            return redirect('customers/show')->with('banner_success', 'Customer created successfuly');
        }else
            return redirect('customers/createForm')->with('banner_fail', 'Please fill all data');        

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show()
    {
        $customer = Customer::select('id','first_name','last_name','address','company_name','city','state','zip')->get();
        
        return Datatables::of($customer)
        ->add_column('action','<a title="Edit customer" href="{{{ URL::to(\'customer/edit?customer_id=\' . $id  ) }}}">
            <span class="fa fa-edit"></span></a>

            <a title="Create pre-estimation form with this customer" href="{{{ URL::to(\'preestimation/create?customer_id=\' . $id  ) }}}">
            <span class="fa fa-plus-square-o"></span></a>
            
            <a title="Remove customer" href="{{{ URL::to(\'customer/delete?customer_id=\' . $id  ) }}}">
            <span class="fa fa-remove"></span>
            </a>')
        ->remove_column('id')
        ->make();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit()
    {
        $customer_id = Input::get('customer_id');
        $this->data['customer'] = Customer::where('id',$customer_id)->first();
        return View::make('customer.editCustomer',$this->data);
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
        $id = Input::get('customer_id');
        $rules = array(     
            'firstName' => 'required',
            'lastName' => 'Required', 
            'address' => 'required',
            'company_name' => 'Required',
            'city' => 'required',
            'state' => 'Required',
            'zip' => 'required',
            'primary_phone' => 'Required',
            'other_phone' => 'required',            
            'fax' => 'required',
            'email' => 'Required',
            'referred_by' => 'required',
            'customer_type' => 'Required', 
            'customer_id' => 'Required'   
        );
         

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes())                
        {   

            $customer = Customer::where('id',$id)->first();
            $customer->first_name = Input::get("firstName");
            $customer->last_name = Input::get("lastName");
            $customer->address = Input::get("address");
            $customer->company_name = Input::get("company_name");
            $customer->city = Input::get("city");
            $customer->state = Input::get("state");
            $customer->zip = Input::get("zip");
            $customer->primary_phone = Input::get("primary_phone");
            $customer->other_phone = Input::get("other_phone");
            $customer->fax = Input::get("fax");
            $customer->email = Input::get("email");
            $customer->referred_by = Input::get("referred_by");
            $customer->customer_type = Input::get("customer_type");
            $customer->save();
            return redirect('customers/show')->with('banner_success', 'Customer edited successfuly');
        }else
            return redirect('customers/edit?customer_id='.$id)->with('banner_fail', 'Please fill all data');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy()
    {
        return View::make('customer.deleteCustomer',$this->data);
    }

    public function findCustomerbyid(){
        $cid = Input::get('customer_id');
        if($cid){
            $customer = Customer::where('id',$cid)->first();
            if($customer){
                return $customer;
            }else
                return 0;
        }
    }
}
