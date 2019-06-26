<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Ip extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
      'ip' => 'required|unique',
    ];

    // Relationships

}
