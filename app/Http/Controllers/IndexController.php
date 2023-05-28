<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Slider;
use App\Product;
use App\Category;
use App\SubscriptionPlan;
use App\Setting;
use App\OrderItem;
use App\Order;
use App\Store;
use App\User;
use Auth;
use Image;
class IndexController extends Controller
{
    public function index(){
         return redirect()->route('login');
    }
    
    
    public function merchant_index(){
        return view('merchant.index');
    }
    
    public function get_membership($id=null){
     if(Auth::check()){
        $subscription_plans=SubscriptionPlan::all();
        return view('merchant.subscription_plans.index',compact('subscription_plans'));
    }
        return redirect()->route('login');
    }


    public function merchant_add_stores(){
         if(Auth::check()){
            $user_id=Auth::user()->id;
            $no_of_stores=app('App\Http\Controllers\CommonController')->get_user_no_of_stores($user_id);
            //dd($no_of_stores);
            $active_stores=Store::where(['merchant_id'=>Auth::user()->id,'is_deleted'=>0]);
            //dd($active_stores->get()->count());
               if($active_stores->get()->count()<$no_of_stores){
                    $categories=Category::where('is_deleted',0)->get();
                    return view('merchant.stores.add_stores',compact('categories'));
                }else{
                    return redirect()->route('merchant.subscription_plans.index');
                }
           
        }else{
            return redirect()->route('login');
        }
    }


    public function merchant_store_stores(Request $request){
        if(Auth::check()){
        $this->validate($request,[
		'title'=>'required',
		'cat_id'=>'required',
		'location'=>'required',
		'location_lat'=>'required',
		'morning_timings'=>'required',
// 		'evening_timings'=>'required',
		'image_url'=>'required|image|max:2048',
        ],[
            'location_lat.required' => 'Please enable location in browser first.'
        ]);
        $image=$request->file('image_url');
        $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $image->move('public/images/stores',$new_name);
        
        $timings=$request->get('morning_timings')." AND ".$request->get('evening_timings');
	  
	  $store=new Store([
		'merchant_id'=>Auth::user()->id,
		'title'=>$request->get('title'),
		'cat_id'=>$request->get('cat_id'),
		'location'=>$request->get('location'),
		'location_lat'=>$request->get('location_lat'),
		'location_long'=>$request->get('location_long'),
		'timings'=>$timings,
		'image_url'=>$new_name,
		'created_at'=>date('Y-m-d'),
	]);
        $store->save();
        return redirect()->route('merchant.stores.add_stores')->with('success','Store Added Successfully');
        }
        return redirect()->route('login');
    }


    public function merchant_add_products(){
        if(Auth::check()){
            $user_id=Auth::user()->id;
            $no_of_products=app('App\Http\Controllers\CommonController')->get_user_no_of_products($user_id);
            //dd($no_of_products);
            $active_products=Product::join('stores','stores.id','=','products.store_id')
                            ->where(['stores.merchant_id'=>Auth::user()->id,'stores.is_deleted'=>0,'products.is_deleted'=>0])
                            ->select('products.id as id');
            // dd($active_products->get()->count());
          
                if($active_products->get()->count()<$no_of_products){
                    $stores=Store::where(['merchant_id'=>Auth::user()->id,'is_deleted'=>0])->get();
                    return view('merchant.products.add_products',compact('stores'));
                }else{
                    return redirect()->route('merchant.subscription_plans.index');
                }
            
            
        }else{
            return redirect()->route('login');
        }
        
        
    }


    public function merchant_store_products(Request $request){
        if(Auth::check()){
        $this->validate($request,[
		'store_id'=>'required',
		'title'=>'required',
		'price'=>'required',
		'quantity'=>'required',
// 		'code'=>'required',
		'description'=>'required',
		'image_url'=>'required|image|max:2048',
	]);
        $image=$request->file('image_url');
        $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $image->move('public/images/products',$new_name);
        
	  $product=new Product([
		'store_id'=>$request->get('store_id'),
		'title'=>$request->get('title'),
		'price'=>$request->get('price'),
		'quantity'=>$request->get('quantity'),
		'code'=>$request->get('code'),
		'description'=>$request->get('description'),
		'image_url'=>$new_name,
		'created_at'=>date('Y-m-d'),
	]);
        $product->save();
        return redirect()->route('merchant.products.add_products')->with('success','Product Added Successfully');
        }
        return redirect()->route('login');
    }
    
    
    public function merchant_my_stores(){
        if(Auth::check()){
        $id=Auth::user()->id;
          $stores=Store::join('users','users.id','=','stores.merchant_id')
                ->where(['stores.merchant_id'=>$id,'is_deleted'=>0])
                ->select('stores.*')->orderBy('stores.created_at','desc')->get();
        return view('merchant.stores.my_stores',compact('stores'));
        }
        return view('merchant.stores.my_stores');
    }

    
    public function merchant_purchase($id=null){
        
        if(Auth::check()){
        if(empty($id)){
            return redirect()->route('merchant.subscription_plans.index');
        }
            $ids=explode('i',$id); 
            $plan=SubscriptionPlan::find($ids[1]);
            return view('merchant.subscription_plans.purchase',compact('plan'));
        }
    }


