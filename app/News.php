<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
  use CrudTrait;
  // use SoftDeletes;
  protected $table = 'news';
  protected $primaryKey = 'id';
  // protected $guarded = ['id'];
  protected $fillable = ['title','summary','body','avatar', 'user_id'];
  public function setAvatarAttribute($value) {
    // dd(123);
    $attribute_name = "avatar";
    $disk = 'uploads'; // or use your own disk, defined in config/filesystems.php
    $destination_path = "/images"; // path relative to the disk above

  // if the image was erased
    if ($value==null) {
      // delete the image from disk
      \Storage::disk($disk)->delete($this->{$attribute_name});

      // set null in the database column
      $this->attributes[$attribute_name] = null;
    }

    // if a base64 was sent, store it in the db
    if (starts_with($value, 'data:image'))
    {
      // 0. Make the image
      $image = \Image::make($value)->encode('jpg', 90);
      // 1. Generate a filename.
      $filename = md5($value.time()).'.jpg';
      // 2. Store the image on disk.
      \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());
      // 3. Save the public path to the database
      // but first, remove "public/" from the path, since we're pointing to it from the root folder
      // that way, what gets saved in the database is the user-accesible URL
      $public_destination_path = Str::replaceFirst('public/', '', $destination_path);
      $this->attributes[$attribute_name] = $public_destination_path.'/'.$filename;
    }
  }
}
