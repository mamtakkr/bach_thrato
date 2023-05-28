<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contactlist extends Model
{
    protected $primarykey="id";
    protected $table = 'contactlists';
    protected $fillable=['id','user_id','first_name','last_name','contact','is_registered','created_at','updated_at']; 
    public $timestamps=true; 
}
