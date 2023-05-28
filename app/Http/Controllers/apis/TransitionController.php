<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Transition;
use Image;

class TransitionController extends Controller
{
   
    public function transition_store(Request $request){
        $response=array();
        
        $new_name=null;
        if ($request->hasFile('image_url')) {
            $image=$request->file('image_url');
            $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
            $image = Image::make($image)->resize(300,376);
            $image->save('public/images/transitions/merchant_transitions/'.$new_name);
        }else{
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        
        
        if(empty($request->get('title')) || empty($request->get('description'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
       
        else{
    	    $transitions=new Transition([
        		'title'=>$request->get('title'),
        		'image_url'=>$new_name,
        		'description'=>$request->get('description'),
		        'type'=>'merchant',
        		'created_at'=>date('Y-m-d'),
    	    ]);
        
            $transitions->save();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('merchant_transition_created');
            $response=['responseCode'=>200,'message'=>$message,'transitions'=>$transitions];
            return $response;
        }
    }
    
    
    public function transition_show(){
        $response=array();
        
            $transitions=Transition::where(['type'=>'merchant','is_deleted'=>0])->select('image_url')->get();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('merchant_transition_images_found');
            $response=['responseCode'=>200,'message'=>$message,'transitions'=>$transitions];
            return $response;
       
    }
    
    
    public function client_transition_store(Request $request){
        $response=array();
        
        $new_name=null;
        if ($request->hasFile('image_url')) {
            $image=$request->file('image_url');
            $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
            $image = Image::make($image)->resize(300,376);
            $image->save('public/images/transitions/client_transitions/'.$new_name);
        }else{
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        
        
        if(empty($request->get('title')) || empty($request->get('description'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
       
        else{
    	    $transitions=new Transition([
        		'title'=>$request->get('title'),
        		'image_url'=>$new_name,
        		'description'=>$request->get('description'),
		        'type'=>'client',
        		'created_at'=>date('Y-m-d'),
    	    ]);
        
            $transitions->save();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('client_transition_created');
            $response=['responseCode'=>200,'message'=>$message,'transitions'=>$transitions];
            return $response;
        }
    }
    
    
    public function client_transition_show(){
        $response=array();
        
            $transitions=Transition::where(['type'=>'client','is_deleted'=>0])->select('image_url')->get();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('client_transition_images_found');
            $response=['responseCode'=>200,'message'=>$message,'transitions'=>$transitions];
            return $response;
       
    }
    
}