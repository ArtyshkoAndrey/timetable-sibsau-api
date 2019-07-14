<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;
class Lesson extends Model {
  use CrudTrait;
  protected $table = 'lessons';
  protected $fillable = [];

  protected $dates = [];

  public static $rules = [
      // Validation rules
  ];
  protected $casts = [
    'id' => 'int',
    'day' => 'array',
    'time' => 'array'
  ];

  public function image()
  {
    return $this->hasOne('App\LessonImage', 'id', 'lesson_image_id');
  }
  public function teacher () {
    return $this->hasOne('App\Teacher', 'id', 'teacher_id');
  }
  public function group () {
    return $this->hasOne('App\Group', 'id', 'group_id');
  }
}
