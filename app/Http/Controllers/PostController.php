<?php

namespace App\Http\Controllers;
use App\News;

class PostController extends Controller {
  public function __construct() {

  }
  public function all() {
    return  response()
      ->json(
        News::all()->with('user')->toArray()
      )
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }
  public function post($id) {
    // dd(News::where('id', $id)->with('user')->first()->toArray());
    return  response()
      ->json(
        News::where('id', $id)->with('user')->first()->toArray()
      )
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }
}
