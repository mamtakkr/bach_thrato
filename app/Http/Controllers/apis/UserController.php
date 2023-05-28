<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Setting;
use App\Contactlist;
use App\Advertisement;
use Hash;
use Image;
class UserController extends Controller
{
   
   public function random_strings($length) 
    { 
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
        return substr(str_shuffle($str_result), 0, $length);
    } 



    //customer area
    public function user_login(Request $request){
        $response=array();
        
        //return $resp;
        if(empty($request->get('email')) || empty($request->get('password')) || empty($request->get('location_lat')) || 
            empty($request->get('location_long')) || empty($request->get('device_token'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{    
            if(User::where('email',$request->get('email'))->exists()){
                $user = User::where('email', $request->email)->first();
                if(Hash::check($request->password, $user->password) && $user->user_type=="user" && $user->status=="enable" ){
                    User::where('email', $request->email)->update(['device_token'=>$request->device_token,
                    'location_lat'=>$request->location_lat, 'location_long'=>$request->location_long]);
                    $user = User::where('email', $request->email)->first();
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('logedin_succeessfully',$user->id);
                    $response=['responseCode'=>200,'message'=>$message,'user_id'=>$user];
                    return $response;
                }
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('login_failed');
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('login_failed');
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }

    public function user_logout(Request $request){
        if(empty($request->get('user_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{    
        $res=User::where('id',$request->user_id)->update(['device_token'=>null]);
        if($res==1){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('logout_successfully',$request->user_id);
            $response=['responseCode'=>200,'message'=>$message];
            return $response;
        }else{
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('could_not_logout',$request->user_id);
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        }
    }
    
    public function user_store(Request $request){
        
        $unique_id=$this->random_strings(12);
        if(User::where('unique_id',$unique_id)->exists()){
            $unique_id=$this->random_strings(12);
        }
       
        $response=array();
        if(empty($request->get('first_name')) || empty($request->get('last_name')) || empty($request->get('email')) || 
           empty($request->get('password')) || empty($request->get('location_lat')) || empty($request->get('location_long')) || 
           empty($request->get('device_token'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        else if($request->confirm_password!=$request->password){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('password_confirm_password_same');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        else if(User::where('email',$request->get('email'))->exists()){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('email_exists');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
    	    $users=new User([
        		'first_name'=>$request->get('first_name'),
        		'last_name'=>$request->get('last_name'),
        		'email'=>$request->get('email'),
        		'password'=>bcrypt($request->get('password')),
        		'unique_id'=>$unique_id,
        		'user_type'=>'user',
        		'location_lat'=>$request->get('location_lat'),
        		'location_long'=>$request->get('location_long'),
        		'device_token'=>$request->get('device_token'),
        		'created_at'=>date('Y-m-d'),
    	    ]);
            $users->save();
            unset($users->password);
            unset($users->user_type);
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('account_created_successfully');
            $response=['responseCode'=>200,'message'=>$message,'user_detail'=>$users];
            return $response;
        }
    }
    
    
    
    //merchant area
    public function merchant_login(Request $request){
        $response=array();
        if(empty($request->get('email')) || empty($request->get('password')) || empty($request->get('device_token'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');  
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{    
            if(User::where('email',$request->get('email'))->exists()){
                $user = User::where('email', $request->email)->first();
    
                //check user password
                if(Hash::check($request->password, $user->password) && $user->user_type=="merchant" && $user->status=="enable"){
                    User::where('email', $request->email)->update(['device_token'=>$request->device_token]);
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('logedin_succeessfully',$user->id);
                    $response=['responseCode'=>200,'message'=>$message,'user_id'=>$user];
                    return $response;
                }
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('login_failed');
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('login_failed');
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }

    public function merchant_store(Request $request){
        $unique_id=$this->random_strings(12);
        if(User::where('unique_id',$unique_id)->exists()){
            $unique_id=$this->random_strings(12);
        }
       
        $response=array();
        if(empty($request->get('first_name')) || empty($request->get('last_name')) || empty($request->get('email')) || 
            empty($request->get('password')) || empty($request->get('device_token'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else if($request->confirm_password!=$request->password){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('password_confirm_password_same');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else if(User::where('email',$request->get('email'))->exists()){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('email_exists');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            $no_of_products=Setting::find(1)->num_of_free_products;
            $no_of_stores=Setting::find(1)->num_of_free_stores;
    	    $users=new User([
        		'first_name'=>$request->get('first_name'),
        		'last_name'=>$request->get('last_name'),
        		'email'=>$request->get('email'),
        		'password'=>bcrypt($request->get('password')),
        		'unique_id'=>$unique_id,
        		'no_of_products'=>$no_of_products,
        		'no_of_stores'=>$no_of_stores,
        		'user_type'=>'merchant',
        		'device_token'=>$request->get('device_token'),
        		'created_at'=>date('Y-m-d'),
    	    ]);
            $users->save();
            unset($users->password);
            unset($users->user_type);
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('account_created_successfully');
            $response=['responseCode'=>200,'message'=>$message,'user_detail'=>$users];
            return $response;
        }
    }
    
    
        //Sub Admin area
    public function sub_admin_login(Request $request){
        $response=array();
        
        if(empty($request->get('email')) || empty($request->get('password'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{    
            if(User::where('email',$request->get('email'))->exists()){
                $user = User::where('email', $request->email)->first();
                if(Hash::check($request->password, $user->password) && $user->user_type=="sub_admin"){
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('logedin_succeessfully',$user->id);
                    $response=['responseCode'=>200,'message'=>$message,'user_id'=>$user];
                    return $response;
                }
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('login_failed');
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('login_failed');
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
    }

    public function sub_admin_store(Request $request){
        
        $unique_id=$this->random_strings(12);
        if(User::where('unique_id',$unique_id)->exists()){
            $unique_id=$this->random_strings(12);
        }
       
        $response=array();
        if(empty($request->get('email')) || empty($request->get('password'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        else if($request->confirm_password!=$request->password){
            //$user = User::where('email', $request->email)->first();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('password_confirm_password_same');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        else if(User::where('email',$request->get('email'))->exists()){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('email_exists');
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
    	    $users=new User([
        		'email'=>$request->get('email'),
        		'password'=>bcrypt($request->get('password')),
        		'unique_id'=>$unique_id,
        		'user_type'=>'sub_admin',
        		'created_at'=>date('Y-m-d'),
    	    ]);
            $users->save();
            unset($users->password);
            unset($users->user_type);
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('account_created_successfully');
            $response=['responseCode'=>200,'message'=>$message,'user_detail'=>$users];
            return $response;
        }
    }
    
    
    public function update_location(Request $request){
        //checking device token is changed or not
        if(!empty($request->get('user_id'))){
        if(User::find($request->get('user_id'))->status=='disable'){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('user_not_available');  
                $response=['responseCode'=>201,'message'=>$message];
                return $response;
            }
        }
        if(!empty($request->get('device_token')) && !empty($request->get('user_id'))){
            $resp=app('App\Http\Controllers\apis\UserController')->check_device_token($request->get('user_id'),$request->get('device_token'));
            if($resp['responseCode']){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$request->get('user_id'));    
                $response=['responseCode'=>419,'message'=>$message];
                return $response;
            }
        }
        $response=array();
        if(empty($request->get('user_id')) || empty($request->get('location_lat')) || empty($request->get('location_long'))){
            if(!empty($request->get('user_id'))){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields',$request->get('user_id'));
            }
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            if(User::where(['id'=> $request->user_id])->update(['location_lat'=>$request->location_lat,'location_long'=>$request->location_long])){
                $ad=$this->app_advertisements($request->get('user_id'));
                //print_r($ad); die;
                if($ad=='disable'){
                    $response=['responseCode'=>201,'message'=>"Subadmin disabled or unavailable."];
                return $response;
                }
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('location_updated_succeessfully',$request->get('user_id'));
                $response=['responseCode'=>200,'message'=>$message, 'data'=>$ad];
                return $response;
            }else{
                $response=['responseCode'=>203,'message'=>"Please try after some time."];
                return $response;
            }
        }
    }
    
    
    public function password_update(Request $request)
    { 
        $response=array();
        if(empty($request->get('user_id')) || empty($request->get('old_password')) || 
        empty($request->get('new_password')) || empty($request->get('confirm_password'))){
            if(!empty($request->get('user_id'))){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields',$request->get('user_id')); 
            }
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        else if($request->confirm_password!=$request->new_password){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('password_confirm_password_same',$request->get('user_id'));
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{    
            $user=User::where('id',$request->get('user_id'))->update(['password'=>bcrypt($request->get('new_password'))]);
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('password_changed',$request->get('user_id'));
            $response=['responseCode'=>200,'message'=>$message];
            return $response;
        }
    }
    
    public function contact_update(Request $request)
    { 
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
        if(empty($request->get('user_id')) || empty($request->get('first_name')) || 
            empty($request->get('last_name')) || empty($request->get('contact'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        else{    
            $user=User::where('id',$request->get('user_id'))
            ->update(['first_name'=>$request->first_name,'last_name'=>$request->last_name,'contact'=>$request->contact]);
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('contact_updated',$request->get('user_id'));
            $response=['responseCode'=>200,'message'=>$message];
            return $response;
        }
    }
    
    
    public function send_email($to,$to_name,$from,$from_name,$subject,$data){
        \Mail::send('email',$data,function($message) use ($to,$to_name,$from,$from_name,$subject){
            $message->to($to,$to_name)->subject($subject);
            $message->from($from,$from_name);
        });
    }


    //customer area
    public function forget_password_token(Request $request){
        //die("loading");
        $response=array();
        
        if(empty($request->get('email'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{     
            $user = User::where('email', $request->email)->first();
            if(User::where('email',$request->get('email'))->exists()){
               
                $token=$this->random_strings(4);
                User::where('email', $request->email)->update(['forget_password_token'=>$token]);  
                
                $to=$user->email;
                $to_name=$user->first_name." ".$user->last_name;
                $from=env('MAIL_USERNAME');
                $from_name=env('MAIL_FROM_NAME');
                $subject=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forget_password_notification',$user->id);
                $customer_greetings=app('App\Http\Controllers\DefaultNotificationController')->get_notification('dear_customer',$user->id);
             
                $forget_password_token_is=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forget_password_token_is',$user->id);
                $from1=app('App\Http\Controllers\DefaultNotificationController')->get_notification('from',$user->id);
                $support_team=app('App\Http\Controllers\DefaultNotificationController')->get_notification('support_team',$user->id);
                $data=[
                    'greeting'=>"<b>".$customer_greetings.",</b><br>",
                    'title'=>"<b>".$subject.":</b><br>",
                    'body'=>$forget_password_token_is.": ".$token." <br><br><br>",
                    'footer'=>"<b>".$from1.": </b>".$support_team        
                ];
                
                
                //$this->send_email($to,$to_name,$from,$from_name,$subject,$data);
                // echo $to."<br>".$to_name."<br>".$from."<br>".$from_name."<br>".$subject; die;
                // dd($data);
                
                app('App\Http\Controllers\apis\UserController')->send_email($to,$to_name,$from,$from_name,$subject,$data);
                $check_email=app('App\Http\Controllers\DefaultNotificationController')->get_notification('check_email',$user->id);
                $response=['responseCode'=>200,'message'=>$check_email];
                return $response;
            }
            else{
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('invalid_email',$user->id);
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
            }
        }
    }
    
    
    public function verify_forget_password(Request $request)
    { 
        $response=array();
        if(empty($request->get('email')) || empty($request->get('forget_password_token')) || 
            empty($request->get('new_password')) || empty($request->get('confirm_password'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        else if($request->confirm_password!=$request->new_password){
            $user = User::where('email', $request->email)->first();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('password_confirm_password_same',$user->id);
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        
        else{    
            User::where('email',$request->get('email'))->update(['password'=>bcrypt($request->get('new_password')),'forget_password_token'=>NULL]);
            $user = User::where('email', $request->email)->first();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('password_changed',$user->id);
            $response=['responseCode'=>200,'message'=>$message];
            return $response;
        }
    }
    
    
    public function update_profile(Request $request)
    { 
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
        
        $new_name=null;
        if ($request->hasFile('image_url')) {
            $image=$request->file('image_url');
            $new_name=str_replace(" ", "-",$request->get('first_name'))."-|-".rand().'.'.$image->getClientOriginalExtension();
            $image = Image::make($image)->resize(300,376);
            $image->save('public/images/users/'.$new_name);
        }
        if(empty($request->get('user_id')) || empty($request->get('first_name')) || 
           empty($request->get('last_name')) || empty($request->get('contact'))){
            
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{  
            $users=User::find($request->get('user_id'));
            $users->first_name=$request->get('first_name');
            $users->last_name=$request->get('last_name');
            $users->contact=$request->get('contact');
    	    if(isset($image)){
                $users->image_url=$new_name;   
    	    }
    	    $users->updated_at=date('Y-m-d');
            $users->save();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('profile_updated',$request->get('user_id'));  
                $response=['responseCode'=>200,'message'=>$message,'data'=>$users];
                return $response;
            
        }
    }
    
    
    public function add_contact_list(Request $request)
    {
        //checking device token is changed or not
        if(!empty($request->get('device_token')) && !empty($request->get('user_id'))){
            $resp=app('App\Http\Controllers\apis\UserController')->check_device_token($request->get('user_id'),$request->get('device_token'));
            if($resp['responseCode']){
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$request->get('user_id'));    
                $response=['responseCode'=>419,'message'=>$message];
                return $response;
            }
        }
        
        $contact_list_array = json_decode($request->contact_list, true);
        
        $response=array();
        $data=array();
        foreach($contact_list_array as $row){
            if(Contactlist::where(['contact'=>$row['contact'],'user_id'=>$request->user_id])->exists()){
                if(User::where(['contact'=>$row['contact']])->exists()){
                Contactlist::where(['contact'=>$row['contact']])->update(['is_registered'=>'1']);
            }
            }else{
                 $data=([
                    'user_id'=>$request->user_id,
                    'first_name'=>$row['first_name'],
                    'last_name'=>$row['last_name'],
                    'contact'=>$row['contact'],
                ]);
                $contact_list=new Contactlist($data);
                $contact_list->save();
            }
            if(empty($data)){}
            else{
                if(User::where(['contact'=>$row['contact']])->exists()){
                    Contactlist::where(['contact'=>$row['contact']])->update(['is_registered'=>'1']);
                }
            }
            
        } 
        $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('add_contactList',$request->get('user_id')); 
        $response=['responseCode'=>200,'message'=>$message];
        return $response;
    }
    
    
    public function contact_list_unregistered(Request $request)
    { 
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
        if(empty($request->get('user_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        
        else{    
                $contact_list=Contactlist::where(['user_id'=>$request->get('user_id'),'is_registered'=>'0'])
                ->select(['first_name','last_name','contact','is_registered'])->get();
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('contact_list',$request->get('user_id')); 
                    $response=['responseCode'=>200,'message'=>$message,'contact_list'=>$contact_list];
                    return $response;
        }
    }
    
    
    public function contact_list_registered(Request $request)
    { 
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
        if(empty($request->get('user_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        
        
        else{    
                $contact_list=Contactlist::where(['user_id'=>$request->get('user_id'),'is_registered'=>'1'])
                ->select(['first_name','last_name','contact','is_registered'])->get();
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('contact_list',$request->get('user_id'));  
                    $response=['responseCode'=>200,'message'=>$message,'contact_list'=>$contact_list];
                    return $response;
        }
    }
    
    
    public function device_token(Request $request)
    { 
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
        if(empty($request->get('user_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{   
            $device_token=User::where('id',$request->get('user_id'))->select('device_token')->get();
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('device_token_found',$request->get('user_id'));   
            $response=['responseCode'=>200,'message'=>$message,'device_token'=>$device_token];
            return $response;
        }
    }
    
    
    public function set_push_notification($to='',$data=array()){
        $apiKey = 'AAAA77qKhHw:APA91bEQRRoyTj5MtDtb3xzZVlre28qx87DvwkGguh11N5wsie5kl6IxNlLLgfMs-j2IZWTyjX7yzsSvBR0uP608g8NtZ5y8zVJrJvboNmwCtpFwgLYoiCiKRtLVN7mCiheDGKS8FRj0';
        $headers = array('Authorization: key='.$apiKey,'Content-Type: application/json');
        $url = 'https://fcm.googleapis.com/fcm/send';
        if(empty($to)){
        $to = "dRnxcGE_SW-sOULuGc2Utz:APA91bEZ9Zkrx1HI8DJLlTwXwi4qhVKRF7LCOyHKG4_74_0KX69UdgtEdfyf37eaVQ1T20yeMyA0YBmRq2OsD1YXOrw2jOzx-LVMMiH7zdoU0rwJzLqzwuvK8_LB_ORoGsWRckWqlmUl";
        $data = array(
            'body'=> 'Order Placed Successfully',
            'title'=> 'Team-Thrato-Test',
            //'sound'=>1,
            // 'icon'=>'https://newmotivetechnology.com/thrato/public/images/logo.png',
            //'image'=>'https://newmotivetechnology.com/thrato/public/images/logo.png'
            // 'favicon'=>'https://newmotivetechnology.com/thrato/public/images/logo.png'
            );
        }
        //$data['image']='https://newmotivetechnology.com/thrato/public/images/logo.png';
        //$data['icon']='https://newmotivetechnology.com/thrato/public/images/google-icon.svg';
        //---$data['favicon']='https://newmotivetechnology.com/thrato/public/images/icon.png';
        
        //$data['badge']='1';
        //$data['smallIcon']='https://newmotivetechnology.com/thrato/public/images/logo.png';
        //$data['largeIcon']='https://newmotivetechnology.com/thrato/public/images/logo.png';
        $fields = array('to'=>$to, 'notification'=>$data);
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $url );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        //print_r($result);
        
        curl_close( $ch );
        $response=json_decode($result,true);
        if($response['success']==1){
            $response1=['responseCode'=>200,'message'=>"Notification Sent",'data'=>$response];
            return $response1;
        }else{
            $response1=['responseCode'=>201,'message'=>"Notification Failed",'data'=>$response];
            return $response1;
        }
    } 
    
    
    public function app_advertisements($user_id){
        $response=array();
        if(empty($user_id)){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{
            $user=User::where(['id'=>$user_id,'status'=>'enable'])->first();
            $sub_admin=User::where(['user_type'=>'sub_admin','status'=>'enable'])->get()->toArray();
            $sa=array();
            foreach($sub_admin as $row){
                if(isset($row['location_lat'])){
                    // echo $user[0]->location_lat.", ".$user[0]->location_long.", ".$row['location_lat'].", ".$row['location_long']; die;
                    $result=$this->geo_distance($user->location_lat, $user->location_long, $row['location_lat'], $row['location_long'], "K", $row);
                     //print_r($result); die;
                    if(!empty($result)){
                        
                        $sa=$result;
                        // if($sa['status']=='disable'){
                        //    return 'set';
                        // }if($sa['status']=='disable'){
                        //     return 'sub_admin_enabled';
                        // } 
                        $advertisements=Advertisement::where(['sub_admin_id'=>$sa['id'],'is_deleted'=>0,'status'=>'enable'])->get()->toArray();
                        // print_r($advertisements[0]); die;
                        if(!empty($advertisements) ){
                            $advertisements[0]['distance']=$result['distance'];
                        // $response=['responseCode'=>200,'message'=>"Advertisement Found",'data'=>$advertisements[0]];
                        return $advertisements;
                        }
                        else{
                        // $response=['responseCode'=>200,'message'=>"Advertisement Found",'data'=>$advertisements[0]];
                        return $advertisements;
                        }
                    }
                }
            }
            if(empty($sa)){
             return 'disable';
            }
        }
        $advertisements=array();
        return $advertisements;
            // $response=['responseCode'=>201,'message'=>"Advertisement Not Found"];
            // return $response;
    }
    
    
    //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
    // echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
    //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";
    public function geo_distance($lat1, $lon1, $lat2, $lon2, $unit, $sub_admin) {
    //   if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    //     return 0;
    //   }
    //   else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
    
        // print_r($near_by_store_distance); die;
        if ($unit == "K") {
          $distance=number_format((float)($miles * 1.609344), 2, '.', '');
        } 
        $sub_admins=array();
        
        if($distance<=$sub_admin['sub_admin_distance']){
            $sub_admin['distance']=$distance;
            $sub_admins=$sub_admin;
        }
          return $sub_admins;
        // else if ($unit == "N") {
        //   return ($miles * 0.8684);
        // } else {
        //   return $miles;
        // }
    //   }
    }
 
    
    public function set_user_language_code(Request $request){
        $response=array();
        if(empty($request->get('user_id')) || empty($request->get('current_language_code'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields');    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{   
            $user=User::where('id',$request->get('user_id'))->update(['current_language_code'=>($request->get('current_language_code'))]);
            //$language_code=app('App\Http\Controllers\CommonController')->get_user_current_language($request->get('user_id'));
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('language_code_changed',$request->get('user_id')); 
            $response=['responseCode'=>200,'message'=>$message,'data'=>$request->get('current_language_code')];
            return $response;
        }
    }


    public function check_device_token($user_id,$device_token){
        $response=array();
        if(User::where(['id'=>$user_id,'device_token'=>$device_token])->exists()){
            
        }else{
                $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('forbidden',$user_id);    
                $response=['responseCode'=>419,'message'=>$message];
            return $response;
        }
    }
    
    
    public function get_expiry(Request $request){
        $response=array();
        if(empty($request->get('merchant_id'))){
            $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields',$request->get('merchant_id'));    
            $response=['responseCode'=>201,'message'=>$message];
            return $response;
        }
        else{ 
            //Checking if plan is expired.
            $user=User::find($request->get('merchant_id'));
            if(!empty($user->plan_expiry_date)){
            $date = new \DateTime($user->plan_expiry_date);
            $expiry_date=$date->format('Y-m-d H:i:s');
            $current_date=date('Y-m-d H:i:s');
                if($current_date > $expiry_date){
                    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('plan_expired',$user->id);
                    $response=['responseCode'=>203,'message'=>$message];
                    //print_r($response); die;
                    return $response;
                }
                else{
                        $response=['responseCode'=>200];
                        return $response;
                }
            }else{
                $response=['responseCode'=>200];
                     return $response;
            }
        }
    }
}


//update_location
/*
if(!empty($request->get('user_id'))){
    $message=app('App\Http\Controllers\DefaultNotificationController')->get_notification('fill_fields',$request->get('user_id')); 
}
*/