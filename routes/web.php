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

