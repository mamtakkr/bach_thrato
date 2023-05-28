<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use Hash;
use App\User;
use App\Product;
use App\Order;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */
    public function __construct(){
        $this->middleware('auth');
    }
 
    public function super_admin_index(){
        $customers=User::where('user_type','user')->get()->count();
        $merchants=User::where('user_type','merchant')->get()->count();
        $accepted_orders=Order::where('status','accepted')->get()->count();
        $rejected_orders=Order::where('status','rejected')->get()->count();
        return view('super_admin.index',compact('customers','merchants','accepted_orders','rejected_orders'));
    } 
    
    public function sub_admin_index(){
        return view('sub_admin.index');
    }
    
    public function super_admin_sub_admin_index(){
        $sub_admin=User::where('user_type','sub_admin')->get();
        //dd($merchants);
        return view('super_admin.sub_admin.index',compact('sub_admin'));
    }
    
    public function super_admin_sub_admin_create(){
        return view('super_admin.sub_admin.create');
    }
    
    public function super_admin_sub_admin_store(Request $request){
            $this->validate($request,[
                'email' =>'required|string|max:255|unique:users',
                'address_line1' =>'required',
		         'password' => 'required|string|min:8|confirmed',
	]);
	  $sub_admin=new User([
	        'email' => $request->get('email'),
	        'address_line1' => $request->get('address_line1'),
	        'location_lat' => $request->get('location_lat'),
	        'location_long' => $request->get('location_long'),
    		'password' => Hash::make($request->get('password')),
    		'sub_admin_distance'=>$request->get('sub_admin_distance'),
    		'user_type'=>'sub_admin',
    		'created_at'=>date('Y-m-d'),
    		'updated_at'=>date('Y-m-d'),
	]);
        $sub_admin->save();
        return redirect()->route('super_admin.sub_admin.index')->with('success','Data Added');
    }
    
    
    public function super_admin_sub_admin_status_edit(Request $request){
        // echo $request->status; die;
        User::where('id',$request->id)->update(['status'=>$request->status]);
    
        return redirect()->route('super_admin.sub_admin.index');
    }
 
    
    public function super_admin_sub_admin_update_status(Request $request){
       
         User::where('id',$request->id)->update([
            'status'=>($request->status)
        ]);
        
        $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language();
        if($request->status=='enable'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('sub_admin_status_enable',$language_code);
        }
        if($request->status=='disable'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('sub_admin_status_disable',$language_code);
        }
        $update_status = User::where('id', $request->id)->get();
        
        return ['message'=>$message];
    }

    public function sub_admin_distance_update(Request $request)
    {
       return User::where('id',$request->id)->update(['sub_admin_distance'=>($request->sub_admin_distance)]);
    }
    
    public function change_password()
    {
        return view('change_password');
    }
    
    public function password_update(Request $request)
    { 
        $old_password=$request->old_password;
        
		 $request->validate([
		 'password' => 'required|string|min:8|confirmed',
		]);
		
        $new_password=User::findOrFail($request->get('id'));
	    $new_password->password= Hash::make($request->get('password'));
	    $new_password->updated_at=date('Y-m-d');
	         
        $new_password->save();
        return redirect()->route('super_admin.sub_admin.index');
    }
    
    public function sub_admin_change_password()
    {
        return view('sub_admin_change_password');
    }
    
    public function sub_admin_password_update(Request $request)
    { 
        $old_password=$request->old_password;
        
		 $request->validate([
		 'password' => 'required|string|min:8|confirmed',
		]);
		
        $new_password=User::findOrFail($request->get('id'));
	    $new_password->password= Hash::make($request->get('password'));
	    $new_password->updated_at=date('Y-m-d');
	         
        $new_password->save();
        return redirect()->route('sub_admin.index');
    }
    
    public function merchant_change_password()
    {
        return view('merchant.merchant_change_password');
    }
    
    public function merchant_update_password(Request $request)
    { 
        $old_password=$request->old_password;
        
		 $request->validate([
		 'password' => 'required|string|min:8|confirmed',
		]);
		
        $new_password=User::findOrFail($request->get('id'));
	    $new_password->password= Hash::make($request->get('password'));
	    $new_password->updated_at=date('Y-m-d');
	         
        $new_password->save();
        return redirect()->route('merchant.index');
    }
    
    
    public function ajax_set_user_creent_language(Request $request)
    {
        User::where('id',Auth::user()->id)->update(['current_language_code'=>$request->current_language]);
    }
    
    
  
    
}