<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primarykey="id";
    protected $table = 'notifications';
    protected $fillable=['id','user_ids','merchant_ids','sub_admin_id','title','notification_text','created_at','updated_at']; 
    public $timestamps=true;
}
