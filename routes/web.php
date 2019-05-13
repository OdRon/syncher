<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('reset_password/{token}', ['as' => 'password.reset', function($token)
{
    // implement your reset password route here!
}]);

Route::redirect('/', 'login');

Route::get('login/facility', 'Auth\\LoginController@fac_login')->name('login.facility');
Route::post('login/facility', 'Auth\\LoginController@facility_login');

Route::prefix('download')->name('download.')->group(function(){

	Route::get('user_guide', 'DownloadController@user_guide')->name('user_guide');
	Route::get('consumption', 'DownloadController@consumption')->name('consumption');
	Route::get('hei', 'DownloadController@hei')->name('hei');
	Route::get('poc', 'DownloadController@poc')->name('poc');
	Route::get('eid_req', 'DownloadController@eid_req')->name('eid_req');
	Route::get('vl_req', 'DownloadController@vl_req')->name('vl_req');

});

Auth::routes();

Route::post('facility/search/', 'FacilityController@search')->name('facility.search');

Route::middleware(['web', 'auth'])->group(function(){
	Route::get('/home', 'HomeController@index')->name('home');
	Route::get('elvis/{year}/{quarter}', 'ResultController@get_incomplient_patient_record');

	Route::middleware(['only_utype:12'])->group(function(){
		Route::get('allocations/{testtype?}', 'AllocationsController@index')->name('allocations');
		Route::get('allocationdrfs/{lab?}', 'AllocationsController@drf')->name('drf');
		Route::get('viewallocation/{testtype?}/{year?}/{month?}', 'AllocationsController@view_allocations')->name('viewallocation');
		Route::get('approveallocation/{lab}/{testtype?}/{year?}/{month?}', 'AllocationsController@approve_allocations')->name('approveallocation');
		Route::post('approveallocation', 'AllocationsController@save_allocation_approval')->name('approveallocation');

		Route::resource('labcontacts', 'AllocationContactsController');
	});

	Route::middleware(['only_utype:14,15'])->group(function(){
		Route::get('national/allocation', 'AllocationsController@national_allocation')->name('national.allocation');
		Route::post('national/allocation', 'AllocationsController@national_allocation');
		Route::get('lab/allocation/{allocation?}/{type?}/{approval?}', 'AllocationsController@lab_allocation')->name('lab.allocation');
		// Route::get('lab/allocation/{allocation?}/{type?}/{approval?}', 'AllocationsController@lab_allocation')->name('lab.allocation');
		Route::put('lab/allocation/{allocation_detail}/edit', 'AllocationsController@edit_lab_allocation');

		Route::get('lab/consumption/{consumption?}', 'ConsumptionController@history');
		Route::get('cancelallocation', 'AllocationsController@cancel_lab_allocation');
	});

	Route::group(['middleware' => ['only_utype:10,16']], function () {
		Route::prefix('email')->name('email.')->group(function () {
			Route::get('preview/{email}', 'EmailController@demo')->name('demo');
			Route::post('preview/{email}', 'EmailController@demo_email')->name('demo_email');
		});
		Route::resource('email', 'EmailController');
	});


	Route::prefix('hei')->name('hei.')->group(function(){
		Route::get('validate/{year?}/{month?}', 'HEIController@index')->name('followup');
		Route::get('followup/{duration?}/{validation?}/{year?}/{month?}', 'HEIController@followup')->name('followup');
		Route::post('followup', 'HEIController@followup');
	});

	Route::prefix('reports')->name('reports.')->group(function(){
		Route::get('{testtype?}', 'ReportController@index')->name('reports');
		Route::get('nodata/{testtype?}/{year?}/{month?}', 'ReportController@nodata')->name('nodata');
		Route::get('utilization/{testtype?}/{year?}/{month?}', 'ReportController@utilization')->name('utilization');
		Route::post('/', 'ReportController@generate');
	});

	Route::group(['middleware' => ['only_utype:3,8']], function(){
		Route::prefix('sample')->name('sample.')->group(function(){
			Route::get('{testtype}/{patient}/edit', 'SamplesController@edit')->name('edit');
			Route::put('{testtype}/{patient}/update', 'SamplesController@update')->name('update');
		});
		Route::resource('sample', 'SamplesController');
	});

	Route::group(['middleware' => ['only_utype:8']], function () {
		Route::prefix('patients')->name('patients.')->group(function () {
			Route::get('/{testtype}', 'PatientsController@index');
			Route::get('/{testtype}/{patient}/edit', 'PatientsController@edit');
			Route::put('/{testtype}/{patient}/edit', 'PatientsController@edit');
			Route::get('/{testtype}/{patient}/merge', 'PatientsController@merge');
			Route::put('/{testtype}/{patient}/merge', 'PatientsController@merge');
			Route::get('/{testtype}/{patient}/transfer', 'PatientsController@transfer');
			Route::put('/{testtype}/{patient}/transfer', 'PatientsController@transfer');
			Route::post('search/{testtype}/{facility}', 'PatientsController@search');
		});
	});
	
	Route::get('results/{testtype?}', 'ResultController@index')->name('results');
	Route::get('results/{id}/{testtype}/{type}', 'ResultController@specific')->name('specific.results');

	// ----------- Searches -----------
	// ---- Search Options ----
	Route::post('patient/search/', 'GenerealController@patientSearch')->name('patient.search');
	Route::post('batch/search/', 'GenerealController@batchSearch')->name('batch.search');
	Route::post('county/search/', 'GenerealController@countySearch')->name('county.search');
	Route::post('supportfacility/search/', 'GenerealController@facilitySearch')->name('supportfacility.search');
	// ---- Search Options ----
	// ---- Search Results ----
	Route::get('batchsearchresult/{testtype}/{batch}', 'GenerealController@batchresult')->name('batchsearchresult');
	Route::get('facilitysearchresult/{facility}', 'GenerealController@facilityresult')->name('facilitysearchresult');
	Route::get('patientsearchresult/{testtype}/{batch}', 'GenerealController@patientresult')->name('patientsearchresult');
	// ---- Search Results ----
	// ---- Print Result ----
	Route::get('printindividualresult/{testSysm}/{sample}', 'GenerealController@print_individual')->name('printindividualresult');
	Route::get('printindividualbatch/{testSysm}/{batch}', 'GenerealController@print_batch_individual')->name('printindividualbatch');
	Route::get('printbatchsummary/{testSysm}/{batch}', 'GenerealController@print_batch_summary')->name('printbatchsummary');
	// ---- Print Result ----
	// ---- Search AJAX ----
	Route::get('eidresults', 'GenerealController@eidresults')->name('eidresults');
	Route::get('vlresults', 'GenerealController@vlresults')->name('vlresults');
	// ---- Search AJAX ----
	// ----------- Searches -----------

	Route::get('sites', 'SiteController@index')->name('sites');

	Route::get('users', 'UserController@index')->name('users');
	Route::get('user/add', 'UserController@create')->name('user.add');
	Route::get('user/passwordReset/{user?}', 'UserController@passwordreset')->name('passwordReset');
	Route::resource('user', 'UserController');

	Route::get('test', function(){
		// echo max([3,5]);
		\App\Synch::synch_allocations();
	});
});

Route::get('patientstatus', 'HEIController@placeResults');
Route::get('sendsms', 'GenerealController@send_sms');

Route::get('synch/', function(){
	// \App\Synch::synch_allocations();
});
Route::get('positives/{year?}/{month?}', 'HomeController@test');
// $connected = @fsockopen("www.example.com", 80); 