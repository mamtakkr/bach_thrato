<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\Store;
use Intervention\Image\Facades\Image; 

class ProductController extends Controller
{
   
    public function product_add(Request $request){
        
        //checking device token is changed or not
        if(!empty($request->get('device_token')) && !empty($request->get('user_id'))){
            $resp=app('App\Http\Controllers\apis\UserController')->check_device_token($request->get('user_id'),$request->get('device_token'));
            if($resp['responseCode']==419){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$request->get('user_id'));                     
                $response=['responseCode'=>419,'message'=>$message];
                return $response;
            }
        }
        
        $response=array();
           
        if(empty($request->get('title')) || empty($request->get('price')) || empty($request->get('description')) || 
        empty($request->get('quantity')) || empty($request->get('store_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
       
        else{
   
            $merchant_id=Store::find($request->get('store_id'))->merchant_id;
            $no_of_products=app('App\Http\Controllers\CommonController')->get_user_no_of_products($merchant_id);
            
            $active_products=Product::join('stores','stores.id','=','products.store_id')
                            ->where(['stores.merchant_id'=>$merchant_id,'stores.is_deleted'=>0,'products.is_deleted'=>0])
                            ->select('products.id as id');
            //print_r($active_products->get()->toArray()); die;
          
            if($active_products->get()->count()<$no_of_products){

            $new_name=null;
            if ($request->hasFile('image_url')) {
                $image=$request->file('image_url');
                $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
                $image = Image::make($image)->resize(300,376);
                $image->save('public/images/products/'.$new_name);
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('please_provide_image',$merchant_id);
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
    	    $products=new Product([
        		'store_id'=>$request->get('store_id'),
        		'title'=>$request->get('title'),
        		'price'=>$request->get('price'),
        		'quantity'=>$request->get('quantity'),
        		'code'=>$request->get('code'),
        		'description'=>$request->get('description'),
        		'image_url'=>$new_name,
        		'created_at'=>date('Y-m-d'),
    	    ]);
        
            $products->save();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('product_created',$merchant_id);
            $response=['responseCode'=>200,'message'=>$message,'products'=>$products];
            return $response;
            }else{
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('free_products_limit_reach',$merchant_id);
            $response=['responseCode'=>202,'message'=>$message];
            return $response;
        }
        }
    }
    
    
    public function products_show(){
        
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
        
            $products=Product::where('is_deleted',0)->get();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('products_found');  
            $response=['responseCode'=>200,'message'=>$message,'products'=>$products];
            return $response;
       
    }
    
    
    public function store_products_show(Request $request){
        
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
        
        if(empty($request->get('store_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            $products=Product::where(['store_id'=>$request->get('store_id'),'is_deleted'=>0])->get();
            if(count($products)){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_products_found');     
                $response=['responseCode'=>200,'message'=>$message,'products'=>$products];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('store_products_not_found');  
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
   
    public function product_edit(Request $request){
        
        //checking device token is changed or not
        if(!empty($request->get('device_token')) && !empty($request->get('user_id'))){
            $resp=app('App\Http\Controllers\apis\UserController')->check_device_token($request->get('user_id'),$request->get('device_token'));
            if($resp['responseCode']){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$request->get('user_id'));                     
                $response=['responseCode'=>419,'message'=>$message];
                return $response;
            }
        }
        
        
       $new_name=null;
        if ($request->hasFile('image_url')) {
            $image=$request->file('image_url');
            $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
            $image = Image::make($image)->resize(300,376);
            $image->save('public/images/products/'.$new_name);
        }
      if(empty($request->get('id')) || empty($request->get('title')) || empty($request->get('price')) || 
         empty($request->get('quantity')) || empty($request->get('description')) || empty($request->get('store_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        else{
            if(isset($image)){
                $product=Product::where('id',$request->get('id'))
                ->update(['store_id'=>$request->store_id,'title'=>$request->title,
                          'price'=>$request->price,'quantity'=>$request->quantity,'code'=>$request->code,'image_url'=>$new_name,
                          'description'=>$request->description]);
                        if($request->quantity>0){
                            $product=Product::where('id',$request->get('id'))->update(['expiry_date'=>NULL]);
                            Product::where('id',$request->get('id'))->update(['notified_date'=>NULL]);
                        }
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('product_updated');     
                    $response=['responseCode'=>200,'message'=>$message,'product'=>$product];
                    return $response;
            }
            else{
                $product=Product::where('id',$request->get('id'))
                ->update(['store_id'=>$request->store_id,'title'=>$request->title,
                          'price'=>$request->price,'quantity'=>$request->quantity,'code'=>$request->code,
                          'description'=>$request->description]);
                        if($request->quantity>0){
                            $product=Product::where('id',$request->get('id'))->update(['expiry_date'=>NULL]);
                            Product::where('id',$request->get('id'))->update(['notified_date'=>NULL]);
                        }
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('product_updated');     
                    $response=['responseCode'=>200,'message'=>$message,'product'=>$product];
                    return $response;
            }
        }
    }
    
    
    public function product_delete(Request $request){
        
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
        
        if(empty($request->get('product_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            $product_delete=Product::where('id',$request->get('product_id'))->update(['is_deleted'=>1]);
            
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('product_deleted'); 
                $response=['responseCode'=>200,'message'=>$message,'data'=>$product_delete];
                return $response;
            
        }
    }
}