<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('admin', function () {
    return view('admin_template');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::get('reconciliation', [
    'as' => 'reconciliation', 'uses' => 'ReconciliationController@index'
	]);

	Route::get('unmatched', [
    'as' => 'unmatched', 'uses' => 'ReconciliationController@unmatched'
	]);

	Route::get('suggestions', [
    'as' => 'suggestions', 'uses' => 'ReconciliationController@suggestions'
	]);

	Route::post('compare', [
	    'as' => 'compare', 'uses' => 'ReconciliationController@compare'
	]);

});
