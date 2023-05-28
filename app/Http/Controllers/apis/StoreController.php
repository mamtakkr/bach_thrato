<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Category;
use App\Store;
use App\User;
use App\Favourite;
use App\Product;
use App\Setting;
use Image;

class StoreController extends Controller
{
   
public function store_add(Request $request){
        
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
    
    if(empty($request->get('title')) || empty($request->get('location')) || empty($request->get('merchant_id')) || empty($request->get('cat_id')) || 
    empty($request->get('location_lat')) || empty($request->get('location_long'))){
        $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');             
        $response=['responseCode'=>201,'message'=>$message];
        return $response;
    }
    else{
        $no_of_stores=app('App\Http\Controllers\CommonController')->get_user_no_of_stores($request->get('merchant_id'));
        //print_r($no_of_stores); die;
        $active_stores=Store::where(['merchant_id'=>$request->get('merchant_id'),'is_deleted'=>0]);
        if($active_stores->get()->count()<$no_of_stores){
            $new_name=null;
            if ($request->hasFile('image_url')) {
                $image=$request->file('image_url');
                $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
                $image = Image::make($image)->resize(300,376);
                $image->save('public/images/stores/'.$new_name);
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');             
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
            $stores=new Store([
                'merchant_id'=>$request->get('merchant_id'),
                'cat_id'=>$request->get('cat_id'),
                'title'=>$request->get('title'),
                'location'=>$request->get('location'),
                'timings'=>$request->get('timings'),
                'location_lat'=>$request->get('location_lat'),
                'location_long'=>$request->get('location_long'),
                'image_url'=>$new_name,
                'created_at'=>date('Y-m-d'),
            ]);
            
            $stores->save();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_created',$request->get('merchant_id'));      
            $response=['responseCode'=>200,'message'=>$message,'stores'=>$stores];
            return $response;
        }
        else{
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('free_stores_limit_reach',$request->get('merchant_id')); 
            $response=['responseCode'=>202,'message'=>$message];
            return $response;
        }
    }
}
    
    public function stores_show(Request $request){
        
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
            $st=Store::get()->toArray();
            $stores=array();
            $c=0;
            foreach($st as $row){
                $stores[$c]['id']=$row['id'];
                $stores[$c]['merchantID']=$row['merchant_id'];
                $stores[$c]['title']=$row['title'];
                $stores[$c]['location']=$row['location'];
                $stores[$c]['locationLat']=$row['location_lat'];
                $stores[$c]['locationLong']=$row['location_long'];
                $stores[$c]['timings']=$row['timings'];
                $stores[$c]['image_url']=$row['image_url'];
                $stores[$c]['created_at']=$row['created_at'];
                $stores[$c]['updated_at']=$row['updated_at'];
                $c++;
            }
            
            
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('stores_found'); 
            $response=['responseCode'=>200,'message'=>$message,'stores'=>$stores];
            return $response;
       
    }
    
    
    public function store_details(Request $request){
        
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
        if(empty($request->get('id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');             
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            $store=Store::find($request->id);
            if(!empty($store)){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_found');  
                $response=['responseCode'=>200,'message'=>$message,'store'=>$store];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_not_found');  
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
   
    public function store_edit(Request $request){
        
        //checking device token is changed or not
        if(!empty($request->get('device_token')) && !empty($request->get('user_id'))){
            $resp=app('App\Http\Controllers\apis\UserController')->check_device_token($request->get('user_id'),$request->get('device_token'));
            if($resp['responseCode']){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$request->get('user_id'));                     
                $response=['responseCode'=>419,'message'=>$message];
                return $response;
            }
        }
        
        if(empty($request->get('id')) || empty($request->get('title')) || empty($request->get('location')) || empty($request->get('merchant_id')) || 
         empty($request->get('location_lat')) || empty($request->get('location_long')) || empty($request->get('cat_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');             
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        else{
            $store=Store::findOrFail($request->get('id'));
        	$store->merchant_id=$request->get('merchant_id');
        	$store->cat_id=$request->get('cat_id');
        	$store->title=$request->get('title');
            $store->location=$request->get('location');
            $store->timings=$request->get('timings');
            $store->location_lat=$request->get('location_lat');
            $store->location_long=$request->get('location_long');
    	    $store->updated_at=date('Y-m-d');
    	    
            $new_name=null;
            if ($request->hasFile('image_url')) {
                $image=$request->file('image_url');
                $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
                $image = Image::make($image)->resize(300,376);
                $image->save('public/images/stores/'.$new_name);
                $store->image_url=$new_name;
            }
            $store->save();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_updated',$request->get('merchant_id'));     
            $response=['responseCode'=>200,'message'=>$message,'store'=>$store];
            return $response;
        
        }
    }
    
    
    public function merchant_store_details(Request $request){
        
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
        if(empty($request->get('merchant_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');             
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            $store=Store::where(['merchant_id'=>$request->get('merchant_id'),'is_deleted'=>0])->get();
            if(count($store)){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_found',$request->get('merchant_id')); 
                $response=['responseCode'=>200,'message'=>$message,'store'=>$store];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_not_found',$request->get('merchant_id')); 
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
    
    
    public function near_by_store(Request $request){
        
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
            $user=User::find($request->get('user_id'));
            //print_r($user); die;
            $current_date=date('Y-m-d');
            $stores=Store::join('users','users.id','=','stores.merchant_id')
                            ->select('stores.*','users.first_name as first_name','users.last_name as last_name','users.plan_expiry_date as plan_expiry_date')
                            ->where("stores.is_deleted",0)
                            //->where("users.plan_expiry_date",null)
                            //->where("users.plan_expiry_date",'>=',$current_date)
                            ->get()->toArray();
                       
                            
            //print_r($stores); die;
            $store_list=array();
            $favourite=0;
            $near_by_store_distance=Setting::get('near_by_store_distance')[0]->near_by_store_distance;
            
            foreach($stores as $row){
                // print_r($row); die;
                if($row['location_lat']!='null'){
                    $st=$this->geo_distance($user->location_lat, $user->location_long,$row['location_lat'], $row['location_long'], "K", $row,$near_by_store_distance);
                }
                if(Favourite::where(['user_id'=>$request->get('user_id'),'store_id'=>$row['id']])->exists()){
                    $favourite=1;  
                }
                else{
                    $favourite=0;  
                }
                if(count($st)){
                      $st['favourite']=$favourite;
                      
                      $dt=new \DateTime($st['plan_expiry_date']);
                      $plan_expiry_date=$dt->format('Y-m-d H:i:s');
                      //print_r($plan_expiry_date); die;
                      if($plan_expiry_date>=date('Y-m-d H:i:s') || empty($st['plan_expiry_date'])){
                            $store_list[]=$st;
                      }
                    //   $plan_expiry_date=new \DateTime($st['plan_expiry_date']);
                    //   if($st['plan_expiry_date']>=date('Y-m-d H:i:s') || $st['plan_expiry_date']==''){
                    //   $store_list[]=$st;
                    //   }
                }
            }
            if(count($store_list)){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_found',$request->get('user_id')); 
                $response=['responseCode'=>200,'message'=>$message,'stores'=>$store_list];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_not_found',$request->get('user_id')); 
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
    
    //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
    // echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
    //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";
    public function geo_distance($lat1, $lon1, $lat2, $lon2, $unit, $store,$near_by_store_distance) {
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
        $stores=array();
        
        if($distance<=$near_by_store_distance){
            $store['distance']=$distance;
            $stores=$store;
        }
          return $stores;
        // else if ($unit == "N") {
        //   return ($miles * 0.8684);
        // } else {
        //   return $miles;
        // }
    //   }
    }
    
    
    
    public function stores(Request $request){
        
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
            $st=Store::get()->toArray();
            $stores=array();
            $c=0;
            foreach($st as $row){
                $stores[$c]['id']=$row['id'];
                $stores[$c]['merchantID']=$row['merchant_id'];
                $stores[$c]['title']=$row['title'];
                $stores[$c]['location']=$row['location'];
                $stores[$c]['locationLat']=$row['location_lat'];
                $stores[$c]['locationLong']=$row['location_long'];
                $stores[$c]['timings']=$row['timings'];
                $stores[$c]['image_url']=$row['image_url'];
                $stores[$c]['created_at']=$row['created_at'];
                $stores[$c]['updated_at']=$row['updated_at'];
                $c++;
            }
            $response=['data'=>$stores];
            return $response;
    }
   
    public function add_favourite(Request $request){
        
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
        if(empty($request->get('store_id')) || empty($request->get('user_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');             
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            if(Favourite::where(['store_id'=>$request->get('store_id'),'user_id'=>$request->get('user_id')])->exists())
            {
    	        Favourite::where(["user_id"=>$request->get('user_id'),"store_id"=>$request->get('store_id')])->delete();
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('removed_from_favourite',$request->get('user_id')); 
                $response=['responseCode'=>200,'message'=>$message];
                return $response;
            }
            else{
        	    $favourite=new Favourite([
            		'store_id'=>$request->get('store_id'),
            		'user_id'=>$request->get('user_id'),
            		'created_at'=>date('Y-m-d'),
            		'updated_at'=>date('Y-m-d'),
        	    ]);
                $favourite->save();
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('add_favourite',$request->get('user_id')); 
                $response=['responseCode'=>200,'message'=>$message];
                return $response;
            }
        }
    }
   
    
    public function favourite_store_list(Request $request){
        
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
            $favourite=Favourite::where(['user_id'=>$request->get('user_id')])->get();
            $user=User::find($request->get('user_id'));
            $near_by_store_distance=Setting::get('near_by_store_distance')[0]->near_by_store_distance;
            $store=array();
            $stores=array();
            foreach($favourite as $row){
                $store=Store::find($row->store_id);
                $st=$this->geo_distance($user->location_lat, $user->location_long,$store['location_lat'], $store['location_long'], "K", $store,$near_by_store_distance);
                if(!empty($st)){
                      $stores[]=$st;
                }
            }
                if(count($stores)){
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('favourite_store_found',$request->get('user_id')); 
                    $response=['responseCode'=>200,'message'=>$message,'data'=>$stores];
                    return $response;
                }else{
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('favourite_store_not_found',$request->get('user_id')); 
                    $response=['responseCode'=>201,'message'=>$message];
                    return $response;
                }
        }
    }
    
    
    public function categories_show(Request $request){
        
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
        if(empty($request->get('code'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');             
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{    
            if($request->get('code')=='en'){
                $categories=Category::where('is_deleted',0)->select('id as id','title as title','image_url as image_url')->orderBy('id','asc')->get();    
            }
            if($request->get('code')=='es'){
                $categories=Category::where('is_deleted',0)->select('id as id','title_es as title','image_url as image_url')->orderBy('id','asc')->get();    
            }
            if($request->get('code')=='pt'){
                $categories=Category::where('is_deleted',0)->select('id as id','title_pt as title','image_url as image_url')->orderBy('id','asc')->get();    
            }
            
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('category_found');      
            $response=['responseCode'=>200,'message'=>$message,'categories'=>$categories];
            return $response;
        }
    }
       
    
    public function near_by_category_stores(Request $request){
        
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
            if(empty($request->get('category_id')) || empty($request->get('user_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');             
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            $user=User::find($request->get('user_id'));
           
            $stores=Store::join('users','users.id','=','stores.merchant_id')
                            ->select('stores.*','users.first_name as first_name','users.last_name as last_name','users.plan_expiry_date as plan_expiry_date')
                            ->where(['cat_id'=>$request->get('category_id'),'is_deleted'=>0])
                            //->where("users.plan_expiry_date",'!<',date('Y-mm-d'))
                            //->where("users.plan_expiry_date",null)
                            ->get()->toArray();
            
            
            $store_list=array();
            $favourite=0;
            $near_by_store_distance=Setting::get('near_by_store_distance')[0]->near_by_store_distance;
            
            foreach($stores as $row){
                if($row['location_lat']!='null'){
                    $st=$this->geo_distance($user->location_lat, $user->location_long,$row['location_lat'], $row['location_long'], "K", $row,$near_by_store_distance);
                    }
                    if(Favourite::where(['user_id'=>$request->get('user_id'),'store_id'=>$row['id']])->exists()){
                        $favourite=1;  
                    }
                    else{
                        $favourite=0;  
                    }
                    if(count($st)){
                          $st['favourite']=$favourite;
                          $dt=new \DateTime($st['plan_expiry_date']);
                          $plan_expiry_date=$dt->format('Y-m-d H:i:s');
                          //print_r($plan_expiry_date); die;
                          if($plan_expiry_date>=date('Y-m-d H:i:s') || empty($st['plan_expiry_date'])){
                                $store_list[]=$st;
                          }
                    }
            }
            if(count($store_list)){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('category_stores_found',$request->get('user_id'));     
                $response=['responseCode'=>200,'message'=>$message,'data'=>$store_list];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('category_stores_not_found',$request->get('user_id')); 
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
    
    public function get_search_results(Request $request) {
        
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
        $data=$request->get('data');
        if(empty($data) || empty($request->get('user_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');             
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
                  
                // $results = Product::join('stores','stores.id','=','products.store_id')
                //   ->orWhere('stores.title', 'LIKE', '%' . $data . '%')
                //   ->orWhere('products.title', 'LIKE', '%' . $data . '%')
                //   ->select('stores.*')->get();   
                
                
            $results = Product::join('stores','stores.id','=','products.store_id','right')
                ->join('users','users.id','=','stores.merchant_id')
                  ->orWhere('stores.title', 'LIKE', '%' . $data . '%')
                  ->orWhere('products.title', 'LIKE', '%' . $data . '%')
                  ->select('stores.*','users.first_name as first_name','users.last_name as last_name','users.plan_expiry_date as plan_expiry_date')->get();
                  
            // $stores=Store::join('users','users.id','=','stores.merchant_id')
            //                 ->select('stores.*','users.first_name as first_name','users.last_name as last_name','users.plan_expiry_date as plan_expiry_date')
            //                 ->where(['cat_id'=>$request->get('category_id'),'is_deleted'=>0])
            //                  ->get()->toArray();
                            
                            
                            
            $stores=array();
            foreach($results as $store){
                $stores[$store['id']]=$store;
            }
           
            $user=User::find($request->get('user_id'));
            $store_list=array();
            $near_by_store_distance=Setting::get('near_by_store_distance')[0]->near_by_store_distance;
            
            foreach($stores as $row){
                
                $st=$this->geo_distance($user->location_lat, $user->location_long,$row['location_lat'], $row['location_long'], "K", $row,$near_by_store_distance);
            
                if(!empty($st)){
                      //$store_list[]=$st;
                      $dt=new \DateTime($st['plan_expiry_date']);
                          $plan_expiry_date=$dt->format('Y-m-d H:i:s');
                          //print_r($plan_expiry_date); die;
                          if($plan_expiry_date>=date('Y-m-d H:i:s') || empty($st['plan_expiry_date'])){
                                $store_list[]=$st;
                          }
                }
            }
            if(count($store_list)){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('data_found',$request->get('user_id'));       
                $response=['responseCode'=>200,'message'=>$message,'data'=>$store_list];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('data_not_found',$request->get('user_id'));  
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
    
}