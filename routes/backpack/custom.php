<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
  'prefix'     => config('backpack.base.route_prefix', 'admin'),
  'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
  'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
  Route::get('/groups/moderate', 'GroupsCrudController@moderate');
  CRUD::resource('groups', 'GroupsCrudController');
  Route::get('/teachers/moderate', 'TeachersCrudController@moderate');
  CRUD::resource('teachers', 'TeachersCrudController');
  Route::get('/lessons/moderate', 'LessonsCrudController@moderate');
  CRUD::resource('lessons', 'LessonsCrudController');
}); // this should be the absolute last line of this file
