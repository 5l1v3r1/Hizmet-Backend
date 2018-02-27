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

// Device management routing
Route::group(['middleware' => 'auth'], function () {
    Route::get('/all_devices', 'DeviceController@deviceTable')->middleware('custom_authorization:view_all_devices');
    Route::get('/meter', 'DeviceController@deviceTable')->middleware('custom_authorization:view_meter');
    Route::get('/relay', 'DeviceController@deviceTable')->middleware('custom_authorization:view_relay');
    Route::get('/analyzer', 'DeviceController@deviceTable')->middleware('custom_authorization:view_analyzer');

    //authorization middleware is accomplished in deviceController@getDeviceData
    Route::get('/get_device_data/{type}', 'DeviceController@getDeviceData')->where('type', '(all_devices|meter|relay|analyzer)');

    Route::get('/meter/detail/{id}', 'DeviceController@deviceDetail')->middleware('custom_authorization:view_device_detail')->where('id', '[0-9]{1,5}');

    Route::get('/relay/detail/{id}', 'DeviceController@deviceDetail')->middleware('custom_authorization:view_device_detail')->where('id', '[0-9]{1,5}');

    Route::get('/analyzer/detail/{id}', 'DeviceController@deviceDetail')->middleware('custom_authorization:view_device_detail')->where('id', '[0-9]{1,5}');

    Route::get('/mdindex_get_data/{id}', 'DeviceController@getMeterIndex')->middleware('custom_authorization:view_device_detail')->where(['id' => '[0-9]{1,6}']);

    Route::get('/mdcurvol_get_data/{id}', 'DeviceController@getMeterCurVol')->middleware('custom_authorization:view_device_detail')->where(['id' => '[0-9]{1,6}']);

    Route::get('/adenergy_get_data/{id}', 'DeviceController@getAnalyzerEnergy')->middleware('custom_authorization:view_device_detail')->where(['id' => '[0-9]{1,6}']);

    Route::post('/device_management/add', 'DeviceController@create')->middleware('custom_authorization:add_new_device');

    Route::post('/device_management/get_devices', 'DeviceController@getDevices')->middleware('custom_authorization:add_new_device');

    Route::post('/device_management/get_modems', 'DeviceController@getModems')->middleware('custom_authorization:add_new_device');

    Route::post('/device_management/get_fee_scales', 'DeviceController@getFeeScales')->middleware('custom_authorization:add_new_device');

    Route::post('/device_management/get_alert_definitions', 'DeviceController@getAlertDefinitions')->middleware('custom_authorization:add_new_device');

    Route::post('/device_management/get_device_info', 'DeviceController@getDeviceInfo')->middleware('custom_authorization:add_new_device');

    Route::post('/device_management/delete', 'DeviceController@delete')->middleware('custom_authorization:delete_device');

    Route::post('/device_management/get_graph_data/{id}', 'DeviceController@getGraphData')->middleware('custom_authorization:view_device_detail')->where(['id' => '[0-9]{1,6}']);
});

