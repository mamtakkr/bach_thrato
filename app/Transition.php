<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transition extends Model
{
    protected $primarykey="id";
    protected $table = 'transitions';
    protected $fillable=['id','title','image_url','description','type','is_deleted','created_at','updated_at']; 
    public $timestamps=true;
}
