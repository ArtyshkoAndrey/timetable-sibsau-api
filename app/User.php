<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $appends = array('avatar');
    // protected $with = ['avatar'];

    public function getAvatarAttribute() {
      return 'https://s.gravatar.com/avatar/'. md5($this->email) .'?s=80';
    }

    public static function boot()
    {
      parent::boot();
      static::updating(function($model)
      {
        $model->avatar = 'https://s.gravatar.com/avatar/'. md5($model->email) .'?s=80';
      });
    }
}
