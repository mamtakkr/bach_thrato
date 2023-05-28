<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    protected $primarykey="id";
    protected $table = 'advertisements';
    protected $fillable=['id','sub_admin_id','title','image_url','link','button_link','location','description','expiry_date','is_deleted','status','created_at','updated_at']; 
    public $timestamps=true; 
}
