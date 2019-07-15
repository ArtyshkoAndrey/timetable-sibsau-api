<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('api')->group(function () {
  Route::get('/group/{id}', 'GroupController@show');
  Route::get('/group/search/{text}/{count}', 'GroupController@searchGroup');
  Route::get('/parse/group/{id}', 'ParseController@group');
  Route::get('/parse/teacher/{id}', 'ParseController@teacher');
});
