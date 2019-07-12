<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Group extends Model {
  use CrudTrait;

  protected $table = 'groups';
  protected $primaryKey = 'id';
  protected $guarded = ['id'];
  protected $fillable = ['id', 'name'];

  protected $dates = [];

  public static $rules = [
    // Validation rules
  ];

  // Relationships

}
