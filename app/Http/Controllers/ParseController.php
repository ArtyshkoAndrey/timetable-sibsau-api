<?php

namespace App\Http\Controllers;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Lesson;
use App\Ip;
use App\Group;
use Illuminate\Support\Facades\Storage;

class ParseController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
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
    $this->arrayDays = array();
    $this->arrayDays = [
      'Понедельник' => 1,
      'Вторник'     => 2,
      'Среда'       => 3,
      'Четверг'     => 4,
      'Пятница'     => 5,
      'Суббота'     => 6,
    ];
  }

  public function setGroupFromFile () {
    $file = fopen(storage_path("app/public/Group.txt"), "r");
    while(!feof($file)) {
      echo fgets($file). "<br>";
    }
    fclose($file);
    dd($file);
  }

  private function getTimettableForExamGroup($crawler) {
    global $exams;
    $exams = array();
    $crawler->filter('#session_tab> div')
      ->reduce(function (Crawler $node, $i) { // Дни экзамена
        global $exams;
        $exams[$i] = (object) array();
        $exams[$i]->date = trim($node->filter('.name.text-center > div')->text()); // ЧИсло
        $exams[$i]->date = strtok(str_replace("\r", "", str_replace("\n", "", $exams[$i]->date)), " ");
        $exams[$i]->time = trim(str_replace("\r", "", str_replace("\n", "", $node->filter('.body > .line > .time.text-center')->text()))); // Время начало экзамена
        $exams[$i]->name = $node->filter('li')->first()->filter('span')->text(); // Наименование
        $exams[$i]->teacher = $node->filter('li')->eq(1)->text(); // Препод
        $exams[$i]->audience = $node->filter('li')->last()->text();
      });
    return $exams;
  }

  private function getTimettableForWeekGroup($num, $crawler) {
    global $days, $count;
    $days = array();
    $count = 0;
    $crawler->filter('#week_'.$num.'_tab > div')
      ->reduce(function (Crawler $node, $i) { // Дни
        global $days, $count;
        $count = $i;
        $days[$i] = (object) array();
        $days[$i]->nameDay = trim($node->filter('.name.text-center > div')->text());
        $days[$i]->nameDay = strtok(str_replace("\r", "", str_replace("\n", "", $days[$i]->nameDay)), " ");
        $days[$i]->index = $this->arrayDays[$days[$i]->nameDay];
        $days[$i]->lessons = array();
        $node->filter('.body > .line')
          ->reduce(function (Crawler $node, $i) { // Ленты
            global $days, $count;
            if (count($node->filter('.row')->children()) == 1) {
              $days[$count]->lessons[$i] = (object) array();
              $days[$count]->lessons[$i]->time = preg_split('/[\-]/', trim(str_replace("\r", "", str_replace("\n", "", $node->filter('.time.text-center')->children()->first()->text()))));
              $days[$count]->lessons[$i]->name = $node->filter('li')->first()->filter('span')->text();
              $days[$count]->lessons[$i]->type = preg_split('/[\()]/', $node->filter('li')->first()->text())[1];
              $days[$count]->lessons[$i]->teacher = $node->filter('li')->eq(1)->text();
              $days[$count]->lessons[$i]->teacherlink = $node->filter('li')->eq(1)->filter('a')->link()->getUri();
              $days[$count]->lessons[$i]->audience = $node->filter('li')->last()->text();
              if (count(explode(' ', $days[$count]->lessons[$i]->audience)) > 1) {
                if (explode(' ', $days[$count]->lessons[$i]->audience)[1] === 'подгруппа') {
                  $days[$count]->lessons[$i]->subGroup = $days[$count]->lessons[$i]->audience;
                  $days[$count]->lessons[$i]->audience = $node->filter('li')->eq(2)->text();
                }
              }
            } else if (count($node->filter('.row')->children()) == 2) {
            $days[$count]->lessons[$i] = array();
            $days[$count]->lessons[$i][0] = (object) array();
            $days[$count]->lessons[$i][1] = (object) array();
            for ($j = 0; $j < 2; $j++) {
              $days[$count]->lessons[$i][$j]->name = $node->filter('.list-unstyled')->eq($j)->filter('span')->text();
              $days[$count]->lessons[$i][$j]->subGroup = $node->filter('.list-unstyled')->eq($j)->filter('li')->first()->text();
              $days[$count]->lessons[$i][$j]->type = preg_split('/[\()]/', $node->filter('.list-unstyled')->eq($j)->filter('li')->eq(1)->text())[1];
              $days[$count]->lessons[$i][$j]->teacher = $node->filter('.list-unstyled')->eq($j)->filter('li')->eq(2)->text();
              $days[$count]->lessons[$i][$j]->teacherlink = $node->filter('.list-unstyled')->eq($j)->filter('li')->eq(2)->filter('a')->link()->getUri();
              $days[$count]->lessons[$i][$j]->audience = $node->filter('.list-unstyled')->eq($j)->filter('li')->last()->text();
              $days[$count]->lessons[$i][$j]->time = preg_split('/[\-]/', trim(str_replace("\r", "", str_replace("\n", "", $node->filter('.time.text-center')->children()->first()->text()))));
            }
          }
          });
      });
    return $days;
  }

  public function group($group_id) {
    // $group = Group::where('id',  '=', $group_id )->first();
    $html = file_get_contents('https://timetable.pallada.sibsau.ru/timetable/group/'.$group_id);
    $crawler = new Crawler(null, 'https://timetable.pallada.sibsau.ru/timetable/group/'.$group_id);
    $crawler->addHtmlContent($html, 'UTF-8');
    $days = array();
    try {
      $days[0] = $this->getTimettableForWeekGroup(1, $crawler);
    } catch (\InvalidArgumentException $e) {
      $days[0] = null;
    }
    try {
      $days[1] = $this->getTimettableForWeekGroup(2, $crawler);
    } catch (\InvalidArgumentException $e) {
      $days[1] = null;
    }
    try {
      $exams = $this->getTimettableForExamGroup($crawler);
    } catch (\InvalidArgumentException $e) {
      $exams = null;
    }
    return  response()
      ->json([
        'group' => $group_id,
        'timetable' => $days,
        'exams' => $exams
      ])
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }

  private function getTimettableForWeekTeacher($num, $crawler) {
    global $days, $count;
    $days = array();
    $count = 0;
    $crawler->filter('#timetable_tab')->filter('#week_'.$num.'_tab > div')
      ->reduce(function (Crawler $node, $i) { // Дни
        global $days, $count;
        $count = $i;
        $days[$i] = (object) array();
        $days[$i]->nameDay = strtok(str_replace("\r", "", str_replace("\n", "",trim($node->filter('.name.text-center > div')->text()))), " "); // Наименование дня
        $days[$i]->index = $this->arrayDays[$days[$i]->nameDay];
        $days[$i]->lessons = array();
        $node->filter('.body > .line')
          ->reduce(function (Crawler $node, $i) { // Ленты
            global $days, $count;
            $days[$count]->lessons[$i] = (object) array();
            $days[$count]->lessons[$i]->time = preg_split('/[\-]/', trim(str_replace("\r", "", str_replace("\n", "", $node->filter('.time.text-center')->children()->first()->text())))); //Время
            $node = $node->filter('ul > li');
            $days[$count]->lessons[$i]->name = $node->first()->filter('span')->text(); // Наименование
            $days[$count]->lessons[$i]->type = preg_split('/[\()]/', $node->first()->text())[1];
            if (count($node) === 4) {
              $days[$count]->lessons[$i]->type;
              // $node->eq(1)->text(); //Группа 1
              // $node->eq(2)->text(); //Группа 2
            } else {
              // $node->eq(1)->text(); //Группа
            }
            $days[$count]->lessons[$i]->audience = $node->last()->text(); // Кабинет
          });
      });
    return $days;
  }

  public function teacher($teacher_id) {
    $html = file_get_contents('https://timetable.pallada.sibsau.ru/timetable/professor/'.$teacher_id);
    $crawler = new Crawler(null, 'https://timetable.pallada.sibsau.ru/timetable/professor/'.$teacher_id);
    $crawler->addHtmlContent($html, 'UTF-8');
    $day = $this->getTimettableForWeekTeacher(1 ,$crawler);
    return $day;
  }
}
