<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $primarykey="id";
    protected $table = 'order_items';
    protected $fillable=['id','order_id','product_id','store_id','price','quantity','amount','shipping_charge','gst_per','gst']; 
    public $timestamps=true;
}
