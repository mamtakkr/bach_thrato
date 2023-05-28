<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdvertisementExpiry extends Model
{
    protected $primarykey="id";
    protected $table = 'advertisement_expiry';
    protected $fillable=['id','title','num_of_days','created_at','updated_at']; 
    public $timestamps=true; 
}
