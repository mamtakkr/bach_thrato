<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $primarykey="id";
    protected $table = 'sliders';
    protected $fillable=['id','title','image_url','description','created_at','updated_at']; 
    public $timestamps=true; 
}
