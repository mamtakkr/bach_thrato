<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primarykey="id";
    protected $table = 'products';
    protected $fillable=['id','store_id','sub_admin_id','merchant_id','image_url','title','price','quantity','code','description','is_deleted','created_at','updated_at']; 
    public $timestamps=true;
}
