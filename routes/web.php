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

    Route::post('/system_summary/get_booking', 'DashboardController@getLastBooking')->middleware('custom_authorization:view_system_summary');

    Route::post('/system_summary/get_order', 'DashboardController@getLastOrder')->middleware('custom_authorization:view_system_summary');

    Route::post('/system_summary/get_client', 'DashboardController@getLastClient')->middleware('custom_authorization:view_system_summary');

    Route::post('/system_summary/get_seller', 'DashboardController@getLastSeller')->middleware('custom_authorization:view_system_summary');

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

    Route::get('/cpp_get_data/{type}/{id}', 'FinanceController@getData')->middleware('custom_authorization:view_client_management');
    Route::get('/spp_get_data/{type}/{id}', 'FinanceController@getData')->middleware('custom_authorization:view_client_management');

    Route::get('/cdu_get_data/{type}/{id}', 'UserManagementController@getData')->middleware('custom_authorization:view_client_detail')->where(['id' => '[0-9]{1,6}', 'type' => '^client$']);

    Route::get('/cdb_get_data/{type}/{id}', 'BookingManagementController@getData')->middleware('custom_authorization:view_client_detail')->where(['id' => '[0-9]{1,6}', 'type' => '^client$']);

    Route::get('/cdo_get_data/{type}/{id}', 'OrderManagementController@getData')->middleware('custom_authorization:view_client_detail');

    Route::get('/client_management/detail/{id}', 'ClientManagementController@clientDetail')->middleware('custom_authorization:view_client_detail')->where('id', '[0-9]{1,5}');

    Route::post('/client_management/edit_image', 'ClientManagementController@uploadImage')->middleware('custom_authorization:edit_profile_info');

    Route::post('/client_management/get_info', 'ClientManagementController@getInfo')->middleware('custom_authorization:add_new_client');

    Route::post('/client_management/delete', 'ClientManagementController@delete')->middleware('custom_authorization:delete_client');

    Route::post('/client_management/add', 'ClientManagementController@create')->middleware('custom_authorization:add_new_client');

    Route::post('/client_management/detail/{id}/{op}', 'ClientManagementController@userDetail')->middleware(['custom_authorization:view_user_detail','custom_authorization:add_new_user']);



});
Route::group(['middleware' => 'auth'], function () {
    Route::get('/seller_management', 'SellerManagementController@showTable')->middleware('custom_authorization:view_seller_management');

    Route::get('/sm_get_data', 'SellerManagementController@getData')->middleware('custom_authorization:view_seller_management');

    Route::get('/sdu_get_data/{type}/{id}', 'SellerManagementController@getData')->middleware('custom_authorization:view_seller_management')->where(['id' => '[0-9]{1,6}', 'type' => '^client$']);

    Route::get('/sob_get_data/{id}', 'SellerManagementController@getOffer')->middleware('custom_authorization:view_seller_management');

    Route::get('/soo_get_data/{type}/{id}', 'OrderManagementController@getData')->middleware('custom_authorization:view_seller_management');

    Route::get('/seller_management/detail/{id}', 'SellerManagementController@sellerDetail')->middleware('custom_authorization:view_seller_management')->where('id', '[0-9]{1,5}');

    Route::post('/seller_management/edit_image', 'SellerManagementController@uploadImage')->middleware('custom_authorization:edit_profile_info');

    Route::post('/seller_management/get_info', 'SellerManagementController@getInfo')->middleware('custom_authorization:add_new_seller');

    Route::post('/seller_management/delete', 'SellerManagementController@delete')->middleware('custom_authorization:delete_seller');

    Route::post('/seller_management/add', 'SellerManagementController@create')->middleware('custom_authorization:add_new_seller');

    Route::post('/seller_management/detail/{id}/{op}', 'SellerManagementController@userDetail')->middleware(['custom_authorization:view_seller_detail','custom_authorization:add_new_seller']);



});


