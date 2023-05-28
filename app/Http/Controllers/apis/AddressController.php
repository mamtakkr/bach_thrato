<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Address;
use App\Contactlist;
use Hash;
use Image;
class AddressController extends Controller
{
   
   public function random_strings($length) 
    { 
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
        return substr(str_shuffle($str_result), 0, $length);
    } 


    public function add_address(Request $request)
    { 
        //checking device token is changed or not
        if(!empty($request->get('device_token')) && !empty($request->get('user_id'))){
            $resp=app('App\Http\Controllers\apis\UserController')->check_device_token($request->get('user_id'),$request->get('device_token'));
            if($resp['responseCode']){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$request->get('user_id'));                     
                $response=['responseCode'=>419,'message'=>$message];
                return $response;
            }
        }
        
        $response=array();
        if(empty($request->get('user_id')) || empty($request->get('location_lat')) || empty($request->get('location_long')) || empty($request->get('address_line1'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
    	    $address=new Address([
        		'user_id'=>$request->get('user_id'),
        		'location_lat'=>$request->get('location_lat'),
        		'location_long'=>$request->get('location_long'),
        		'address_line1'=>$request->get('address_line1'),
        		'address_line2'=>$request->get('address_line2'),
        		'landmark'=>$request->get('landmark'),
        		'created_at'=>date('Y-m-d'),
    	    ]);
            $address->save();
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('address_added',$request->get('user_id'));     
                $response=['responseCode'=>200,'message'=>$message,'address'=>$address];
                return $response;
        }
    }


    public function update_address(Request $request)
    { 
        //checking device token is changed or not
        if(!empty($request->get('device_token')) && !empty($request->get('user_id'))){
            $resp=app('App\Http\Controllers\apis\UserController')->check_device_token($request->get('user_id'),$request->get('device_token'));
            if($resp['responseCode']){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$request->get('user_id'));                     
                $response=['responseCode'=>419,'message'=>$message];
                return $response;
            }
        }
        
        $response=array();
        if(empty($request->get('id')) || empty($request->get('user_id')) || empty($request->get('location_lat')) || empty($request->get('location_long')) || 
           empty($request->get('address_line1'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{    
                $address=Address::where('id',$request->get('id'))
                ->update(['location_lat'=>$request->location_lat,'location_long'=>$request->location_long,
                          'address_line1'=>$request->address_line1,'address_line2'=>$request->address_line2,
                          'landmark'=>$request->landmark]);
                         $address = Address::where('id',$request->get('id'))->get();
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('address_updated',$request->get('user_id')); 
                    $response=['responseCode'=>200,'message'=>$message,'address'=>$address];
                    return $response;
        }
    }
    
    public function address_list(Request $request)
    { 
        //checking device token is changed or not
        if(!empty($request->get('device_token')) && !empty($request->get('user_id'))){
            $resp=app('App\Http\Controllers\apis\UserController')->check_device_token($request->get('user_id'),$request->get('device_token'));
            if($resp['responseCode']){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$request->get('user_id'));                     
                $response=['responseCode'=>419,'message'=>$message];
                return $response;
            }
        }
        
        $response=array();
        if(empty($request->get('user_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{    
                $address=Address::where(['user_id'=>$request->get('user_id'),'is_deleted'=>0])
                ->select(['id','user_id','location_lat','location_long','address_line1','address_line2','landmark'])->get();
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('address_list',$request->get('user_id')); 
                    $response=['responseCode'=>200,'message'=>$message,'address'=>$address];
                    return $response;
        }
    }
    
    public function delete_address(Request $request)
    {
        //checking device token is changed or not
        if(!empty($request->get('device_token')) && !empty($request->get('user_id'))){
            $resp=app('App\Http\Controllers\apis\UserController')->check_device_token($request->get('user_id'),$request->get('device_token'));
            if($resp['responseCode']){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$request->get('user_id'));                     
                $response=['responseCode'=>419,'message'=>$message];
                return $response;
            }
        }
        
        $response=array();
        if(empty($request->get('address_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{    
                $address=Address::where('id',$request->get('address_id'))->update(['is_deleted'=>1]);
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('address_deleted',$request->get('user_id')); 
                    $response=['responseCode'=>200,'message'=>$message];
                    return $response;
        }
    }
         
    
}