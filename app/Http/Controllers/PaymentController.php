<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Subscription_payment;
use App\SubscriptionPlan;
use Image;
class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */ 
    public function __construct(){
        $this->middleware('auth');
    }
        

    public function plan_subscribed_successfully($id){
        $values=explode("-",$id);
        
        $payment=Subscription_payment::where('transaction_id',$values['0'])->first();
        $merchant=User::find($values['1']);
        $subscription_plan=SubscriptionPlan::find($values['2']);
        //echo "<pre>"; var_dump($subscription_plan);
        return view('merchant.plan_subscribed_successfully',compact('payment','merchant','subscription_plan'));
    }



}