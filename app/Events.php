<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Events extends Model {
  use CrudTrait;

  protected $table = 'events';
  protected $primaryKey = 'id';
  protected $guarded = ['id'];
  // protected $fillable = ['id', 'name'];

  protected $dates = [];

  public static $rules = [
    // Validation rules
  ];

  // Relationships

}
