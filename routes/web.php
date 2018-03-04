<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

//auth default routings
Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () {
        //default page changes according to user privileges
        $default_active_page = \App\Helpers\Helper::create_menu(Auth::user()->operations,'/',true);

        return redirect('/'.$default_active_page);
    });
});

// Dashboard operations' routing
Route::group(['middleware' => 'auth'], function () {
    Route::get('/system_summary', function () {
        return view('pages.system_summary');
    })->middleware('custom_authorization:view_system_summary');

    Route::get('/asset_map', function () {
        return view('pages.asset_map');
    })->middleware('custom_authorization:view_asset_map');

    Route::post('/asset_map/get_data', 'DashboardController@assetMapData')->middleware('custom_authorization:view_asset_map');

    Route::post('/asset_map/get_city_data', 'DashboardController@assetCityData')->middleware('custom_authorization:view_asset_map');

    Route::post('/system_summary/get_reactives', 'DashboardController@getLastReactives')->middleware('custom_authorization:view_system_summary');

    Route::post('/system_summary/get_alerts', 'DashboardController@getLastAlerts')->middleware('custom_authorization:view_system_summary');

    Route::post('/system_summary/get_devices', 'DashboardController@getLastDevices')->middleware('custom_authorization:view_system_summary');

    Route::post('/system_summary/get_ucds', 'DashboardController@getLastUcds')->middleware('custom_authorization:view_system_summary');

    /*
    Route::get('/consumption_map', function () {
        return view('pages.consumption_map');
    })->middleware('custom_authorization:view_consumption_map'); */

});



// user_management operations' routing
Route::group(['middleware' => 'auth'], function () {
    Route::get('/user_management', 'UserManagementController@showTable')->middleware('custom_authorization:view_user_management');

    Route::get('/um_get_data', 'UserManagementController@getData')->middleware('custom_authorization:view_user_management');

    Route::get('/user_management/detail/{id}', 'UserManagementController@userDetail')->middleware('custom_authorization:view_user_detail')->where('id', '[0-9]{1,5}');

    Route::post('/user_management/add', 'UserManagementController@create')->middleware('custom_authorization:add_new_user');

    Route::post('/user_management/upload_image', 'UserManagementController@uploadImage')->middleware('custom_authorization:add_new_user');

    Route::post('/user_management/edit_image', 'UserManagementController@uploadImage')->middleware('custom_authorization:edit_profile_info');

    Route::post('/user_management/get_info', 'UserManagementController@getInfo')->middleware('custom_authorization:add_new_user');

    Route::post('/user_management/detail/{id}/{op}', 'UserManagementController@userDetail')->middleware(['custom_authorization:view_user_detail','custom_authorization:add_new_user']);

});

// client_management operations' routing
Route::group(['middleware' => 'auth'], function () {
    Route::get('/client_management', 'ClientManagementController@showTable')->middleware('custom_authorization:view_client_management');

    Route::get('/cm_get_data', 'ClientManagementController@getData')->middleware('custom_authorization:view_client_management');

    Route::get('/cdu_get_data/{type}/{id}', 'UserManagementController@getData')->middleware('custom_authorization:view_client_detail')->where(['id' => '[0-9]{1,6}', 'type' => '^client$']);

    Route::get('/cdm_get_data/{type}/{id}', 'ModemManagementController@getData')->middleware('custom_authorization:view_client_detail')->where(['id' => '[0-9]{1,6}', 'type' => '^client$']);

    Route::get('/cdd_get_data/{device_type}/{type}/{id}', 'DeviceController@getDeviceData')->middleware('custom_authorization:view_client_detail')->where(['id' => '[0-9]{1,6}', 'type' => '^client$']);

    Route::get('/client_management/detail/{id}', 'ClientManagementController@clientDetail')->middleware('custom_authorization:view_client_detail')->where('id', '[0-9]{1,5}');

    Route::post('/client_management/add', 'ClientManagementController@create')->middleware('custom_authorization:add_new_client');

    Route::post('/client_management/upload_image', 'ClientManagementController@uploadImage')->middleware('custom_authorization:add_new_client');

    Route::post('/client_management/edit_profile', 'ClientManagementController@create')->middleware('custom_authorization:edit_profile_info');

    Route::post('/client_management/edit_image', 'ClientManagementController@uploadImage')->middleware('custom_authorization:edit_profile_info');

    Route::post('/client_management/get_info', 'ClientManagementController@getInfo')->middleware('custom_authorization:add_new_client');

    Route::post('/client_management/delete', 'ClientManagementController@delete')->middleware('custom_authorization:delete_client');

});


Route::group(['middleware'=>'auth'],function (){
	Route::get('/simulate_alert', function () {
        \App\Helpers\ScheduledTasks::detectAlarms();
        //abort(404);
    })->middleware('custom_authorization:view_contact_us');
});

// if not match anything then abort:404
Route::group(['middleware' => 'auth'], function () {
    Route::get('/{page}', function () {
        //\App\Helpers\ScheduledTasks::detectAlarms();
        abort(404);
    })->middleware('custom_authorization:view_contact_us');

});

