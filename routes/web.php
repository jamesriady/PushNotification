<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::post('/subscribe','PushController@update');
Route::post('/unsubscribe','PushController@destroy');
Route::get('/push/notification','NotificationController@sendNotification');
Route::get('/notifications','NotificationController@index');
Route::get('/notifications/read','NotificationController@readAll');
Route::get('/notifications/read/{id}','NotificationController@read');
Route::post('/notifications/{id}/dismiss','NotificationController@dismiss');