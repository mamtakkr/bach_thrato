<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use App\Product;
use App\Store;
use Auth;
use Image;
class MerchantController extends Controller
{
    
    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function super_admin_merchants_index(){
        $merchants=User::where('user_type','merchant')->orderBy('created_at', 'DESC')->paginate(10);
        //dd($merchants);
        return view('super_admin.merchants.index',compact('merchants'));
    }
    
    
    public function super_admin_merchant_status_edit(Request $request){
        // dd($request->status);
        User::where('id',$request->id)->update(['status'=>$request->status]);
    
        return redirect()->route('super_admin.merchants.index');
    }
 
    
    public function super_admin_merchant_update_status(Request $request){
       
        User::where('id',$request->id)->update([
            'status'=>($request->status)
        ]);
        
        $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language();
        if($request->status=='enable'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('merchant_status_enable',$language_code);
        }
        if($request->status=='disable'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('merchant_status_disable',$language_code);
        }
        $update_status = User::where('id', $request->id)->get();
        
        return ['message'=>$message];
    }
    

}