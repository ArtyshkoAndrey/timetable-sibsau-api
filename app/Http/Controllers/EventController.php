<?php

namespace App\Http\Controllers;
use App\Events;

class EventController extends Controller {
  public function __construct() {

  }
  public function all() {
    return  response()
      ->json(
        Events::orderBy('created_at', 'desc')->get()->toArray()
      )
      ->header('Content-Type', 'application/json')
      ->header('charset', 'utf-8');
  }
  // public function post($id) {
  //   // dd(News::where('id', $id)->with('user')->first()->toArray());
  //   return  response()
  //     ->json(
  //       News::where('id', $id)->first()->toArray()
  //     )
  //     ->header('Content-Type', 'application/json')
  //     ->header('charset', 'utf-8');
  // }
}