    public function merchant_store_edit($id){
        if(Auth::check()){
        $stores=Store::findOrFail($id);
        $timings= explode("AND",$stores->timings);
        $morning_timings="";
        $evening_timings="";
        if(!empty($timings[0])){
            $morning_timings=$timings[0];
        }
        
        if(!empty($timings[1])){
            $evening_timings=$timings[1];
        }
        $categories=Category::where('is_deleted',0)->get();
        return view('merchant.stores.store_edit',compact('stores','categories','evening_timings','morning_timings'));
        
        }
        return redirect()->route('login');
    }


    public function merchant_update_stores(Request $request){
    if(Auth::check()){
            
        $image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
        $new_name="";
    if($image!='')
    {
        $this->validate($request,[ 
        		'title'=>'required',
        		'cat_id'=>'required',
        		'location'=>'required',
        		'location_lat'=>'required',
        		'morning_timings'=>'required',
		        'new_image_url'=>'required|image|max:2045',
        ],[
            'location_lat.required' => 'Please enable location in browser first.'
        ]);
        $new_name=rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/stores/').$new_name);
        }else{
            $this->validate($request,[
        		'title'=>'required',
        		'cat_id'=>'required',
        		'location'=>'required',
        		'morning_timings'=>'required',
        		'location_lat'=>'required',
		],[
            'location_lat.required' => 'Please enable location in browser first.'
        ]);
        }   
        $timings=$request->get('morning_timings')." AND ".$request->get('evening_timings');     
        $store=Store::findOrFail($request->get('id'));
    	$store->title=$request->get('title');
    	$store->cat_id=$request->get('cat_id');
        $store->location=$request->get('location');
        $store->location_lat=$request->get('location_lat');
        $store->location_long=$request->get('location_long');
        $store->timings=$timings;
        if(isset($image)){
        $store->image_url=$new_name;
        }
        $store->created_at=date('Y-m-d');
	    $store->updated_at=date('Y-m-d');
	         
        $store->save();
        return redirect()->route('merchant.stores.my_stores');
    }
        return redirect()->route('login');
    }


    public function merchant_view_products($id){
        if(Auth::check()){
        $stores=Store::findOrFail($id);
        $categories=Category::where('is_deleted',0)->get();
        $products=Product::where(['store_id'=>$id,'is_deleted'=>0])->get();
        return view('merchant.products.view_products',compact('stores','categories','products'));
        }
        return redirect()->route('login');
    }


    public function merchant_edit_products($id){
        if(Auth::check()){
        $products=Product::findOrFail($id);
        return view('merchant.products.edit_products',compact('products'));
        }
        return redirect()->route('login');
    }
    
    
    public function merchant_update_products(Request $request){
    if(Auth::check()){
        $image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
        $new_name="";
    if($image!=''){
		 $this->validate($request,[
		      'title'=>'required',
    		  'new_image_url'=>'required|image|max:2045',
    		  'price'=>'required',
    		  'description'=>'required',
    		  'quantity'=>'required',
    		  //'code'=>'required',
		]);
        $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/products/').$new_name);
        }else{
            $this->validate($request,[
                'title'=>'required',
        		'price'=>'required',
        		'description'=>'required',
        		'quantity'=>'required',
		      //  'code'=>'required'
		]);
        }        
                
        $products=Product::findOrFail($request->get('id'));
        $products->title=$request->get('title');
        $products->price=$request->get('price');
        if(isset($image)){
        $products->image_url=$new_name;
        }
        $products->description=$request->get('description');
        $products->quantity=$request->get('quantity');
        $products->code=$request->get('code');
	    $products->updated_at=date('Y-m-d');
	         
        $products->save();
        if($products->quantity>0){
            $products=Product::where('id',$products->id)->update(['expiry_date'=>NULL]);
        }
        return redirect()->back()->with('success','Product Updated');
    }
        return redirect()->route('login');
    }


