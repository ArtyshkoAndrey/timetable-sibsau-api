<?php

namespace App\Http\Controllers;

use Symfony\Component\DomCrawler\Crawler;

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
    // dump($this->arrayDays['Понедельник']);
  }

  private function getTimettableForExam($crawler) {
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

  private function getTimettableForWeek($num, $crawler) {
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
              $days[$count]->lessons[$i]->audience = $node->filter('li')->last()->text();
              if (str_split($days[$count]->lessons[$i]->audience, 1)[1] == 'подгруппа') {
                $days[$count]->lessons[$i]->subGroup = $days[$count]->lessons[$i]->audience
                $days[$count]->lessons[$i]->audience = $node->filter('li')->eq(2)->text();
              }
            } else if (count($node->filter('.row')->children()) == 2) {
            $days[$count]->lessons[$i] = array();
            $days[$count]->lessons[$i][0] = (object) array();
            $days[$count]->lessons[$i][1] = (object) array();
            $days[$count]->lessons[$i]['time'] = preg_split('/[\-]/', trim(str_replace("\r", "", str_replace("\n", "", $node->filter('.time.text-center')->children()->first()->text()))));
            for ($j = 0; $j < 2; $j++) {
                // dd($days[$count]->lessons[$i]->time);
              $days[$count]->lessons[$i][$j]->name = $node->filter('.list-unstyled')->eq($j)->filter('span')->text();
              $days[$count]->lessons[$i][$j]->subGroup = $node->filter('.list-unstyled')->eq($j)->filter('li')->first()->text();
              $days[$count]->lessons[$i][$j]->type = preg_split('/[\()]/', $node->filter('.list-unstyled')->eq($j)->filter('li')->eq(1)->text())[1];
              $days[$count]->lessons[$i][$j]->teacher = $node->filter('.list-unstyled')->eq($j)->filter('li')->eq(2)->text();
              $days[$count]->lessons[$i][$j]->audience = $node->filter('.list-unstyled')->eq($j)->filter('li')->last()->text();
            }
          }
          });
      });
    return $days;
  }

  public function show($group) {
    // $gr = $group;
    $group = mb_strtoupper(urldecode($group));
    $gr = rawurlencode($group);
    $html = file_get_contents('https://timetable.pallada.sibsau.ru/timetable/group/2018/2/'.$gr);
    $crawler = new Crawler(null, 'https://timetable.pallada.sibsau.ru/timetable/group/2018/2/'.$gr);
    $crawler->addHtmlContent($html, 'UTF-8');
    $days = array();
    try {
      $days[0] = $this->getTimettableForWeek(1, $crawler);
    } catch (\InvalidArgumentException $e) {
      $days[0] = null;
    }
    try {
      $days[1] = $this->getTimettableForWeek(2, $crawler);
    } catch (\InvalidArgumentException $e) {
      $days[1] = null;
    }
    try {
      $exams = $this->getTimettableForExam($crawler);
    } catch (\InvalidArgumentException $e) {
      $exams = null;
    }
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
        'group' => $group,
        'timetable' => $days,
        'exams' => $exams
      ])
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }
}
