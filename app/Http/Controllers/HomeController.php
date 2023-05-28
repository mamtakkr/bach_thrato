<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Country;
use App\State;
use Image;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */ 
    public function __construct(){
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
     
     
    public function index(){
        // if(Auth::user()->user_type=='user'){
        //     return redirect()->route('login');
        // }
        // else 
        if(Auth::user()->status=='enable'){
        if(Auth::user()->user_type=='super_admin'){
            return redirect()->route('super_admin.index');
        }
        else if(Auth::user()->user_type=='sub_admin'){
            return redirect()->route('sub_admin.index');
        }
        else if(Auth::user()->user_type=='merchant'){
            return redirect()->route('merchant.orders.show_orders');
        }
        else{
            return redirect()->route('login');
        }
        }else{
            Auth::logout();
            return view('user_disabled');
        }
            
    }
    
}
