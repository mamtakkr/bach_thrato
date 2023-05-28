<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    protected $primarykey="id";
    protected $table = 'favourites';
    protected $fillable=['id','store_id','user_id','created_at','updated_at']; 
    public $timestamps=true; 
}
