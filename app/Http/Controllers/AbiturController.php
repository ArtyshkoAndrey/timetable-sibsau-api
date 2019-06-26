<?php

namespace App\Http\Controllers;

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;

class AbiturController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    
  }

  public function show() {
    $client = new Client();
    $crawler = $client->request('GET', 'https://timetable.pallada.sibsau.ru/');

    $form = $crawler->selectButton('search_btn')->form();
    $form['query'] = 'Чмых';
    // dd($form);
    // $crawler = $client->submit($form);
    dd(count($crawler->filter('ul .typeahead > li')));
    dd($crawler);
    dd($crawler);
  }
}