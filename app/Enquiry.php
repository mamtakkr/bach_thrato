<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $primarykey="id";
    protected $table = 'enquiries';
    protected $fillable=['id','user_id','email','description','created_at','updated_at']; 
    public $timestamps=true; 
}
