<?php

namespace App\Http\Controllers;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Lesson;

class ParseController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->arrayDays = [
      'Понедельник' => 1,
      'Вторник'     => 2,
      'Среда'       => 3,
      'Четверг'     => 4,
      'Пятница'     => 5,
      'Суббота'     => 6,
    ];
  }

  public function group() {

  }

  // private function getTimettableForWeekTeacher($num, $crawler) {
  //   global $days, $count;
  //   $days = array();
  //   $count = 0;
  //   $crawler->filter('#timetable_tab')->filter('#week_'.$num.'_tab > div')
  //     ->reduce(function (Crawler $node, $i) { // Дни
  //       global $days, $count;
  //       $count = $i;
  //       $days[$i] = (object) array();
  //       $days[$i]->nameDay = strtok(str_replace("\r", "", str_replace("\n", "",trim($node->filter('.name.text-center > div')->text()))), " "); // Наименование дня
  //       $days[$i]->index = $this->arrayDays[$days[$i]->nameDay];
  //       $days[$i]->lessons = array();
  //       $node->filter('.body > .line')
  //         ->reduce(function (Crawler $node, $i) { // Ленты
  //           global $days, $count;
  //           $days[$count]->lessons[$i] = (object) array();
  //           $days[$count]->lessons[$i]->time = preg_split('/[\-]/', trim(str_replace("\r", "", str_replace("\n", "", $node->filter('.time.text-center')->children()->first()->text())))); //Время
  //           $node = $node->filter('ul > li');
  //           $days[$count]->lessons[$i]->name = $node->first()->filter('span')->text(); // Наименование
  //           $days[$count]->lessons[$i]->type = preg_split('/[\()]/', $node->first()->text())[1]
  //           if (count($node) === 4) {
  //             $days[$count]->lessons[$i]->type
  //             // $node->eq(1)->text(); //Группа 1
  //             // $node->eq(2)->text(); //Группа 2
  //           } else {
  //             // $node->eq(1)->text(); //Группа
  //           }
  //           $days[$count]->lessons[$i]->audience = $node->last()->text(); // Кабинет
  //         });
  //     });
  // }

  private function GetGroupfromTeacher($crawler, $t) {
    global $arr;
    $arr = $t;
    // dd($arr);
    if (count($arr) >= 15) {
      return $arr;
    }
    $crawler->filter('#timetable_tab')->filter('#week_1_tab > div')
      ->reduce(function (Crawler $node, $i) { // Дни
        global $arr;
        $node->filter('.body > .line')
          ->reduce(function (Crawler $node, $i) { // Ленты
            global $arr;
            $node = $node->filter('ul > li');
            // dd(gettype($node->eq(1)->filter("a")->link()->getUri()));
            // dd($arr);
            try {
              $html = file_get_contents(str_replace(" ", "%20", $node->eq(1)->filter("a")->link()->getUri()));
              $crawler = new Crawler(null, str_replace(" ", "%20", $node->eq(1)->filter("a")->link()->getUri()));
              $crawler->addHtmlContent($html, 'UTF-8');
              // dd($node->eq(1));
              if (!in_array($node->eq(1)->filter("a")->text(), $arr)) {
                // dd(123);
                array_push($arr, $node->eq(1)->filter("a")->text());
                $arr = $this->GetTeacherfromGroup($crawler, $arr);
              }
            } catch (\InvalidArgumentException $e) {
              try {
                $html = file_get_contents(str_replace(" ", "%20", $node->eq(2)->filter("a")->link()->getUri()));
                $crawler = new Crawler(null, str_replace(" ", "%20", $node->eq(2)->filter("a")->link()->getUri()));
                $crawler->addHtmlContent($html, 'UTF-8');
                // dd($node->eq(2));
                // dd(111);
                if (!in_array($node->eq(2)->filter("a")->text(), $arr)) {
                  // dd('111');
                  array_push($arr, $node->eq(2)->filter("a")->text());
                  $arr = $this->GetTeacherfromGroup($crawler, $arr);
                }
              } catch (\InvalidArgumentException $e) {

              }
            }
          });
      });
    return $arr;
  }

  private function GetTeacherfromGroup($crawler, $t) {
    global $arr;
    $arr = $t;
    // dd($arr);
    $crawler->filter('#week_1_tab > div')
      ->reduce(function (Crawler $node, $i) { // Дни
        global $arr;
        $node->filter('.body > .line')
          ->reduce(function (Crawler $node, $i) { // Ленты
            global $arr;
            $teacher = $node->filter('li')->eq(1)->text();
            $teacherlink = $node->filter('li')->eq(1)->filter('a')->link()->getUri();
            $html = file_get_contents(str_replace(" ", "%20", $teacherlink));
            $crawler = new Crawler(null, str_replace(" ", "%20", $teacherlink));
            $crawler->addHtmlContent($html, 'UTF-8');
            $arr = $this->GetGroupfromTeacher($crawler, $arr);
          });
      });
    return $arr;
  }

  public function teacher($teacher) {
    $html = file_get_contents('https://timetable.pallada.sibsau.ru/timetable/professor/'.$teacher);
    $crawler = new Crawler(null, 'https://timetable.pallada.sibsau.ru/timetable/professor/'.$teacher);
    $crawler->addHtmlContent($html, 'UTF-8');
    $day = $this->GetGroupfromTeacher($crawler, array());
    dd($day);
  }
}
