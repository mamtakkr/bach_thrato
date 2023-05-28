<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Advertisement;
use App\User;
use App\Setting;
use Image;

class AdvertisementController extends Controller
{
   
    public function advertisement_store(Request $request){
        
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
        
        $new_name=null;
        if ($request->hasFile('image_url')) {
            $image=$request->file('image_url');
            $new_name=rand().'.'.$image->getClientOriginalExtension();
            $image = Image::make($image)->resize(320,320);
            $image->save('public/images/advertisements/'.$new_name);
        }
        
        
        
        if(empty($request->get('title')) || empty($request->get('description'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
       
        else{
    	    $advertisements=new Advertisement([
        		'title'=>$request->get('title'),
        		'image_url'=>$new_name,
        		'description'=>$request->get('description'),
        		'created_at'=>date('Y-m-d'),
    	    ]);
        
            $advertisements->save();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('advertisement_created'); 
            $response=['responseCode'=>200,'message'=>$message,'data'=>$advertisements];
            return $response;
        }
    }
    
    
    public function advertisement_show(){
        
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
            $advertisements=Advertisement::where('is_deleted',0)->get();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('advertisement_found');    
            $response=['responseCode'=>200,'message'=>$message,'data'=>$advertisements];
            return $response;
    }
    
    
    public function app_advertisements(Request $request){
        
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
            $user=User::where('id',$request->get('user_id'))->get();
            $sub_admin=User::where('user_type','sub_admin')->get()->toArray();
            $sa=array();
            foreach($sub_admin as $row){
                if(isset($row['location_lat'])){
                    //echo $user[0]->location_lat.", ".$user[0]->location_long.", ".$row['location_lat'].", ".$row['location_long']; 
                    $result=$this->geo_distance($user[0]->location_lat, $user[0]->location_long, $row['location_lat'], $row['location_long'], "K", $row);
                    //dd($result);
                    if(!empty($result)){
                        
                        $sa=$result;
                        $advertisements=Advertisement::where('expiry_date','<',date('Y-m-d'))->update(['status'=>'disable']);
                        //print_r($advertisements); die;
                        $advertisements=Advertisement::where(['sub_admin_id'=>$sa['id'],'is_deleted'=>0,'status'=>'enable'])->get()->toArray();
                        $advertisements[0]['distance']=$result['distance'];
                        //print_r($advertisements); die;
                        if(!empty($advertisements) ){
                        $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('advertisement_found',$request->get('user_id'));  
                        $response=['responseCode'=>200,'message'=>$message,'data'=>$advertisements[0]];
                        return $response;
                        }
                    }
                }
            }
            
            
            
            // return $sa;
        }
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('advertisement_not_found',$request->get('user_id')); 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        
    }
    
    //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
    // echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
    //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";
    public function geo_distance($lat1, $lon1, $lat2, $lon2, $unit, $sub_admin) {
    //   if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    //     return 0;
    //   }
    //   else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
    
        // print_r($near_by_store_distance); die;
        if ($unit == "K") {
          $distance=number_format((float)($miles * 1.609344), 2, '.', '');
        } 
        $sub_admins=array();
        
        if($distance<=$sub_admin['sub_admin_distance']){
            $sub_admin['distance']=$distance;
            $sub_admins=$sub_admin;
        }
          return $sub_admins;
        // else if ($unit == "N") {
        //   return ($miles * 0.8684);
        // } else {
        //   return $miles;
        // }
    //   }
    }
}