    public function merchant_show_orders(){
        if(Auth::check()){
        $id=Auth::user()->id;
        $orders=array();
        $get_users_orders=Order::join('stores','stores.id','=','orders.store_id')
                    ->join('users','users.id','=','orders.user_id')
                    ->join('addresses','addresses.id','=','orders.shipping_address_id')
                   ->where(['orders.merchant_id'=>$id,'orders.status'=>'pending'])
                   ->select('orders.id as order_id','orders.user_id as customer_id','users.first_name as first_name','users.last_name as last_name',
                   'users.email as customer_email','users.contact as customer_contact','orders.shipping_address_id as shipping_address_id',
                   'addresses.address_line1 as address_line1','addresses.address_line2 as address_line2','addresses.landmark as landmark',
                   'orders.payment_mode as payment_mode','orders.status as status','orders.amount as total_amount',
                   'stores.id as store_id','stores.title as store_name','stores.image_url as store_image','orders.created_at as date_time')
                   ->orderBy('orders.created_at', 'DESC')->get();
                 $c=0;        
               //dd($get_users_orders);
                foreach($get_users_orders as $row){
                $row['customer_name']=$row['first_name']." ".$row['last_name'];
                //dd($row['order_id']);
                $orders[$c]=$row;
                $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
                    ->where("order_id",$row['order_id'])
                    ->select('order_items.quantity as quantity','products.id as product_id', 'products.title as product_name',
                    'products.code as product_code','products.price as price','products.description as description')->get();
                    $c++;
                }
               // dd(count($orders));
                return view('merchant.orders.show_orders',compact('orders'));
        }else{
            return redirect()->route('login');
        }
    }

    public function ajax_merchant_show_orders(){
        $id=Auth::user()->id;
        $orders=array();
        $get_users_orders=Order::join('stores','stores.id','=','orders.store_id')
            ->join('users','users.id','=','orders.user_id')
            ->join('addresses','addresses.id','=','orders.shipping_address_id')
            ->where(['orders.merchant_id'=>$id,'orders.status'=>'pending'])
            ->select('orders.id as order_id','orders.user_id as customer_id','users.first_name as first_name','users.last_name as last_name',
            'users.email as customer_email','users.contact as customer_contact','orders.shipping_address_id as shipping_address_id',
            'addresses.address_line1 as address_line1','addresses.address_line2 as address_line2','addresses.landmark as landmark',
            'orders.payment_mode as payment_mode','orders.status as status','orders.amount as total_amount',
            'stores.id as store_id','stores.title as store_name','stores.image_url as store_image','orders.created_at as date_time')
            ->orderBy('orders.created_at', 'DESC')->get();
        $c=0;        
        foreach($get_users_orders as $row){
        $row['customer_name']=$row['first_name']." ".$row['last_name'];
        
        $orders[$c]=$row;
        $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
            ->where("order_id",$row['order_id'])
            ->select('order_items.quantity as quantity','products.id as product_id', 'products.title as product_name',
            'products.code as product_code','products.price as price','products.description as description')->get();
            $c++;
        }
        $st="";
        foreach($orders as $row){
        foreach($row->order_item as $row2){
        $st="<div class='col-lg-12 mt-5 pb-5' style='border: 1px solid #fff; box-shadow: 0px 2px 2px #ccc;'>
            <div class='row no-gutters'>
                <div class='col-md-3' style='background: #fff;'>
                    <div class='order_img'>
                    <center><img src='public/images/stores/".$row->store_image."' class='top mt-2' alt='...' width='200'></center>
                </div>
                </div>
                <div class='col-md-3'>
                   
                        <p><b>Order Id: </b>".$row['order_id']."</p>
                        <p>".$row2->product_name."</p>
                        <p><b>By:</b> ".$row['customer_name']."</p>
                        <p><b>Quantity: </b>".$row2->quantity." Amount: $".$row->total_amount."</p>
                   
                    
                </div>
                <div class='col-md-3'>
                    
                        <p class='mt-5'><b>Current Status:</b> ".$row->status."</p>
                      
                </div>
                <div class='col-md-3'>
                   
                        <p><b>Address:</b> ".$row['address_line1']." &nbsp; ".$row['address_line2']."</p>
                        <p><b>Contact:</b> ".$row['customer_contact']."</p>
                        <p><b>Time:</b> ".date(' h:i A', strtotime($row->date_time))." &nbsp; Date: ".date('d-M-Y', strtotime($row->date_time))."</p>
                  
                    <div class='rs_view_edit'>
                        <a href='#' onclick=updateStatus(".$row->order_id.",'accepted') name='status' class='btn btn-light stretched-link ml-0'></i>Accept</a>
                        <a href='#' onclick=updateStatus(".$row->order_id.",'rejected') name='status' class='btn btn-light stretched-link'></i>Reject</a>
                    </div>
                </div>
            </div>
        </div>";
        }
        }
        return $st;
    }


