<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Teacher extends Model {
  use CrudTrait;
  protected $table = 'teachers';
  protected $primaryKey = 'id';
  protected $guarded = ['id'];
  protected $fillable = ['id', 'initials_name'];

  protected $dates = [];

  public static $rules = [
    // Validation rules
  ];

    // Relationships

}
