<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ShippingAddress;
use Image;

class ShippingAddressController extends Controller
{
   
    public function shipping_address_store(Request $request){
        
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
        
        if(empty($request->get('user_id')) || empty($request->get('address_line1')) || empty($request->get('address_line2')) || 
        empty($request->get('country')) || empty($request->get('state')) || empty($request->get('city')) || 
        empty($request->get('landmark')) || empty($request->get('contact'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
       
        else{
    	    $shipping_address=new ShippingAddress([
        		'user_id'=>$request->get('user_id'),
        		'address_line1'=>$request->get('address_line1'),
        		'address_line2'=>$request->get('address_line2'),
        		'country'=>$request->get('country'),
        		'state'=>$request->get('state'),
        		'city'=>$request->get('city'),
        		'landmark'=>$request->get('landmark'),
        		'contact'=>$request->get('contact'),
        		'created_at'=>date('Y-m-d'),
    	    ]);
        
            $shipping_address->save();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('shippingaddress_created',$request->get('user_id'));    
            $response=['responseCode'=>200,'message'=>$message,'shipping_address'=>$shipping_address];
            return $response;
        }
    }
    
    
    public function shipping_address_show(Request $request){
        
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
            $shipping_address=ShippingAddress::where('user_id',$request->get('user_id'))->get();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('shippingaddress_found',$request->get('user_id'));  
            $response=['responseCode'=>200,'message'=>$message,'shipping_address'=>$shipping_address];
            return $response;
        }
    }
    
}