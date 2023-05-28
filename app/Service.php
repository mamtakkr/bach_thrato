<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Service extends Model
{
    protected $primarykey="id";
    protected $table = 'services';
    protected $fillable=['id','title','heading','price','body','image_url','created_at','updated_at']; 
    public $timestamps=true; 

    // public function user(){
    //     return $this->belongsTo(User::class);
    // }

}