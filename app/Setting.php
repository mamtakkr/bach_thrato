<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primarykey="id";
    protected $table = 'settings';
    protected $fillable=['id','near_by_store_distance','site_title','num_of_free_products','support_email','contact','address1','address2','term_condition','created_at','updated_at']; 
    public $timestamps=true; 
}
