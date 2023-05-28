<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SubscriptionPlan;
use App\Subscription_payment;
use App\User;


class SubscriptionPlanController extends Controller
{
   
   public function random_strings($length) 
    { 
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
        return substr(str_shuffle($str_result), 0, $length);
    } 

    public function show_all_subscription(Request $request){
        
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
        
            $subscription_plans=SubscriptionPlan::get();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('subscriptionplan_found',$request->get('user_id')); 
            $response=['responseCode'=>200,'message'=>$message,'data'=>$subscription_plans];
            return $response;
    }

    public function subscription_payment_add(Request $request){
        
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
        
        if(empty($request->get('merchant_id')) || empty($request->get('subscription_id')) || empty($request->get('transaction_id')) || empty($request->get('payment_gateway'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
       
        else{
            
            $days=SubscriptionPlan::where('id',$request->get('subscription_id'))->select('no_of_days')->get();
            $day=$days[0]->no_of_days;
            $date = new \DateTime(date('Y-m-d H:i:s'));
            $date->add(new \DateInterval("P".$day."D"));
            $datetime=$date->format('Y-m-d H:i:s');
            
    	    $subscription_payments=new Subscription_payment([
        		'merchant_id'=>$request->get('merchant_id'),
        		'subscription_id'=>$request->get('subscription_id'),
        		'transaction_id'=>$request->get('transaction_id'),
        		'payment_gateway'=>$request->get('payment_gateway'),
        		'expiry_date'=>$datetime,
    	    ]);
    	    
            $user=User::where('id', $request->get('merchant_id'))->update(['plan_expiry_date'=>$subscription_payments->expiry_date]);
           
    	    $subscription_payments->save();
            
            
            $subscription_plans=SubscriptionPlan::find($request->get('subscription_id'));
          
            User::find($request->get('merchant_id'))->update(['no_of_products'=>$subscription_plans->no_of_products,'no_of_stores'=>$subscription_plans->no_of_stores]);
           
            unset($subscription_payments->updated_at);
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('subscription_payment_created',$request->get('merchant_id')); 
            $response=['responseCode'=>200,'message'=>$message,'data'=>$subscription_payments];
            return $response;
        }
    }

    public function subscription_details(Request $request){
        
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
            $subscription_details=Subscription_payment::where('merchant_id',$request->get('merchant_id'))
                ->select('subscription_payments.*','subscription_plans.title as subscription_plan')
                ->join('subscription_plans','subscription_plans.id','=','subscription_payments.subscription_id')
                ->where('expiry_date','>',date('Y-m-d H:i:s'))
                ->orderBy('subscription_payments.id','desc')
                ->limit(1)
                ->get();
                
            if(count($subscription_details)){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('subscription_plan_detail_found',$request->get('merchant_id'));
                $response=['responseCode'=>200,'message'=>$message,'data'=>$subscription_details];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('subscription_plan_detail_not _found',$request->get('merchant_id'));
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }
    
}