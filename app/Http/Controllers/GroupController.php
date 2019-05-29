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

    public function show($group) {
        $html = file_get_contents('https://timetable.pallada.sibsau.ru/timetable/group/2018/2/'.$group);
        $crawler = new Crawler(null, 'https://timetable.pallada.sibsau.ru/timetable/group/2018/2/'.$group);
        $crawler->addHtmlContent($html, 'UTF-8');
        global $days, $count;
        $days = array();
        $count = 0;
        $crawler->filter('#week_1_tab > div')
            ->reduce(function (Crawler $node, $i) { // Дни
                global $days, $count;
                $count = $i;
                $days[$i] = (object) array();
                $days[$i]->nameDay = trim($node->filter('.name.text-center > div')->text());
                $days[$i]->lentas = array();
                $node->filter('.body > .line')
                    ->reduce(function (Crawler $node, $i) { // Ленты
                        global $days, $count;
                        $days[$count]->lentas[$i] = (object) array();
                        // $days[$count]->lentas[$i]->name = $node->filter('span')->text();
                        if (count($node->filter('.row')->children()) == 1) {
                            $days[$count]->lentas[$i] = (object) array();
                            $days[$count]->lentas[$i]->name = $node->filter('span')->text();
                        } else if (count($node->filter('.row')->children()) == 2) {
                            $days[$count]->lentas[$i] = array();
                            $days[$count]->lentas[$i][0] = (object) array();
                            $days[$count]->lentas[$i][1] = (object) array();
                            // dd($node->filter('span')->last()->text());
                            $days[$count]->lentas[$i][0]->name = $node->filter('span')->first()->text();
                            $days[$count]->lentas[$i][1]->name = $node->filter('span')->first()->text();
                        }
                    });
            });
        // dd($days);        
        $group = urldecode($group);
        return  response()
            ->json([
                'group' => $group,
                'timetable' => $days
            ])
            ->header('Content-Type', 'application/json')
            ->header('charset', 'utf-8');
    }
}
