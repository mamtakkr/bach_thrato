<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $primarykey="id";
    protected $table = 'addresses';
    protected $fillable=['id','user_id','address_line1','address_line2','landmark','city','state','country','location_lat','location_long','created_at','updated_at']; 
    public $timestamps=true; 
}
