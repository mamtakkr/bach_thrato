<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Setting;
use App\Subscription_payment;
use Auth;
class CommonController extends Controller
{
    //$language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($merchant_id);
    public function get_user_current_language($user_id=NULL){
        if(!empty($user_id)){
            return User::find($user_id)->current_language_code;
        }
    }
    
    //$no_of_stores=app('App\Http\Controllers\CommonController')->get_user_no_of_stores($merchant_id);
    public function get_user_no_of_stores($user_id=NULL){
        if(!empty($user_id)){
            return User::find($user_id)->no_of_stores;
        }
        return Setting::find(1)->num_of_free_stores;
    }
        
    //$no_of_products=app('App\Http\Controllers\CommonController')->get_user_no_of_products($merchant_id);
    public function get_user_no_of_products($user_id=NULL){
        if(!empty($user_id)){
            return User::find($user_id)->no_of_products;
        }
        return Setting::find(1)->num_of_free_products;
    }
}