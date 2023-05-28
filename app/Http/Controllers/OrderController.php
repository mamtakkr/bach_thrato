<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Order;
use App\OrderItem;
class OrderController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function accepted_orders(){
        $accepted_orders=Order::join('stores','stores.id','=','orders.store_id')
            ->join('users','users.id','=','orders.user_id')
            ->where('orders.status','accepted')
            ->select('users.first_name as first_name','users.last_name as last_name','orders.payment_mode as payment_mode','orders.status as status',
            'orders.amount as total_amount','stores.title as store_name','stores.image_url as store_image')->paginate(25);
    $c=0;        
                foreach($accepted_orders as $row){
                $row['customer_name']=$row['first_name']." ".$row['last_name'];
                
                $orders[$c]=$row;
                $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
                                        ->where("order_id",$row['order_id'])
                                        ->select('order_items.quantity as quantity','products.id as product_id',
                                            'products.title as product_name','products.code as product_code','products.price as price')->get();
                    $c++;
                }
        
        return view('super_admin.orders.accepted_orders',compact('accepted_orders'));
    }
    
    
    public function rejected_orders(){
        $rejected_orders=Order::join('stores','stores.id','=','orders.store_id')
            ->join('users','users.id','=','orders.user_id')
            ->where('orders.status','rejected')
            ->select('users.first_name as first_name','users.last_name as last_name','orders.payment_mode as payment_mode','orders.status as status',
            'orders.amount as total_amount','stores.title as store_name','stores.image_url as store_image')->paginate(25);
    $c=0;        
                foreach($rejected_orders as $row){
                $row['customer_name']=$row['first_name']." ".$row['last_name'];
                
                $orders[$c]=$row;
                $orders[$c]['order_item']=OrderItem::join('products','products.id','=','order_items.product_id')
                                        ->where("order_id",$row['order_id'])
                                        ->select('order_items.quantity as quantity','products.id as product_id',
                                            'products.title as product_name','products.code as product_code','products.price as price')->get();
                    $c++;
                }
        
        return view('super_admin.orders.rejected_orders', compact('rejected_orders'));
    }

}