Route::group(['middleware' => 'auth'], function () {
    Route::get('/booking_management', 'BookingManagementController@showTable')->middleware('custom_authorization:view_booking_management');

    Route::get('/bm_get_data', 'BookingManagementController@getData')->middleware('custom_authorization:view_user_management');

    Route::get('/bd_get_data/{id}', 'BookingManagementController@getData')->middleware('custom_authorization:view_user_management');

    Route::get('/bo_get_data/{id}', 'BookingManagementController@getOffer')->middleware('custom_authorization:view_user_management');

    Route::get('/booking_management/detail/{id}', 'BookingManagementController@bookingDetail')->middleware('custom_authorization:view_user_detail')->where('id', '[0-9]{1,5}');

    Route::get('/booking_management/offer/{id}', 'BookingManagementController@offerDetail')->middleware('custom_authorization:view_user_detail')->where('id', '[0-9]{1,5}');

    Route::post('/booking_management/add', 'BookingManagementController@create')->middleware('custom_authorization:add_new_user');

    Route::post('/booking_management/get_info', 'BookingManagementController@getInfo')->middleware('custom_authorization:add_new_user');

    Route::post('/booking_management/detail/{id}/{op}', 'BookingManagementController@bookingChange')->middleware(['custom_authorization:view_user_detail']);




});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/order_management', 'OrderManagementController@showTable')->middleware('custom_authorization:view_booking_management');

    Route::get('/om_get_data', 'OrderManagementController@getData')->middleware('custom_authorization:view_user_management');

    Route::get('/od_get_data/{id}', 'OrderManagementController@getData')->middleware('custom_authorization:view_user_management');

    Route::get('/oo_get_data/{id}', 'OrderManagementController@getOffer')->middleware('custom_authorization:view_user_management');

    Route::get('/order_management/detail/{id}', 'OrderManagementController@orderDetail')->middleware('custom_authorization:view_user_detail')->where('id', '[0-9]{1,5}');

    Route::get('/order_management/offer/{id}', 'OrderManagementController@offerDetail')->middleware('custom_authorization:view_user_detail')->where('id', '[0-9]{1,5}');

    Route::post('/order_management/add', 'OrderManagementController@create')->middleware('custom_authorization:add_new_user');

    Route::post('/order_management/get_info', 'OrderManagementController@getInfo')->middleware('custom_authorization:add_new_user');

    Route::post('/order_management/detail/{id}/{op}', 'OrderManagementController@bookingChange')->middleware(['custom_authorization:view_user_detail']);

    Route::post('/offer_management/change', 'OrderManagementController@create')->middleware(['custom_authorization:view_user_detail']);

    Route::get('/order_billing/{id}', 'PdfBillingController@pdfview')->middleware('custom_authorization:view_user_management');

    Route::get('/order_payment', 'OrderManagementController@payment');

});
Route::group(['middleware' => 'auth'], function () {

    Route::get('/finance', 'FinanceController@showFinance')->middleware('custom_authorization:view_booking_management');

    Route::get('/cp_get_data/{op}', 'FinanceController@getData')->middleware('custom_authorization:view_user_management');
    Route::get('/sp_get_data/{op}', 'FinanceController@getData')->middleware('custom_authorization:view_user_management');
    Route::get('/finance/getFinanceInfo', 'FinanceController@getFinanceInfo')->middleware('custom_authorization:view_user_management');
    Route::get('/finance/getSelectUser/{type}', 'FinanceController@getSelectUser')->middleware('custom_authorization:view_user_management');
    Route::post('/finance/addFinance', 'FinanceController@create')->middleware('custom_authorization:view_user_management');
    Route::get('/send_billing/{id}', 'PdfBillingController@pdfview2')->middleware('custom_authorization:view_user_management');




});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/event_logs', 'EventlogsController@showTable')->middleware('custom_authorization:view_event_logs');

    Route::get('/el_get_data/{type}/{id}', 'EventlogsController@getData')->middleware('custom_authorization:view_event_logs');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/my_profile', 'UserManagementController@getProfile')->middleware('custom_authorization:view_my_profile');

    Route::post('/my_profile/edit/account', 'UserManagementController@editProfileInfo')->middleware(['custom_authorization:view_my_profile','custom_authorization:edit_profile_info']);
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
