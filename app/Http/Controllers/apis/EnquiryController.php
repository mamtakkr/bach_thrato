<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Enquiry;
class EnquiryController extends Controller
{


    public function add_enquiry(Request $request)
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
        if(empty($request->get('user_id')) || empty($request->get('email')) || empty($request->get('description'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
    	    $enquiry=new Enquiry([
        		'user_id'=>$request->get('user_id'),
        		'email'=>$request->get('email'),
        		'description'=>$request->get('description'),
        		'created_at'=>date('Y-m-d')
    	    ]);
            $enquiry->save();
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('enquiry_added',$request->get('user_id'));
                $response=['responseCode'=>200,'message'=>$message,'data'=>$enquiry];
                return $response;
        }
    }

    
    public function show_enquiry(Request $request)
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
        
            $enquiry=Enquiry::get();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('enquiries_found');
            $response=['responseCode'=>200,'message'=>$message,'data'=>$enquiry];
            return $response;
    }
}