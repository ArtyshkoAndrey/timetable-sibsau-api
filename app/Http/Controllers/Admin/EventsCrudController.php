<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\EventsRequest as StoreRequest;
use App\Http\Requests\EventsRequest as UpdateRequest;
use App\Events;
// VALIDATION: change the requests to match your own file names if you need form validation

class EventsCrudController extends CrudController
{
  public function setup() {
    /*
    |--------------------------------------------------------------------------
    | CrudPanel Basic Information
    |--------------------------------------------------------------------------
    */
    $this->crud->setModel('App\Events');
    $this->crud->setRoute(config('backpack.base.route_prefix') . '/events');
    $this->crud->setEntityNameStrings('Event', 'Events');

    /*
    |--------------------------------------------------------------------------
    | CrudPanel Configuration
    |--------------------------------------------------------------------------
    */
    // Columns
    // $this->crud->addColumn(['name' => 'id', 'type' => 'number', 'label' => 'ID']);
    // $this->crud->addColumn(['name' => 'initials_name', 'type' => 'text', 'label' => 'Name']);
    // $this->crud->addButtonFromView('top', 'Обновить список преподавателей', 'moderate', 'beginning');
    // $this->crud->removeButton('create');
    // $this->crud->disableDetailsRow();
    // $this->crud->enableResponsiveTable();
    // $this->crud->denyAccess(['create', 'delete', 'update']);
    // $this->crud->addButton('top','moderate', 'view', 'moderate', 'beginning');
    // TODO: remove setFromDb() and manually define Fields and Columns
    $this->crud->setFromDb();
  }

  public function store(StoreRequest $request)
  {
      $redirect_location = parent::storeCrud($request);
      $msg = array (
        "title" =>"Новое мероприятие",
      );
      $fields = array (
        "to" => "/topics/allDevice",
        'data'  => $msg,
        "notification" =>array("title" => "Новое мероприятие", "body" => "Добавлено новое мероприятие, просмотри что бы не опоздать", "sound" => "default")
      );

      $headers = array (
        'Authorization: key=' . 'AAAAB42lrsY:APA91bE8gsm3g16tb3jAGMxZTIpvIfVRQzC3aYYut4RUDrfd7wmlPtCIg7vORB1O6ssQjYzMYOaynvxRY9xjbR5huG-g2LlIDOBv3bQQBD-QzZ-zDhao6A8wzIDjQ0JNunscC5GKqEcS',
        'Content-Type: application/json'
      );

      $ch = curl_init();
      curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
      curl_setopt( $ch,CURLOPT_POST, true );
      curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
      curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
      curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
      curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
      $result = curl_exec($ch );
      curl_close( $ch );
      // use $this->data['entry'] or $this->crud->entry
      return $redirect_location;
  }

  public function update(UpdateRequest $request)
  {
      // your additional operations before save here
      $redirect_location = parent::updateCrud($request);
      // your additional operations after save here
      // use $this->data['entry'] or $this->crud->entry
      return $redirect_location;
  }
}
