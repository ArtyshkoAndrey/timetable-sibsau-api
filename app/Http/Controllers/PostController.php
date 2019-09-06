<?php

namespace App\Http\Controllers;
use App\News;

class PostController extends Controller {
  public function __construct() {

  }
  public function test() {
    $count = count(News::all());
    $c = rand(1, $count);
    return  response()
      ->json(
        News::take($c)->get()->toArray()
      )
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }
  public function all() {
    return  response()
      ->json(
        News::orderBy('created_at', 'desc')->get()->toArray()
      )
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }
  public function post($id) {
    // dd(News::where('id', $id)->with('user')->first()->toArray());
    return  response()
      ->json(
        News::where('id', $id)->first()->toArray()
      )
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }
}
