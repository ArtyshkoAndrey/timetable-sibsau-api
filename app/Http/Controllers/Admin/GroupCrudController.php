<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\RedirectResponse;
use App\Group;
// VALIDATION: change the requests to match your own file names if you need form validation

class GroupCrudController extends CrudController
{
  public function setup() {
    /*
    |--------------------------------------------------------------------------
    | CrudPanel Basic Information
    |--------------------------------------------------------------------------
    */
    $this->crud->setModel('App\Group');
    $this->crud->setRoute(config('backpack.base.route_prefix') . '/group');
    $this->crud->setEntityNameStrings('group', 'groups');

    /*
    |--------------------------------------------------------------------------
    | CrudPanel Configuration
    |--------------------------------------------------------------------------
    */
    // Columns
    $this->crud->addColumn(['name' => 'id', 'type' => 'number', 'label' => 'ID']);
    $this->crud->addColumn(['name' => 'name', 'type' => 'text', 'label' => 'Name']);
    $this->crud->addButtonFromView('top', 'moderate', 'moderate', 'beginning');
    $this->crud->removeButton('create');
    $this->crud->disableDetailsRow();
    $this->crud->enableResponsiveTable();
    $this->crud->denyAccess(['create', 'delete', 'update']);
    // $this->crud->addButton('top','moderate', 'view', 'moderate', 'beginning');
    // TODO: remove setFromDb() and manually define Fields and Columns
    // $this->crud->setFromDb();
  }

  public function moderate() {
    if (file_exists(public_path("storage/Group.txt"))) {
      $groups = Group::get();
      if (count($groups) > 0) {
        $groups->each->delete();
      }
      $file = fopen(public_path("storage/Group.txt"), "r");
      while(!feof($file)) {
        try {
          $line = fgets($file);
          $group = new Group();
          $line = explode(" - ", $line);
          $group->id = $line[0];
          $group->name = trim($line[1]);
          $group->save();
        }
        catch (\Illuminate\Database\QueryException $e) {

        }
      }
      \Alert::success('new Groups save')->flash();
    } else {
      \Alert::error('not found file in "public/storage/Group.txt"')->flash();
    }
    return redirect(backpack_url('group'));
  }
}
