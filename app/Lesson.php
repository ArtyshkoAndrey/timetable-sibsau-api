<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model {

  protected $fillable = [];

  protected $dates = [];

  public static $rules = [
      // Validation rules
  ];

  public function image()
  {
    return $this->belongsTo('App\LessonImage', 'lesson_image_id');
  }
  public function teacher () {
    return $this->belongsTo('App\Teacher', 'teacher_id');
  }
  public function group () {
    return $this->belongsTo('App\Group', 'group_id');
  }
}
