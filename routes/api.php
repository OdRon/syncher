<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['namespace' => 'App\\Api\\V1\\Controllers'], function(Router $api) {
        $api->group(['prefix' => 'auth'], function(Router $api) {
            $api->post('signup', 'SignUpController@signUp');
            $api->post('login', 'LoginController@login');

            $api->post('recovery', 'ForgotPasswordController@sendResetEmail');
            $api->post('reset', 'ResetPasswordController@resetPassword');

            $api->post('logout', 'LogoutController@logout');
            $api->post('refresh', 'RefreshController@refresh');
            $api->get('me', 'UserController@me');
        });

        $api->group(['middleware' => 'jwt.auth'], function(Router $api) {

            $api->get('protected', 'RandomController@protected_route');

            $api->group(['middleware' => 'jwt.refresh'], function(Router $api) {
                $api->get('refresh', 'RandomController@refresh_route');
            });
        });

        $api->get('hello', 'RandomController@hello');



        $api->group(['middleware' => 'jwt.auth'], function(Router $api) {

            $api->resource('facility', 'FacilityController');
            $api->post('lablogs', 'LablogController@lablogs');

            // Route group that matches records between national and lab
            $api->group(['prefix' => 'synch'], function(Router $api) {

                $api->post('patients', 'EidController@synch_patients');
                $api->post('batches', 'EidController@synch_batches');
                $api->post('worksheets', 'EidController@worksheets');

                $api->post('viralpatients', 'VlController@synch_patients');
                $api->post('viralbatches', 'VlController@synch_batches');
                $api->post('viralworksheets', 'VlController@worksheets');

            });

            $api->group(['prefix' => 'insert'], function(Router $api) {

                $api->post('patients', 'EidController@patients');
                $api->post('batches', 'EidController@batches');
                $api->post('worksheets', 'EidController@worksheets');

                $api->post('viralpatients', 'VlController@patients');
                $api->post('viralbatches', 'VlController@batches');
                $api->post('viralworksheets', 'VlController@worksheets');

            });

            $api->group(['prefix' => 'update'], function(Router $api) {

                $api->post('worksheets', 'EidController@update_worksheets');
                $api->post('mothers', 'EidController@update_mothers');
                $api->post('patients', 'EidController@update_patients');
                $api->post('batches', 'EidController@update_batches');
                $api->post('samples', 'EidController@update_samples');

                $api->post('viralworksheets', 'VlController@update_worksheets');
                $api->post('viralpatients', 'VlController@update_patients');
                $api->post('viralbatches', 'VlController@update_batches');
                $api->post('viralsamples', 'VlController@update_samples');

            });

            $api->group(['prefix' => 'delete'], function(Router $api) {

                $api->post('worksheets', 'EidController@delete_worksheets');
                $api->post('mothers', 'EidController@delete_mothers');
                $api->post('patients', 'EidController@delete_patients');
                $api->post('samples', 'EidController@delete_samples');

                $api->post('viralworksheets', 'VlController@delete_worksheets');
                $api->post('viralpatients', 'VlController@delete_patients');
                $api->post('viralsamples', 'VlController@delete_samples');

            });

        });



    });
});
