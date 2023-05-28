<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primarykey="id";
    protected $table = 'orders';
    protected $fillable=['id','user_id','shipping_address_id','store_id','merchant_id','amount','status','payment_mode']; 
    public $timestamps=true;
}
