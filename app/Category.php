<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primarykey="id";
    protected $table = 'categories';
    protected $fillable=['id','title','title_es','title_pt','image_url','is_deleted','created_at','updated_at']; 
    public $timestamps=true; 
}