    public function merchant_my_orders(){
        if(Auth::check()){
        $id=Auth::user()->id;
        $orders=array();
        $get_users_orders=Order::join('stores','stores.id','=','orders.store_id')
                ->join('users','users.id','=','orders.user_id')
                ->join('addresses','addresses.id','=','orders.shipping_address_id')
                ->where(['orders.merchant_id'=>$id])->where('orders.status','!=','pending')
                ->select('orders.id as order_id','orders.user_id as customer_id','users.first_name as first_name','users.last_name as last_name',
                'users.email as customer_email','users.contact as customer_contact','orders.shipping_address_id as shipping_address_id',
                'addresses.address_line1 as address_line1','addresses.address_line2 as address_line2','addresses.landmark as landmark',
                'orders.payment_mode as payment_mode','orders.status as status','orders.amount as total_amount',
                'stores.id as store_id','stores.title as store_name','stores.image_url as store_image','orders.created_at as date_time')->get();
                 $c=0;        
                foreach($get_users_orders as $row){
                $row['customer_name']=$row['first_name']." ".$row['last_name'];
                
                $orders[$c]=$row;
                $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
                    ->where("order_id",$row['order_id'])
                    ->select('order_items.quantity as quantity','products.id as product_id', 'products.title as product_name',
                    'products.code as product_code','products.price as price','products.description as description')->get();
                    $c++;
                }
                return view('merchant.orders.my_orders',compact('orders'));
        }else{
            return redirect()->route('login');
        }
    }
 
    
    public function ajax_update_order(Request $request){
     
        $cus=Order::where('id',$request->get('id'))->get();
        $customer_id=$cus[0]->user_id;
        $token=User::where(['id'=>$customer_id,'user_type'=>'user'])->get();
        $device_token=$token[0]->device_token;
        Order::where('id',$request->get('id'))->update([
            'status'=>($request->get('status'))
        ]);
        
        $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($customer_id);
        if($request->get('status')=='accepted'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_accept',$language_code);
        }
        if($request->get('status')=='rejected'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_reject',$language_code);
        }
        
        $update_orders = Order::where('id', $request->get('id'))->get();
        $store_id=$update_orders[0]->store_id;
        $store=Store::where(['id'=>$store_id,'is_deleted'=>0])->get();
        $store_name=$store[0]->title;
        
        $args=array(
            'body'=>$message,
            'title'=>$store_name,
            "click_action"=>"https://newmotivetechnology.com/thrato/login"
        );
        $to=$device_token;
        
        app('App\Http\Controllers\apis\UserController')->set_push_notification($to,$args);
        
        $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language();
        if($request->get('status')=='accepted'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_accept',$language_code);
        }
        if($request->get('status')=='rejected'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('order_reject',$language_code);
            $items=OrderItem::where('order_id',$update_orders[0]->id)->get();
            foreach($items as $row){
                $this->increase_product_quantity($row['product_id'],$row['quantity']);
            }
        }
        
          
            // if($request->get('status')=='accepted'){ 
            //             $items=OrderItem::where('order_id',$update_orders[0]->id)->get();
            //             foreach($items as $row){
            //                 //$this->update_product_quantity($row['product_id'],$row['quantity']);
            //                 $this->check_zero($row['product_id']);
            //         }
            // }
        return ['message'=>$message ." ". $store_name];
    }
    
    
    public function increase_product_quantity($pro_id,$qty){
        $product=Product::find($pro_id);
        Product::where('id',$pro_id)->update(['quantity'=>(($product->quantity)+$qty)]);
    }
    
    
    public function update_product_quantity($pro_id,$qty){
        $product=Product::find($pro_id);
        Product::where('id',$pro_id)->update(['quantity'=>(($product->quantity)-$qty)]);
    }
    
    
    public function check_zero($pro_id){
        $product=Product::find($pro_id);
        $date = new \DateTime(date('Y-m-d'));
        $date->add(new \DateInterval("P7D"));
        $expiry_date=$date->format('Y-m-d H:i:s');
        Product::where(['id'=>$pro_id])->where('quantity','<=', 0)->update(['expiry_date'=>$expiry_date]);
        
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
        
        $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($merchant_id);
        
        $msg=app('App\Http\Controllers\apis\OrderController')->get_translated_message('quantity_zero_update',$language_code);
        $message=$product[0]->title ." ".$msg ." ". $expiry;
       
        $args=array(
            'body'=>$message,
            'title'=>$stores[0]->title,
            "click_action"=>"https://newmotivetechnology.com/thrato/login"
        );
        $to=$device_token;
        
        app('App\Http\Controllers\apis\UserController')->set_push_notification($to,$args);
    }
    
    
    public function term_condition(){
        $settings=Setting::select('id','term_condition')->get();
        return view('term_condition',compact('settings'));
    }
    
    
    public function ajax_super_admin_update_current_language(Request $request){
        $request->session()->put('language_code',$request->code); 
        return ['code'=>$request->code];
    }
}