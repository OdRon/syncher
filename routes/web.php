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

Auth::routes();

Route::post('facility/search/', 'FacilityController@search')->name('facility.search');

Route::middleware(['web', 'auth'])->group(function(){
	Route::get('/home', 'HomeController@index')->name('home');

	Route::prefix('hei')->name('hei.')->group(function(){
		Route::get('validate/{year?}/{month?}', 'HEIController@index')->name('followup');
		Route::get('followup/{duration?}/{validation?}', 'HEIController@followup')->name('followup');
		Route::post('followup', 'HEIController@followup');
	});

	Route::get('reports/{testtype?}', 'ReportController@index')->name('reports');
	Route::post('reports', 'ReportController@generate');

	Route::get('results/{testtype?}', 'ResultController@index')->name('results');
	Route::get('results/{id}/{testtype}/{type}', 'ResultController@specific')->name('specific.results');

	// ----------- Searches -----------
	// ---- Search Options ----
	Route::post('patient/search/', 'GenerealController@patientSearch')->name('patient.search');
	Route::post('batch/search/', 'GenerealController@batchSearch')->name('batch.search');
	Route::post('county/search/', 'GenerealController@countySearch')->name('county.search');
	Route::post('facility/search/', 'GenerealController@facilitySearch')->name('facility.search');
	// ---- Search Options ----
	// ---- Search Results ----
	Route::get('batchsearchresult/{testtype}/{batch}', 'GenerealController@batchresult')->name('batchsearchresult');
	Route::get('facilitysearchresult/{facility}', 'GenerealController@facilityresult')->name('facilitysearchresult');
	// ---- Search Results ----
	Route::get('eidresults', 'GenerealController@eidresults')->name('eidresults');
	Route::get('vlresults', 'GenerealController@vlresults')->name('vlresults');
	// ----------- Searches -----------

	Route::get('sites', 'SiteController@index')->name('sites');

	Route::get('users', 'UserController@index')->name('users');
	Route::get('user/add', 'UserController@create')->name('user.add');
	Route::get('user/passwordReset/{user?}', 'UserController@passwordreset')->name('passwordReset');
	Route::resource('user', 'UserController');

	Route::get('test', 'GenerealController@index');
});

Route::get('patientstatus', 'HEIController@placeResults');

