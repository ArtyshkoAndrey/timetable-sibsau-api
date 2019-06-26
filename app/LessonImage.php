<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonImage extends Model {

    protected $table = 'lesson_images';

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships

}