// Modem Management routing
Route::group(['middleware' => 'auth'], function () {
    Route::get('/modem_management', 'ModemManagementController@showTable')->middleware('custom_authorization:view_modem_management');

    Route::get('/modem_management/detail/{id}', 'ModemManagementController@modemDetail')->middleware('custom_authorization:view_modem_detail')->where('id', '[0-9]{1,5}');

    Route::get('/mm_get_data', 'ModemManagementController@getData')->middleware('custom_authorization:view_modem_management');

    Route::post('/modem_management/add', 'ModemManagementController@create')->middleware('custom_authorization:add_new_modem');

    Route::post('/modem_management/get_clients', 'ModemManagementController@getClients')->middleware('custom_authorization:add_new_modem');

    Route::post('/modem_management/get_info', 'ModemManagementController@getInfo')->middleware('custom_authorization:add_new_modem');

    Route::get('/md_get_data/{device_type}/{type}/{id}', 'DeviceController@getDeviceData')->where('id','[0-9]{1,6}');

    Route::get('/get_device_data/{type}', 'DeviceController@getDeviceData')->where('type', '(all_devices|meter|relay|analyzer)');

    Route::post('/modem_management/delete', 'ModemManagementController@delete')->middleware('custom_authorization:delete_modem');

    Route::post('/modem_management/get_add_infos', 'ModemManagementController@getAddInfo')->middleware('custom_authorization:add_new_modem');

    Route::post('/modem_management/get_airports', 'ModemManagementController@getAirportsByDistance')->middleware('custom_authorization:add_new_modem');
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

// distributor_management operations' routing
Route::group(['middleware' => 'auth'], function () {
    Route::get('/distributor_management', 'DistributorManagementController@showTable')->middleware('custom_authorization:view_distributor_management');

    Route::get('/dm_get_data', 'DistributorManagementController@getData')->middleware('custom_authorization:view_distributor_management');

    Route::get('/ddu_get_data/{type}/{id}', 'UserManagementController@getData')->middleware('custom_authorization:view_distributor_detail')->where(['id' => '[0-9]{1,6}', 'type' => '^distributor$']);

    Route::get('/ddc_get_data/{type}/{id}', 'ClientManagementController@getData')->middleware('custom_authorization:view_distributor_detail')->where(['id' => '[0-9]{1,6}', 'type' => '^distributor$']);

    Route::get('/ddm_get_data/{type}/{id}', 'ModemManagementController@getData')->middleware('custom_authorization:view_distributor_detail')->where(['id' => '[0-9]{1,6}', 'type' => '^distributor$']);

    Route::get('/ddd_get_data/{device_type}/{type}/{id}', 'DeviceController@getDeviceData')->middleware('custom_authorization:view_distributor_detail')->where(['id' => '[0-9]{1,6}', 'type' => '^distributor$']);

    Route::get('/distributor_management/detail/{id}', 'DistributorManagementController@distributorDetail')->middleware('custom_authorization:view_distributor_detail')->where('id', '[0-9]{1,5}');

    Route::post('/distributor_management/add', 'DistributorManagementController@create')->middleware('custom_authorization:add_new_distributor');

    Route::post('/distributor_management/edit_profile', 'DistributorManagementController@create')->middleware('custom_authorization:edit_profile_info');

    Route::post('/distributor_management/edit_image', 'DistributorManagementController@uploadImage')->middleware('custom_authorization:edit_profile_info');

    Route::post('/distributor_management/upload_image', 'DistributorManagementController@uploadImage')->middleware('custom_authorization:view_distributor_management');

    Route::post('/distributor_management/get_info', 'DistributorManagementController@getInfo')->middleware('custom_authorization:add_new_distributor');

    Route::post('/distributor_management/delete', 'DistributorManagementController@delete')->middleware('custom_authorization:delete_distributor');

    Route::post('/distributor_management/detail/add/ainfo/{id}', 'DistributorManagementController@createAddInfo')->middleware('custom_authorization:view_distributor_detail')->where(['id' => '[0-9]{1,6}']);

    Route::post('/distributor_management/detail/ainfo_delete', 'DistributorManagementController@deleteAddInfo')->middleware('custom_authorization:delete_distributor');

    Route::post('/distributor_management/detail/get_ainfo', 'DistributorManagementController@getAddInfo')->middleware('custom_authorization:view_distributor_detail');

    Route::get('/ai_get_data/{id}', 'DistributorManagementController@getAddInfoList')->middleware('custom_authorization:view_distributor_detail')->where(['id' => '[0-9]{1,6}']);
});

// my_profile operations' routing
Route::group(['middleware' => 'auth'], function () {
    Route::get('/my_profile', 'UserManagementController@getProfile')->middleware('custom_authorization:view_my_profile');

    Route::post('/my_profile/edit/account', 'UserManagementController@editProfileInfo')->middleware(['custom_authorization:view_my_profile','custom_authorization:edit_profile_info']);
});

// event_logs routing
Route::group(['middleware' => 'auth'], function () {
    Route::get('/event_logs', 'EventlogsController@showTable')->middleware('custom_authorization:view_event_logs');

    Route::get('/el_get_data/{type}/{id}', 'EventlogsController@getData')->middleware('custom_authorization:view_event_logs');
});

// System Status Menu
Route::group(['middleware' => 'auth'], function () {
    // fee_scale routing
    Route::get('/fee_scale', 'FeeScaleController@showTable')->middleware('custom_authorization:view_fee_scale');

    Route::get('/fs_get_data', 'FeeScaleController@getData')->middleware('custom_authorization:view_fee_scale');

    Route::post('/fee_scale/get_info', 'FeeScaleController@getInfo')->middleware('custom_authorization:add_new_fee_scale');

    Route::post('/fee_scale/add', 'FeeScaleController@create')->middleware('custom_authorization:add_new_fee_scale');

    Route::post('/fee_scale/delete', 'FeeScaleController@delete')->middleware('custom_authorization:delete_fee_scale');

    // alerts routing
    Route::get('/alerts', 'AlertController@showTable')->middleware('custom_authorization:view_alerts');

    Route::get('/al_get_data', 'AlertController@getData')->middleware('custom_authorization:view_alerts');

    Route::get('/al_get_data/{type}/{id}', 'AlertController@getData')->middleware('custom_authorization:view_alerts')->where(['id' => '[0-9]{1,6}', 'type' => '(modem|meter|relay|analyzer|client_management|distributor_management)']);

    Route::get('/ald_get_data', 'AlertController@getDefinitionData')->middleware('custom_authorization:view_alerts');

    Route::post('/alerts/add', 'AlertController@create')->middleware('custom_authorization:add_new_alert_definition');

    Route::post('/alerts/delete', 'AlertController@delete')->middleware('custom_authorization:delete_alert_definition');

    Route::post('/alerts/update_user_read', 'AlertController@updateUserRead')->middleware('custom_authorization:view_alerts');

    Route::post('/alerts/get_definition_info', 'AlertController@getDefinitionInfo')->middleware('custom_authorization:add_new_alert_definition');

    //temperature routing
    Route::get('/temperature', 'TemperatureController@showTable')->middleware('custom_authorization:view_temperature');

    Route::get('/at_get_data', 'TemperatureController@getData')->middleware('custom_authorization:view_temperature');

    Route::get('/temperature/detail/{id}', 'TemperatureController@temperatureDetail')->middleware('custom_authorization:view_temperature')->where('id', '[0-9]{1,5}');

    Route::get('/td_get_data/{id}', 'TemperatureController@temperatureTableData')->middleware('custom_authorization:view_temperature')->where('id', '[0-9]{1,5}');

    // Announcement
    Route::get('/announcement', 'AnnouncementController@showTable')->middleware('custom_authorization:view_announcement');
});

// contact_us routing
Route::group(['middleware' => 'auth'], function () {

    Route::get('/contact_us', function () {

        return view('pages.contact_us');
    })->middleware('custom_authorization:view_contact_us');

    //1a9e814d16108e5fd619be46e07ce614
    //http://api.openweathermap.org/data/2.5/weather?q=D%C3%BCzce,TR&appid=1a9e814d16108e5fd619be46e07ce614
    


    //test purpose, this will be deleted after test

    /*Route::get('/contact_us', function () {

        \App\Http\Controllers\TemperatureController::traverseAirports();
    })->middleware('custom_authorization:view_contact_us');*/

    Route::post('/contact_us/send_message', 'ContactUsController@sendMail')->middleware('custom_authorization:view_contact_us');
});

// Statistics routing
Route::group(['middleware'=>'auth'],function (){

    Route::get('/graphs', 'StatsController@showGraphs')->middleware('custom_authorization:view_graphs');

    Route::post('/graphs/get_distributors', 'StatsController@getDistributors')->middleware('custom_authorization:view_graphs');

    Route::post('/graphs/get_graph_data', 'StatsController@getGraphData')->middleware('custom_authorization:view_graphs');

    Route::get('/comparison', function () {
        return view('pages.comparison');
    })->middleware('custom_authorization:view_comparison');
});

// Reporting routing
Route::group(['middleware'=>'auth'],function (){
    Route::get('/reporting', 'ReportController@showTable')->middleware('custom_authorization:view_reporting');

    Route::get('/r_get_data/{type}', 'ReportController@getData')->middleware('custom_authorization:view_reporting')->where('type', '(report|template)');

    Route::get('/rt_get_data', 'ReportController@getTemplateData')->middleware('custom_authorization:view_reporting');

    Route::post('/reporting/add', 'ReportController@create')->middleware('custom_authorization:create_new_report');

    Route::post('/reporting/delete', 'ReportController@deleteReport')->middleware('custom_authorization:delete_report');

    Route::post('/reporting/startstop', 'ReportController@startStop')->middleware('custom_authorization:view_reporting');

    Route::post('/reporting/download_report_file', 'ReportController@downloadReportFile')->middleware('custom_authorization:view_reporting');

    Route::post('/reporting/rerun', 'ReportController@reRunTemplate')->middleware('custom_authorization:create_new_report');

    Route::post('/reporting/get_info', 'ReportController@getInfo')->middleware('custom_authorization:create_new_report');

    Route::post('/reporting/get_add_infos', 'ReportController@getAddInfo')->middleware('custom_authorization:create_new_report');
});

// Organization Schema routing
Route::group(['middleware'=>'auth'],function (){
    // Route::get('/organization_schema', 'OrganizationController@showTable')->middleware('custom_authorization:view_organization_schema');

    // Route::post('/organization_schema/get_distributors', 'OrganizationController@getDistributors')->middleware('custom_authorization:view_organization_schema');

    Route::post('/organization_schema/get_organization_schema', 'OrganizationController@getOrganizationSchema')->middleware('custom_authorization:view_organization_schema');

    Route::post('/organization_schema/node_detail', 'OrganizationController@getNodeDetail')->middleware('custom_authorization:view_organization_schema');

    Route::post('/organization_schema/add_node', 'OrganizationController@createNode')->middleware('custom_authorization:create_new_node');

    Route::post('/organization_schema/move_node', 'OrganizationController@moveNode')->middleware('custom_authorization:create_new_node');

    Route::post('/organization_schema/update_node_info', 'OrganizationController@updateNodeInfo')->middleware('custom_authorization:create_new_node');

    Route::post('/organization_schema/delete_node', 'OrganizationController@deleteNode')->middleware('custom_authorization:delete_node');
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

