<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $primarykey="id";
    protected $table = 'shipping_address';
    protected $fillable=['id','user_id','address_line1','address_line2','country','state','city','landmark','contact','created_at','updated_at']; 
    public $timestamps=true; 
}
