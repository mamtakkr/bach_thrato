<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DefaultNotification extends Model
{
    protected $primarykey="id";
    protected $table = 'default_notifications';
    protected $fillable=['id','key','en','es','pt']; 
    public $timestamps=false;
}
