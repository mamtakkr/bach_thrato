<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\SubscriptionPlan;
class SubscriptionPlanController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function index(){
        $subscription_plans=SubscriptionPlan::all();
        return view('super_admin.subscription_plans.index',compact('subscription_plans'));
    }


    public function create(){
            return view('super_admin.subscription_plans.create');
    }


    public function store(Request $request){
            $this->validate($request,[
		'title'=>'required',
		'plan_type'=>'required',
		'no_of_days'=>'required',
		'no_of_products'=>'required',
		'no_of_stores'=>'required',
		'amount'=>'required',
		'description'=>'required',
	]);
	  $subscription_plans=new SubscriptionPlan([
		'title'=>$request->get('title'),
		'plan_type'=>$request->get('plan_type'),
		'no_of_days'=>$request->get('no_of_days'),
		'no_of_products'=>$request->get('no_of_products'),
		'no_of_stores'=>$request->get('no_of_stores'),
		'amount'=>$request->get('amount'),
		'description'=>$request->get('description'),
		'created_at'=>date('Y-m-d'),
		'updated_at'=>date('Y-m-d'),
	]);
        $subscription_plans->save();
        return redirect()->route('super_admin.subscription_plans.index')->with('success','Data Added');
    }


    public function edit($id){
        $subscription_plans=SubscriptionPlan::findOrFail($id);
        return view('super_admin.subscription_plans.edit',compact('subscription_plans'));
    }


    public function update(Request $request){
        $this->validate($request,[ 
              'title'=>'required',
              'plan_type'=>'required',
              'no_of_days'=>'required',
              'no_of_products'=>'required',
              'amount'=>'required',
        ]);
                
        $subscription_plans=SubscriptionPlan::findOrFail($request->get('id'));
	    $subscription_plans->title=$request->get('title');
        $subscription_plans->plan_type=$request->get('plan_type');
        $subscription_plans->no_of_days=$request->get('no_of_days');
        //$subscription_plans->date_of_availability=$request->get('date_of_availability');
        $subscription_plans->amount=$request->get('amount');
        $subscription_plans->no_of_products=$request->get('no_of_products');
		$subscription_plans->no_of_stores=$request->get('no_of_stores');
        $subscription_plans->description=$request->get('description');
    	$subscription_plans->updated_at=date('Y-m-d');
	         
        $subscription_plans->save();
        return redirect()->route('super_admin.subscription_plans.index')->with('success','Data Updated');
    }


    public function destroy($id){
        $subscription_plans=SubscriptionPlan::findOrFail($id);
    	$subscription_plans->delete();
        return redirect()->route('super_admin.subscription_plans.index')->with('success','Data Deleted');
    }
    
    
    public function super_admin_plans_status_edit(Request $request){
        // dd($request->status);
        SubscriptionPlan::where('id',$request->id)->update(['status'=>$request->status]);
    
        return redirect()->route('super_admin.subscription_plans.index');
    }
 
    
    public function ajax_plans_update_status(Request $request){
       
        SubscriptionPlan::where('id',$request->id)->update([
            'status'=>($request->status)
        ]);
        
        $language_code=app('App\Http\Controllers\CommonController')->get_user_current_language();
        if($request->status=='enable'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('sub_admin_status_enable',$language_code);
        }
        if($request->status=='disable'){
        $message=app('App\Http\Controllers\apis\OrderController')->get_translated_message('subscription_plans_disable',$language_code);
        }
        $update_status = SubscriptionPlan::where('id', $request->id)->get();
        
        return ['message'=>$message];
    }
}
