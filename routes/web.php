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
	Route::get('followup', 'HEIController@index')->name('followup');
	
	Route::get('/home', 'HomeController@index')->name('home');

	Route::get('reports/{testtype?}', 'ReportController@index')->name('reports');
	Route::post('reports', 'ReportController@generate');

	// ------ Searches --------
	Route::post('county/search/', 'HomeController@countysearch')->name('county.search');
	// ------ Searches --------

	Route::get('users', 'UserController@index')->name('users');
	Route::get('user/add', 'UserController@create')->name('user.add');
	Route::get('user/passwordReset/{user?}', 'UserController@passwordreset')->name('passwordReset');
	Route::resource('user', 'UserController');
});

