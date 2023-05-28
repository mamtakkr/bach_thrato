<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Store;
use App\Order;
use App\User;
use App\OrderItem;
use App\Product;
use App\DefaultNotification;
use Intervention\Image\Facades\Image; 

class OrderController extends Controller
{
   
   
    public function order_add(Request $request){
        
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
		if(empty($request->get('user_id')) || empty($request->get('shipping_address_id')) || empty($request->get('payment_mode'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
		    return $response;
		}
        
	
        
        $order_item=array();
        //Checking item is not empty.
        if(!empty($request->get('order_item'))){
            $order_item=$request->get('order_item');
        }
         
        $store_id=$order_item[0]['store_id'];
        $store=Store::find($store_id);
        $merchant_id=$store->merchant_id;
        if(!empty($order_item)){ 
            $order=new Order([
        		'user_id'=>$request->get('user_id'),
        		'shipping_address_id'=>$request->get('shipping_address_id'),
        		'merchant_id'=>$merchant_id,
        		'store_id'=>$store_id,
        		'payment_mode'=>$request->get('payment_mode')
        	]);
            $order->save();                                                             //placed order 
            
            $order_total_amount=0;
            $c=1;
            foreach($order_item as $row){
                
                $product=Product::find($row['product_id']);
                $order_items=new OrderItem([
            		'order_id'=>$order->id,
            		'product_id'=>$row['product_id'],
            		'store_id'=>$row['store_id'],
            		'quantity'=>$row['quantity'],
            		'created_at'=>date('Y-m-d'),
            		'updated_at'=>date('Y-m-d'),
            	]);
                $order_total_amount+=$product->price*$row['quantity'];
                if(!empty($order_total_amount)){
                    $order_items->save();
                    /* Update quantity*/
                    $check_quantity=$this->update_product_quantity($row['product_id'],$row['quantity']);
                    if($check_quantity==false){
                        $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('order_not_enough',$request->get('user_id'));
                        $response=['responseCode'=>201,'message'=>$message];
                        return $response;
                    }
            	}
                $c++;
            }
		    $order->order_id=$order->id;
		    Order::where("id",$order->id)->update(["amount"=>$order_total_amount]); //updated price
		    $order->amount=$order_total_amount;
		    unset($order->id,$order->updated_at);
		    
            $token=User::where(['id'=>$merchant_id,'user_type'=>'merchant'])->get();
            $device_token=$token[0]->device_token;
           
            $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($merchant_id);
		
            $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('received_new_order',$language_code);
            
            $args=array(
                'body'=>$message,
                'title'=>$store->title,
                //"image"=>'https://newmotivetechnology.com/thrato/public/images/logo.png'
            );
            $to=$device_token;
            app('App\Http\Controllers\apis\UserController')->set_push_notification($to,$args);
		    
		    $merchant_token=User::find($merchant_id)->fcm_web_token;
		    if(!empty($merchant_token)){
            //$language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($merchant_id);
		    $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('web_order_arrived',$language_code);
            // print_r($message); die;
		    
		    $data=array(
                "to"=>$merchant_token,
                "notification"=>array(
                    "title"=>$store->title,
                    "body"=>$message,
                    //"icon"=>"https://newmotivetechnology.com/thrato/public/images/logo.png",
                    "click_action"=>"https://newmotivetechnology.com/thrato/login"
                )
            );
            app('App\Http\Controllers\NotificationController')->set_fcm_web_notification($data);
		    }
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('order_received',$merchant_id);
		    $response=['responseCode'=>200,'message'=>$message,"Order"=>$order];
		    
		    return $response;   
		    
        }else{
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('please_provide_products',$merchant_id);
             $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
    }


    public function get_merchant_orders(Request $request){
        
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
            $orders=array();
            if(!empty($request->get('status'))){
            
            $get_users_orders=Order::join('stores','stores.id','=','orders.store_id')
                ->join('users','users.id','=','orders.user_id')
                ->join('addresses','addresses.id','=','orders.shipping_address_id')
                ->where(['orders.merchant_id'=>$request['merchant_id'],'orders.status'=>$request->get('status')])
                ->where('orders.amount','>',0)
                ->select('orders.id as order_id','orders.user_id as customer_id','users.first_name as first_name','users.last_name as last_name',
                'users.email as customer_email','users.contact as customer_contact','orders.shipping_address_id as shipping_address_id',
                'addresses.address_line1 as address_line1','addresses.address_line2 as address_line2','addresses.landmark as landmark',
                'orders.payment_mode as payment_mode','orders.status as status','orders.amount as total_amount','orders.created_at as date_time',
                'stores.id as store_id','stores.title as store_name','stores.image_url as store_image')->get();
                $c=0;        
                foreach($get_users_orders as $row){
                $row['customer_name']=$row['first_name']." ".$row['last_name'];
                unset($row['first_name']);
                unset($row['last_name']);
                $orders[$c]=$row;
                $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
                    ->where("order_id",$row['order_id'])
                    ->select('order_items.quantity as quantity','products.id as product_id','products.title as product_name','products.code as product_code','products.price as price')->get();
                    $c++;
                }
                     
            }else{
                $get_users_orders=Order::join('stores','stores.id','=','orders.store_id')
                    ->join('users','users.id','=','orders.user_id')
                    ->join('addresses','addresses.id','=','orders.shipping_address_id')
                    ->where('orders.merchant_id',$request['merchant_id'])
                    ->select('orders.id as order_id','orders.user_id as customer_id','users.first_name as first_name','users.last_name as last_name',
                    'users.email as customer_email','users.contact as customer_contact','orders.shipping_address_id as shipping_address_id',
                    'addresses.address_line1 as address_line1','addresses.address_line2 as address_line2','addresses.landmark as landmark',
                    'orders.payment_mode as payment_mode','orders.status as status','orders.amount as total_amount',
                    'stores.id as store_id','stores.title as store_name','stores.image_url as store_image','orders.created_at as date_time')->get();
                // echo "<pre>"; var_dump($get_users_orders); die;
               $c=0;        
                foreach($get_users_orders as $row){
                $row['customer_name']=$row['first_name']." ".$row['last_name'];
                unset($row['first_name']);
                unset($row['last_name']);
                $orders[$c]=$row;
                $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
                                        ->where("order_id",$row['order_id'])
                                        ->select('order_items.quantity as quantity','products.id as product_id',
                                            'products.title as product_name','products.code as product_code','products.price as price')->get();
                    $c++;
                }
                        
            }
            if(!empty($get_users_orders))
            {
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('merchant_orders_found',$request->get('merchant_id'));  
                $response=['responseCode'=>200,'message'=>$message,'orders'=>$orders];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('merchant_orders_not_found',$request->get('merchant_id'));
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
    
    
    public function get_customer_orders(Request $request){
        
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
        $orders=array();
        if(empty($request->get('user_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            if(!empty($request->get('status'))){
            $get_users_orders=Order::join('stores','stores.id','=','orders.store_id')
                ->join('users','users.id','=','orders.user_id')
                ->join('addresses','addresses.id','=','orders.shipping_address_id')
                ->where(['orders.user_id'=>$request['user_id'],'orders.status'=>$request->get('status')])
                ->select('orders.id as order_id','orders.merchant_id as merchant_id','users.first_name as customer_firstname','users.last_name as customer_lastname',
                    'orders.shipping_address_id as shipping_address_id','addresses.address_line1 as address_line1',
                    'addresses.address_line2 as address_line2','addresses.landmark as landmark',
                    'orders.payment_mode as payment_mode','orders.status as status','orders.amount as total_amount','orders.created_at as date_time',
                    'stores.id as store_id','stores.title as store_name','stores.image_url as store_image')->get();
                               
                $c=0;
                foreach($get_users_orders as $row){
                $orders[$c]=$row;
                $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
                                        ->where("order_id",$row['order_id'])
                                        ->select('order_items.quantity as quantity','products.id as product_id',
                                            'products.title as product_name','products.code as product_code','products.price as price')->get();
                 $c++;
                }                  
                                       
            }else{
                $get_users_orders=Order::join('stores','stores.id','=','orders.store_id')
                    ->join('users','users.id','=','orders.user_id')
                    ->join('addresses','addresses.id','=','orders.shipping_address_id')
                   ->where('orders.user_id',$request['user_id'])
                   ->select('orders.id as order_id','orders.merchant_id as merchant_id','orders.shipping_address_id as shipping_address_id',
                   'addresses.address_line1 as address_line1','addresses.address_line2 as address_line2','addresses.landmark as landmark',
                   'orders.payment_mode as payment_mode','orders.status as status','orders.amount as total_amount','orders.created_at as date_time',
                   'stores.id as store_id','stores.title as store_name','stores.image_url as store_image')->get();
                // echo "<pre>"; var_dump($get_users_orders); die;
                $c=0;
                foreach($get_users_orders as $row){
                $orders[$c]=$row;
                $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
                    ->where("order_id",$row['order_id'])
                    ->select('order_items.quantity as quantity','products.id as product_id',
                    'products.title as product_name','products.code as product_code','products.price as price')->get();
                 $c++;
                }                  
            }
            if(!empty($get_users_orders))
            {
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('customer_orders_found',$request->get('user_id'));
                $response=['responseCode'=>200, 'message'=>$message,'orders'=>$orders];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('customer_orders_not_found',$request->get('user_id'));
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
    /*
    
    public function update_orders(Request $request){
        
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
        
        if(empty($request->get('order_id')) || empty($request->get('status'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
        
        $order=Order::find($request->order_id);
        $store=Store::find($order->store_id);
        $merchant_id=$store->merchant_id;
        $customer_id=$order->user_id;
        
        $token=User::find($customer_id);
        $device_token=$token->device_token;
             $update_orders = Order::find($request->order_id);
       
            $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($customer_id);
            
            if($request->status=='accepted'){
                $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_accept',$language_code);
            }
            elseif($request->status=='rejected'){
                $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_reject',$language_code);
                  $items=OrderItem::where('order_id',$update_orders->id)->get();
                    foreach($items as $row){
                        $this->increase_product_quantity($row['product_id'],$row['quantity']);
                    }
            }
            elseif($request->status=='order_cancel'){
                $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_cancel',$language_code);
                 $items=OrderItem::where('order_id',$update_orders->id)->get();
                    foreach($items as $row){
                        $this->increase_product_quantity($row['product_id'],$row['quantity']);
                    }
                
            }
            else{
                 $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_updted',$language_code);
            }
            
            $args=array(
                'body'=>$message,
                'title'=>$store->title,
                "image"=>'https://newmotivetechnology.com/thrato/public/images/logo.png'
            );
            $to=$device_token;
            //app('App\Http\Controllers\apis\UserController')->set_push_notification($to,$args);
                    if($request->status=='accepted'){ 
                       $items=OrderItem::where('order_id',$update_orders->id)->get();
                    /* foreach($items as $row){
                         $product=Product::find($row['product_id']);
                        if($product->quantity<=0){
                               
                            $token=User::find($merchant_id);
                            $device_token=$token->device_token;
                            $date = new \DateTime(date('Y-m-d'));
                            $date->add(new \DateInterval("P7D"));
                            $expiry_date=$date->format('Y-m-d H:i:s');
                            
                            Product::where(['id'=>$row['product_id']])->update(['expiry_date'=>$expiry_date]);
                            
                            $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($merchant_id);
                            
                            $msg=app('App\Http\Controllers\apis\OrderController')->get_translated_message('quantity_zero_update',$language_code);
                            $message=$product->title ." ".$msg ." ". $date->format('d-M, Y');
                            
                            //$this->check_zero($row['product_id'],$request->get('language_code')); 
                            //if(empty($product->notified_date)){
                            $args=array(
                                'body'=>$message,
                                'title'=>$store->title,
                                "image"=>'https://newmotivetechnology.com/thrato/public/images/logo.png'
                            );
                            $to=$device_token;
                            app('App\Http\Controllers\apis\UserController')->set_push_notification($to,$args);
                            Product::where(['id'=>$row['product_id']])->update(['notified_date'=>date('Y-m-d')]);
                           // }
                        }
                    } /
                            $token=User::find($merchant_id);
                            $device_token=$token->device_token;
                            $date = new \DateTime(date('Y-m-d'));
                            $date->add(new \DateInterval("P7D"));
                            $expiry_date=$date->format('Y-m-d H:i:s');
                            
                            //Product::where(['id'=>$row['product_id']])->update(['expiry_date'=>$expiry_date]);
                            
                            //$language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($merchant_id);
                            
                            //$msg=app('App\Http\Controllers\apis\OrderController')->get_translated_message('quantity_zero_update',$language_code);
                            //$message=$product->title ." ".$msg ." ". $date->format('d-M, Y');
                            $message="great";
                            //$this->check_zero($row['product_id'],$request->get('language_code')); 
                            //if(empty($product->notified_date)){
                            $args=array(
                                'body'=>$message,
                                'title'=>"title",
                                "image"=>'https://newmotivetechnology.com/thrato/public/images/logo.png'
                            );
                            $to=$device_token;
                            //app('App\Http\Controllers\apis\UserController')->set_push_notification($to,$args);
                            
                            
                            $apiKey = 'AAAA77qKhHw:APA91bEQRRoyTj5MtDtb3xzZVlre28qx87DvwkGguh11N5wsie5kl6IxNlLLgfMs-j2IZWTyjX7yzsSvBR0uP608g8NtZ5y8zVJrJvboNmwCtpFwgLYoiCiKRtLVN7mCiheDGKS8FRj0';
                            $headers = array('Authorization: key='.$apiKey,'Content-Type: application/json');
                            $url = 'https://fcm.googleapis.com/fcm/send';
                            $fields = array('to'=>$to, 'notification'=>$args);
                          
                        
                          
                            $ch = curl_init();
                            curl_setopt( $ch,CURLOPT_URL, $url );
                            curl_setopt( $ch,CURLOPT_POST, true );
                            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
                            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
                            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
                            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
                            $result = curl_exec($ch );
                              print_r( $result); die;
                           
                            
                            
                            
                }
                Order::where('id',$request->get('order_id'))->update(['status'=>$request->status]);
                $orders = Order::where('id', $request->order_id)->get();
            if(count($orders)){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('order_updated',$customer_id);
                $response=['responseCode'=>200,'message'=>$message,'update_orders'=>$orders];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('order_not_updated',$customer_id);
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
    */
    
  
    
    public function update_orders(Request $request){
        
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
        
        if(empty($request->get('order_id')) || empty($request->get('status'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
        
        $order=Order::find($request->order_id);
        $store=Store::find($order->store_id);
        $merchant_id=$store->merchant_id;
        $customer_id=$order->user_id;
        
        $token=User::find($customer_id);
        $device_token=$token->device_token;
            $update_orders = Order::find($request->order_id);
       
            $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($customer_id);
            
            if($request->status=='accepted'){
                $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_accept',$language_code);
            }
            elseif($request->status=='rejected'){
                $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_reject',$language_code);
                  $items=OrderItem::where('order_id',$update_orders->id)->get();
                    foreach($items as $row){
                        $this->increase_product_quantity($row['product_id'],$row['quantity']);
                    }
            }
            elseif($request->status=='order_cancel'){
                $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_cancel',$language_code);
                 $items=OrderItem::where('order_id',$update_orders->id)->get();
                    foreach($items as $row){
                        $this->increase_product_quantity($row['product_id'],$row['quantity']);
                    }
                
            }
            else{
                 $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_updted',$language_code);
            }
            
            $args=array(
                'body'=>$message,
                'title'=>$store->title,
                //"image"=>'https://newmotivetechnology.com/thrato/public/images/logo.png'
            );
            $to=$device_token;
            app('App\Http\Controllers\apis\UserController')->set_push_notification($to,$args);
                    if($request->status=='accepted'){ 
                       $items=OrderItem::where('order_id',$update_orders->id)->get();
                     foreach($items as $row){
                         $product=Product::find($row['product_id']);
                        if($product->quantity<=0){
                               
                            $token=User::find($merchant_id);
                            $device_token=$token->device_token;
                            $date = new \DateTime(date('Y-m-d'));
                            $date->add(new \DateInterval("P7D"));
                            $expiry_date=$date->format('Y-m-d H:i:s');
                            
                            Product::where(['id'=>$row['product_id']])->update(['expiry_date'=>$expiry_date]);
                            
                            $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($merchant_id);
                            
                            $msg=app('App\Http\Controllers\apis\OrderController')->get_translated_message('quantity_zero_update',$language_code);
                            $message=$product->title ." ".$msg ." ". $date->format('d-M, Y');
                            
                            //$this->check_zero($row['product_id'],$request->get('language_code')); 
                            //if(empty($product->notified_date)){
                            $args=array(
                                'body'=>$message,
                                'title'=>$store->title,
                                //"image"=>'https://newmotivetechnology.com/thrato/public/images/logo.png'
                            );
                            $to=$device_token;
                            app('App\Http\Controllers\apis\UserController')->set_push_notification($to,$args);
                            Product::where(['id'=>$row['product_id']])->update(['notified_date'=>date('Y-m-d')]);
                           // }
                        }
                    }
                }
                Order::where('id',$request->get('order_id'))->update(['status'=>$request->status]);
                $orders = Order::where('id', $request->order_id)->get();
            if(count($orders)){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('order_updated',$customer_id);
                $response=['responseCode'=>200,'message'=>$message,'update_orders'=>$orders];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('order_not_updated',$customer_id);
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
   
    
    public function increase_product_quantity($pro_id,$qty){
        $product=Product::find($pro_id);
        Product::where('id',$pro_id)->update(['quantity'=>(($product->quantity)+$qty)]);
        Product::where('id',$pro_id)->update(['notified_date'=>NULL]);
    }
    
    
    public function update_product_quantity($pro_id,$qty){
        $product=Product::find($pro_id);
        
        if($product->quantity<$qty){
            return false;
        }else{
            Product::where('id',$pro_id)->update(['quantity'=>(($product->quantity)-$qty)]);
            Product::where('id',$pro_id)->update(['notified_date'=>NULL]);
             return true;
        }
        
    }
    
    
    public function check_zero($pro_id,$language_code){
    //   echo "here";
    //     die;
        $product=Product::find($pro_id);
        $stores=Store::where('id',$product->store_id)->get(); 
        $merchant_id=$stores[0]->merchant_id;
        $token=User::where(['id'=>$merchant_id,'user_type'=>'merchant'])->get();
        $device_token=$token[0]->device_token;
        $date = new \DateTime(date('Y-m-d'));
        $date->add(new \DateInterval("P7D"));
        $expiry_date=$date->format('Y-m-d H:i:s');
       
        Product::where(['id'=>$pro_id])->where('quantity','<=', 0)->update(['expiry_date'=>$expiry_date]);
        $product=Product::where(['id'=>$pro_id])->where('quantity','<=', 0)->get();
        //if(!empty($product)){
        $last_date=new \DateTime($product[0]->expiry_date);
        $expiry=$last_date->format('d-M');
        
        // $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($merchant_id);
        
        // $msg=app('App\Http\Controllers\apis\OrderController')->get_translated_message('quantity_zero_update',$language_code);
        // $message=$product[0]->title ." ".$msg ." ". $expiry;
       
            // $args=array(
            //     'body'=>$message,
            //     'title'=>$stores[0]->title,
            //     "image"=>'https://newmotivetechnology.com/thrato/public/images/logo.png'
            // );
            // $to=$device_token;
            // app('App\Http\Controllers\apis\UserController')->set_push_notification($to,$args);
            return true;
    }
    
    
    public function orders_not_pending(Request $request){
        
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
                $get_users_orders=Order::join('stores','stores.id','=','orders.store_id')
                    ->join('users','users.id','=','orders.user_id')
                    ->join('addresses','addresses.id','=','orders.shipping_address_id')
                    ->where(['orders.merchant_id'=>$request->get('merchant_id')])->where('orders.status','!=','pending')
                    ->select('orders.id as order_id','orders.user_id as customer_id','users.first_name as first_name','users.last_name as last_name',
                        'users.email as customer_email','users.contact as customer_contact','orders.shipping_address_id as shipping_address_id',
                        'addresses.address_line1 as address_line1','addresses.address_line2 as address_line2','addresses.landmark as landmark',
                        'orders.payment_mode as payment_mode','orders.status as status','orders.amount as total_amount','orders.created_at as date_time',
                        'stores.id as store_id','stores.title as store_name','stores.image_url as store_image')->get();
                $c=0;        
                foreach($get_users_orders as $row){
                    
                    $status="";
                    if($row->status == 'accepted'){
                        $status=app('App\Http\Controllers\DefaultNotificationController')->get_notification('accepted',$request->get('merchant_id')); 
                    }if($row->status == 'rejected'){
                        $status=app('App\Http\Controllers\DefaultNotificationController')->get_notification('rejected',$request->get('merchant_id')); 
                    }if($row->status == 'user_cancel'){
                        $status=app('App\Http\Controllers\DefaultNotificationController')->get_notification('user_cancel',$request->get('merchant_id')); 
                    }
                    $row->status = $status;
                    
                    
                    
                    
                $row['customer_name']=$row['first_name']." ".$row['last_name'];
                unset($row['first_name']);
                unset($row['last_name']);
                $orders[$c]=$row;
                $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
                                        ->where("order_id",$row['order_id'])
                                        ->select('order_items.quantity as quantity','products.id as product_id',
                                            'products.title as product_name','products.code as product_code','products.price as price')->get();
                    $c++;
                }
                        
            }
            if(!empty($orders))
            {
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('orders_found',$request->get('merchant_id')); 
                // print_r($orders);
                $response=['responseCode'=>200, 'message'=>$message,'orders'=>$orders];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('orders_not_found',$request->get('merchant_id')); 
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        
        
    }
    
    
    public function get_translated_message($key,$language_code=null){
        
        $result=DefaultNotification::where('key',$key)->first();
            
            if($language_code=='es'){
                return $result->es;
            }
            if($language_code=='pt'){
                return $result->pt;
            }
            return $result->en;
    }   
    
    
    public function test(Request $request){
        if(empty($request->get('product_id'))){
             $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');                 
             $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            $product=Product::find($request->get('product_id'));
            $current_date = date('Y-m-d');
            $notified_date=$product->notified_date;
            
            if($current_date!=$notified_date){
                // echo "Not Matched";
               return Product::where('id',$request->get('product_id'))->update(['notified_date'=>$current_date]);
            }
        }
    }
    
}