<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\RedirectResponse;
use App\Teacher;
// VALIDATION: change the requests to match your own file names if you need form validation

class TeachersCrudController extends CrudController
{
  public function setup() {
    /*
    |--------------------------------------------------------------------------
    | CrudPanel Basic Information
    |--------------------------------------------------------------------------
    */
    $this->crud->setModel('App\Teacher');
    $this->crud->setRoute(config('backpack.base.route_prefix') . '/teachers');
    $this->crud->setEntityNameStrings('teacher', 'teachers');

    /*
    |--------------------------------------------------------------------------
    | CrudPanel Configuration
    |--------------------------------------------------------------------------
    */
    // Columns
    $this->crud->addColumn(['name' => 'id', 'type' => 'number', 'label' => 'ID']);
    $this->crud->addColumn(['name' => 'initials_name', 'type' => 'text', 'label' => 'Name']);
    $this->crud->addButtonFromView('top', 'Обновить список преподавателей', 'moderate', 'beginning');
    $this->crud->removeButton('create');
    $this->crud->disableDetailsRow();
    $this->crud->enableResponsiveTable();
    $this->crud->denyAccess(['create', 'delete', 'update']);
    // $this->crud->addButton('top','moderate', 'view', 'moderate', 'beginning');
    // TODO: remove setFromDb() and manually define Fields and Columns
    // $this->crud->setFromDb();
  }

  public function moderate() {
    if (file_exists(public_path("uploads/Teachers.txt"))) {
      $teachers = Teacher::get();
      if (count($teachers) > 0) {
        $teachers->each->delete();
      }
      $file = fopen(public_path("uploads/Teachers.txt"), "r");
      while(!feof($file)) {
        try {
          $line = fgets($file);
          $teacher = new Teacher();
          $line = explode(" - ", $line);
          $teacher->id = $line[0];
          $teacher->initials_name = trim($line[1]);
          $teacher->full_name = trim($line[1]);
          $teacher->save();
        }
        catch (\Illuminate\Database\QueryException $e) {

        }
      }
      \Alert::success('new Teachers save')->flash();
    } else {
      \Alert::error('not found file in "public/uploads/Teachers.txt"')->flash();
    }
    return redirect(backpack_url('teachers'));
  }
}
