<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/deleteDB', function () {
    
    DB::table('bar')->delete();
    DB::table('bathroom_shower')->delete();
    DB::table('bathroom_type')->delete();
    DB::table('building_description')->delete();
    DB::table('duties')->delete();
    DB::table('estimation')->delete();
    DB::table('hose_bibbs')->delete();
    DB::table('insulation_pipes')->delete();
    DB::table('laundry')->delete();
    DB::table('main_service')->delete();
    DB::table('other_kitchen')->delete();
    DB::table('preestimation')->delete();
    DB::table('recirculation_system')->delete();
    DB::table('sink_kitchen')->delete();
    DB::table('water_heater')->delete();
    DB::table('water_heater_tank')->delete();
    DB::table('water_softener')->delete();
    DB::table('patching_jobs')->delete();
    DB::table('plumbing_jobs')->delete();
    DB::table('job')->delete();


});
 
Route::get('/deleteContracts', function () {
    
    DB::table('contract_permit')->delete();
    DB::table('contract_patching')->delete();
    DB::table('contract_materials')->delete();
    DB::table('contract_inspection')->delete();
    DB::table('contract_final_inspection')->delete();
    DB::table('contract_extra')->delete();
    DB::table('contract_attachment')->delete();
    DB::table('contract')->delete();
   
});

Route::get('/deleteContract', function () {
    
    $job_id = Input::get('job_id');
    DB::table('contract_permit')->where('job_id',$job_id)->delete();
    DB::table('contract_patching')->where('job_id',$job_id)->delete();
    DB::table('contract_materials')->where('job_id',$job_id)->delete();
    DB::table('contract_inspection')->where('job_id',$job_id)->delete();
    DB::table('contract_final_inspection')->where('job_id',$job_id)->delete();
    DB::table('contract_extra')->where('job_id',$job_id)->delete();
    DB::table('contract_attachment')->where('job_id',$job_id)->delete();
    DB::table('contract')->where('job_id',$job_id)->delete();
    echo "Contract data is erased <a href='/'>Home</a>";
});

//Login menangment
Route::get('/login', 'SessionController@index');
Route::post('/signup', 'SessionController@store');

