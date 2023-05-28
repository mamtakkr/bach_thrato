<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
class CustomerController extends Controller
{
    
    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function super_admin_customers_index(){
        $customers=User::where('user_type','user')->paginate(25);
        return view('super_admin.customers.index',compact('customers'));
    }
    
    
    public function super_admin_customer_status_edit(Request $request){
        // echo $request->status; die;
        User::where('id',$request->id)->update(['status'=>$request->status]);
    
        return redirect()->route('super_admin.customers.index');
    }
 
    
    public function super_admin_customer_update_status(Request $request){
       
        User::where('id',$request->id)->update([
            'status'=>($request->status)
        ]);
        
        $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language();
        if($request->status=='enable'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('customer_status_enable',$language_code);
        }
        if($request->status=='disable'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('customer_status_disable',$language_code);
        }
        $update_status = User::where('id', $request->id)->get();
        
        return ['message'=>$message];
    }
    

}