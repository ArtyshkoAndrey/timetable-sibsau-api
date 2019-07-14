<?php

namespace App\Http\Controllers;

use Symfony\Component\DomCrawler\Crawler;
use App\Ip;
use App\Lesson;
use App\Group;

class GroupController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->arrayDays = array();
    $this->arrayDays = [
      'Понедельник' => 1,
      'Вторник'     => 2,
      'Среда'       => 3,
      'Четверг'     => 4,
      'Пятница'     => 5,
      'Суббота'     => 6,
    ];
    $ipAddress = '';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ('' !== trim($_SERVER['HTTP_X_FORWARDED_FOR']))) {
      $ipAddress = trim($_SERVER['HTTP_X_FORWARDED_FOR']);
    } else {
      if (isset($_SERVER['REMOTE_ADDR']) && ('' !== trim($_SERVER['REMOTE_ADDR']))) {
        $ipAddress = trim($_SERVER['REMOTE_ADDR']);
      }
    }
    // dd($ipAddress);
    try {
      $ip = new Ip();
      $ip->ip = $ipAddress;
      $ip->save();
    } catch(\Illuminate\Database\QueryException $e) {

    }
  }

  public function show($group_id) {
    $lessons = Lesson::where('group_id', '=', $group_id)->get();
    // dd($lessons->where('week', '=', '1'));
    $days = array();
    for ($j = 0; $j < 2; $j++) {
      $days[$j] = array();
      $lessonsFirstWeek = $lessons->where('week', '=', $j+1);
      // dd($lessonsFirstWeek);
      foreach ($this->arrayDays as $key => $value) {
        $day = (object) array();
        $day->index = $value;
        $day->name = $key;
        $day->lessons = array();
        $lessonsKeyDay = $lessonsFirstWeek->where('day', ['index' => $value, 'name' => $key]);
        $lessonsKeyDay->sortBy(function ($lesson, $key) {
          return (int) $lesson->time['start'];
        });
        // dd($lessonsKeyDay->toArray());
        $lessonsKeyDay = $lessonsKeyDay->values();
        for ($i = 0; $i < count($lessonsKeyDay); $i++) {
          if (isset($lessonsKeyDay[$i + 1])) {
            if ($lessonsKeyDay[$i + 1]->prefLesson_id !== 0) {
              $lessonsWithPref = array();
              $lesson = (object) array();
              $lesson = $lessonsKeyDay[$i]->toArray();
              $lesson['group'] = $lessonsKeyDay[$i]->group->name;
              $lesson['teacher'] = $lessonsKeyDay[$i]->teacher->initials_name;
              array_push($lessonsWithPref, $lesson);
              $lesson = (object) array();
              $lesson = $lessonsKeyDay[$i + 1]->toArray();
              $lesson['group'] = $lessonsKeyDay[$i + 1]->group->name;
              $lesson['teacher'] = $lessonsKeyDay[$i + 1]->teacher->initials_name;
              array_push($lessonsWithPref, $lesson);
              array_push($day->lessons, $lessonsWithPref);
              $i++;
            } else {
              $lesson = (object) array();
              $lesson = $lessonsKeyDay[$i]->toArray();
              $lesson['group'] = $lessonsKeyDay[$i]->group->name;
              $lesson['teacher'] = $lessonsKeyDay[$i]->teacher->initials_name;
              array_push($day->lessons, $lesson);
            }
          } else {
            $lesson = (object) array();
            $lesson = $lessonsKeyDay[$i]->toArray();
            $lesson['group'] = $lessonsKeyDay[$i]->group->name;
            $lesson['teacher'] = $lessonsKeyDay[$i]->teacher->initials_name;
            array_push($day->lessons, $lesson);
          }
        }
        array_push($days[$j], $day);
      }
    }
    // dd($days);
    $exams = null;
    // dd([
    //    'group' => $group,
    //    'timetable' => $days
    //  ]);
    // dd([
    //     'group' => $group,
    //     'timetable' => $days,
    //     'exams' => $exams
    //   ]);
    return  response()
      ->json([
        'group' => Group::where('id', $group_id)->first()->name,
        'timetable' => $days,
        'exams' => $exams
      ])
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }

  public function searchGroup ($text) {
    // dd('%'.$text.'%');
    $groups = Group::where('name', 'LIKE','%'.$text.'%')->get();
    return  response()
      ->json([
        'groups' => $groups->toArray(),
      ])
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }
}
