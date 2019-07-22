<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\NewsRequest as StoreRequest;
use App\Http\Requests\NewsRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;
use App\User;

/**
 * Class NewsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class NewsCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\News');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/news');
        $this->crud->setEntityNameStrings('news', 'news');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */
        $this->crud->addField([ // image
          'label' => "Avatar Image",
          'name' => "avatar",
          'type' => 'image',
          'upload' => true,
          'disk' => 'uploads'
        ],'both');
        $this->crud->addField([
          'label' => "User",
           'type' => 'select2',
           'name' => 'user_id', // the db column for the foreign key
           'entity' => 'user', // the method that defines the relationship in your Model
           'attribute' => 'name', // foreign key attribute that is shown to user
           'model' => "App\Models\BackpackUser" // foreign key model
         ]);

        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->setFromDb();

        // add asterisk for fields that are required in NewsRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function store(StoreRequest $request)
    {
        // User::where('id', $request->user_id)->first();
        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        $msg = array (
          "title" =>"Новая запись",
        );
        $fields = array (
          "to" => "/topics/allDevice",
          'data'  => $msg,
          "notification" =>array("title" => "Новая запись", "body" => "Добавлена новая запись от " . User::where('id', $request->user_id)->first()->name, "sound" => "default")
        );

        $headers = array (
          'Authorization: key=' . 'AAAAB42lrsY:APA91bE8gsm3g16tb3jAGMxZTIpvIfVRQzC3aYYut4RUDrfd7wmlPtCIg7vORB1O6ssQjYzMYOaynvxRY9xjbR5huG-g2LlIDOBv3bQQBD-QzZ-zDhao6A8wzIDjQ0JNunscC5GKqEcS',
          'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        // your additional operations after save here
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
