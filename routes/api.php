<?php

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use \Curl\MultiCurl;

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
  Route::get('/test', function() {
    $groups = App\Group::all();
    $lessons = App\Lesson::get();
    if (count($lessons) > 0) {
      $lessons->each->delete();
    }
    $multi_curl = new MultiCurl();
    $multi_curl->setProxy('167.71.103.168', '3128');
    $multi_curl->setConcurrency(40);
    $multi_curl->setConnectTimeout(100);
    $multi_curl->setTimeout(100);
    $multi_curl->success(function ($instance) {
      // dd($instance);
      usleep(2000);
      echo 'call to "' . $instance->url . '" was successful.' . "\n";
      $arr_url=explode("/",$instance->url);
      $parse = new App\Http\Controllers\ParseController();
      if ($instance->httpStatusCode == 200) {
        $crawler = new Crawler($instance->response, $instance->url);
        try {
	         $days[0] = $parse->getTimettableForWeekGroup(1, $crawler);
        } catch (\InvalidArgumentException $e) {
          $days[0] = null;
        }
        try {
          $days[1] = $parse->getTimettableForWeekGroup(2, $crawler);
        } catch (\InvalidArgumentException $e) {
          $days[1] = null;
        }
        $timetable = [
          'group' => (int) $arr_url[count($arr_url) - 1],
	        'timetable' => $days
        ];
        $countDay = 1;
        $countWeek = 1;
        if ($timetable['timetable'])
        foreach ($timetable['timetable'] as $week) {
          // var_dump($week);
          if (!is_null($week)) {
              foreach ($week as $day) {
                foreach ($day->lessons as $lesson) {
                  if(!is_array($lesson)) {
                    $teacher_id = explode('/', $lesson->teacherlink);
                    $teacher_id = $teacher_id[count($teacher_id) - 1];
                    $dbLesson = new App\Lesson();
                    $dbLesson->name = $lesson->name;
                    $dbLesson->group_id = (int) $arr_url[count($arr_url) - 1];
                    $dbLesson->teacher_id = $teacher_id;
                    $dbLesson->audience = $lesson->audience;
                    $dbLesson->time = [
                      'end' => $lesson->time[1],
                      'start' => $lesson->time[0]
                    ];
                    $dbLesson->type = $lesson->type;
                    $dbLesson->week = $countWeek;

                    $dbLesson->day = [
                      'name' => $day->nameDay,
                      'index' => $day->index
                    ];
                    if (isset($lesson->subGroup)) {
                      $dbLesson->subgroup = $lesson->subGroup;
                    }
                    $dbLesson->prefLesson_id = 0;
                    $dbLesson->lesson_image_id = 1;
                    $dbLesson->save();
                  } else {
                    for ($i = 0; $i < count($lesson); $i++) {
                      $teacher_id = explode('/', $lesson[$i]->teacherlink);
                      $teacher_id = $teacher_id[count($teacher_id) - 1];
                      $dbLesson = new App\Lesson();
                      $dbLesson->name = $lesson[$i]->name;
                      $dbLesson->group_id = (int) $arr_url[count($arr_url) - 1];
                      $dbLesson->teacher_id = $teacher_id;
                      $dbLesson->audience = $lesson[$i]->audience;
                      $dbLesson->time = [
                        'end' => $lesson[$i]->time[1],
                        'start' => $lesson[$i]->time[0]
                      ];
                      $dbLesson->type = $lesson[$i]->type;
                      $dbLesson->week = $countWeek;

                      $dbLesson->day = [
                        'name' => $day->nameDay,
                        'index' => $day->index
                      ];
                      if (isset($lesson[$i]->subGroup)) {
                        $dbLesson->subgroup = $lesson[$i]->subGroup;
                      }
                      if ($i > 0) {
                        $dbLesson->prefLesson_id = $prefId;
                      } else {
                        $dbLesson->prefLesson_id = 0;
                      }
                      $dbLesson->lesson_image_id = 1;
                      $dbLesson->save();
                      if ($i !== count($lesson) - 1) {
                        $prefId = $dbLesson->id;
                      }
                    }
                  }
                }
                $countDay++;
              }
          }
          $countWeek++;
        }
      }

    });
    $multi_curl->error(function ($instance) {
      echo 'call to "' . $instance->url . '" was unsuccessful.' . "\n";
      echo 'error code: ' . $instance->errorCode . "\n";
      echo 'error message: ' . $instance->errorMessage . "\n";
    });
    $multi_curl->complete(function ($instance) {
      echo 'call to "' . $instance->url . '" completed.' . "\n";
    });
    foreach ($groups as $group) {
      $multi_curl->addGet("https://timetable.pallada.sibsau.ru/timetable/group/".$group->id);
    }

    $multi_curl->start();
    // $mn = curl_multi_init();
    // $hendles = [];
    // foreach ($groups as $group) {
    //   // print("https://timetable.pallada.sibsau.ru/timetable/group/".$group->id);
    //   $ch = curl_init();
    //   $url ="https://timetable.pallada.sibsau.ru/timetable/group/".$group->id;
    //   curl_setopt($ch, CURLOPT_URL, $url);
    //   curl_setopt($ch, CURLOPT_HEADER, false);
    //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //   curl_multi_add_handle($mn,$ch);
    //   $hendles[$url] = $ch;
    // }
    //
    // do {
    //   $mrc = curl_multi_exec($mn, $active);
    // } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    //
    // while($active && $mrc == CURLM_OK) {
    //   if (curl_multi_select($mn) == -1) {
    //     usleep(100);
    //   }
    //   do {
    //     $mrc = curl_multi_exec($mn, $active);
    //   } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    // }
    //
    //
    //
    // $parse = new App\Http\Controllers\ParseController();
    // foreach($hendles as $channel) {
    //   // dd(curl_multi_getcontent($channel));
    //   $html = curl_multi_getcontent($channel);
    //   $crawler = new Crawler($html);
    //   dd($crawler);
    //   var_dump($parse->getTimettableForWeekGroup(1, $crawler));
    //   curl_multi_remove_handle($mn, $channel);
    // }
    // curl_multi_close($mn);
  });
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
