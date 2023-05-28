<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription_payment extends Model
{
    protected $primarykey="id";
    protected $table = 'subscription_payments';
    protected $fillable=['id','merchant_id','subscription_id','transaction_id','payment_gateway','expiry_date','timezone','created_at','updated_at']; 
    public $timestamps=true;
}
