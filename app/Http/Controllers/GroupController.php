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
		//
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
		$gr = $group;
		$group = mb_strtoupper(urldecode($group));
		$html = file_get_contents('https://timetable.pallada.sibsau.ru/timetable/group/2018/2/'.$gr);
		$crawler = new Crawler(null, 'https://timetable.pallada.sibsau.ru/timetable/group/2018/2/'.$gr);
		$crawler->addHtmlContent($html, 'UTF-8');
		$days = array();
		$days[0] = $this->getTimettableForWeek(1, $crawler);
		$days[1] = $this->getTimettableForWeek(2, $crawler);
		// dd([
		// 		'group' => $group,
		// 		'timetable' => $days
		// 	]);
		return  response()
			->json([
				'group' => $group,
				'timetable' => $days
			])
			->header('Content-Type', 'application/json')
			->header('charset', 'utf-8');
	}
}
