<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $primarykey="id";
    protected $table = 'stores';
    protected $fillable=['id','merchant_id','cat_id','title','location','timings','location_lat','location_long','image_url','is_deleted','created_at','updated_at']; 
    public $timestamps=true; 
}