Route::group(['middleware' => 'auth'], function() {
    // lots of routes that require auth middleware


Route::get('/', 'HomeController@index');
// Users menangment
Route::get('users/showUsers', 'UserMenangmentController@index');
Route::get('users/createForm', 'UserMenangmentController@create');
Route::get('users/edit', 'UserMenangmentController@show');
Route::get('users/delete', 'UserMenangmentController@destroy');
Route::post('users/store', 'UserMenangmentController@store');
Route::post('users/update', 'UserMenangmentController@update');
Route::get('users/showUsersTable','UserMenangmentController@showUsersTable');
// Roles menangment
Route::get('users/showRoles', 'RolesController@index');
Route::get('users/createRole', 'RolesController@create');
Route::post('users/storeRole', 'RolesController@store');
Route::get('users/editRole', 'RolesController@edit');
Route::get('users/removeRole', 'RolesController@destroy');
Route::post('users/updateRole', 'RolesController@update');
Route::get('users/showRoleTable','RolesController@showRolesTable');
//Permision
Route::get('permission/show', 'PermisionController@index');
Route::get('permission/showPermisionTable', 'PermisionController@showPermisionTable');
Route::get('permission/assign', 'PermisionController@create');
Route::post('permission/save', 'PermisionController@store');
Route::get('permission/getUserPermissions', 'PermisionController@getUserPermission');
// Customers 
Route::get('customers/show', 'CustomerController@index');
Route::get('customers/showCustomerTable', 'CustomerController@show');
Route::get('customer/createForm', 'CustomerController@create');
Route::post('customer/store', 'CustomerController@store');
Route::get('customer/edit', 'CustomerController@edit');
Route::post('customer/update', 'CustomerController@update');
Route::get('customer/findCustomerbyid', 'CustomerController@findCustomerbyid');
Route::get('customer/delete', 'CustomerController@destroy');
// Patching Crew
Route::get('patching/createForm', 'StaffController@createPatchingCrewForm');
Route::post('patching/store', 'StaffController@storePatchingCrewForm');
Route::get('patching/show', 'StaffController@showPatchingCrewForm');
Route::get('patching/showTable', 'StaffController@showTablePatching');
Route::get('patching/editForm', 'StaffController@editPatchingCrewForm');

// Plumbing Crew
Route::get('plumbing/createForm', 'StaffController@createPlumbingCrewForm');
Route::post('plumbing/store', 'StaffController@storePlumbingCrewForm');
Route::get('plumbing/show', 'StaffController@showPlumbingCrewForm');
Route::get('plumbing/showTable', 'StaffController@showTablePlumbing');
Route::get('plumbing/editForm', 'StaffController@editPlumbingCrewForm');


// Preestimation
Route::get('preestimation/show', 'PreEstimationController@show');
Route::get('preestimation/create', 'PreEstimationController@index');
Route::post('preestimation/store', 'PreEstimationController@store');
Route::get('estimation/edit', 'PreEstimationController@edit');
Route::post('preestimation/storeEstReq','PreEstimationController@storePreRequest');
Route::post('preestimation/update', 'PreEstimationController@updatePreRequest');
// Estimation
Route::get('estimation/show', 'PreEstimationController@showEstimationForm');
Route::post('estimation/store', 'EstimationController@store');
Route::post('estimation/update', 'PreEditController@index');
Route::get('jobs/plumbing', 'EstimationController@plumbing');
Route::get('jobs/patching', 'EstimationController@patching');
Route::get('estimation/showTable', 'EstimationController@showJobsTable');
Route::get('estimation/getTableData', 'EstimationController@showEstimationJobs');
Route::get('estimation/calendarEstimator', 'CalendarController@showJustCalendarEstimator');

// Contract
Route::get('contract/show', 'ContractController@index');
Route::get('contract/create', 'ContractController@create');
Route::get('contract/edit', 'ContractController@create');
Route::post('contract/store', 'ContractController@store');
Route::post('contract/update', 'ContractController@update');
Route::get('contract/showTable', 'ContractController@showTable');
Route::get('contract/getAllContracts', 'ContractController@getAllContracts');


// Jobs Calendar
Route::get('job/addJobinprecalendar', 'PreEstimationController@addInCalendar');
Route::post('job/storeJobinprecalendar', 'CalendarController@jobInPreForm');
Route::get('job/addJobsInpreCalendar', 'CalendarController@showjobInPreForm');
Route::post('job/storeJobPlumbing', 'CalendarController@jobPlumbingCalendar');
Route::get('job/getAllJobPlumbing', 'CalendarController@getAllJobPlumbingEvents');
Route::post('job/storeJobPatching', 'CalendarController@jobPatchingCalendar');
Route::get('job/getAllJobPatching', 'CalendarController@getAllJobPatchingEvents');
Route::get('job/getEventData', 'CalendarController@getEventData');
Route::get('job/getAllPreEstimationEvents', 'CalendarController@getAllPreEstimationEvents');
Route::get('job/showEstimationJobTable', 'PreEstimationController@showJobsInTable');
Route::get('job/showEstimationJobTableData', 'PreEstimationController@showJobsTableData');
//table Jobs
Route::get('jobs/showAllJobTable', 'JobController@showAllJobs');
Route::get('jobs/getAllJobTable', 'JobController@allJobs');
Route::get('jobs/getJobDetails', 'JobController@getJobDetails');
Route::get('jobs/getActiveJobs', 'JobController@showActiveJobs');
Route::get('jobs/activeJobs', 'JobController@activeJobs');


//PDF for preestimation job
Route::get('job/PreestimationPDF', 'PreEstimationController@PreestimationPDF');

});