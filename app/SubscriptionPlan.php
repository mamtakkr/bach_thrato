<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $primarykey="id";
    protected $table = 'subscription_plans';
    protected $fillable=['id','title','plan_type','no_of_days','no_of_products','no_of_stores','amount','status','description','created_at','updated_at']; 
    public $timestamps=true;
}
