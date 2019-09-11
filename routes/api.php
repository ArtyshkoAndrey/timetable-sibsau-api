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
    Route::get('/teacher/{id}', 'TeacherController@show');
  Route::get('/posts', 'PostController@all');
  Route::get('/posts/test', 'PostController@test');
  Route::get('/events', 'EventController@all');
  Route::get('/post/{id}', 'PostController@post');
  Route::get('/group/search/{text}/{count}', 'GroupController@searchGroup');
  Route::get('/teacher/search/{text}/{count}', 'TeacherController@searchTeacher');
  Route::get('/parse/group/{id}', 'ParseController@group');
  Route::get('/parse/teacher/{id}', 'ParseController@teacher');
  Route::get('/notify', function() {
    $msg = array (
      "title" =>"Расписание обновлено",
      "message" => "Зайдите в приложение для обновления расписания"
    );
    $fields = array (
      "to" => "/topics/allDevice",
      'data'  => $msg,
      "notification" =>array("title" => "Доступно новое расписание", "body" => "Зайдите и загрузите новое расписание, что бы оставаться с актуальным расписанием даже в оффлайн", "sound" => "default", "content_available" => true, "priority" => "high")
    );

    $headers = array (
      'Authorization: key=AAAAB42lrsY:APA91bE8gsm3g16tb3jAGMxZTIpvIfVRQzC3aYYut4RUDrfd7wmlPtCIg7vORB1O6ssQjYzMYOaynvxRY9xjbR5huG-g2LlIDOBv3bQQBD-QzZ-zDhao6A8wzIDjQ0JNunscC5GKqEcS',
      'Content-Type: application/json');

    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    $result = curl_exec($ch );
    curl_close( $ch );
  });
});
