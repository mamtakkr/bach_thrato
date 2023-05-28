<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notification;
use App\User;
use Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $notification = Notification::all();
        return view('super_admin.notification.index', compact('notification'));
    }


    public function create()
    {

        $merchant = User::where('user_type', '=', 'merchant')->get();
        $users = User::where('user_type', '=', 'user')->get();
        return view('super_admin.notification.create', compact('merchant', 'users'));
    }
    
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'notification_text' => 'required',
        ]);

        $sent_users = array();
        if (!empty($request->user_ids)) {
            // die('user');
            $values = $request->user_ids;
            foreach ($values as $item) {
                //   echo $item ." , ";

                $token = User::where(['id' => $item])->select('id', 'device_token')->get();
                $device_token = $token[0]->device_token;
                $sent_users[] = $token[0]->id;

                //   continue;
                //dd(strlen($device_token)); die;
                if (strlen($device_token) == 163) {
                    $title = $request->title;
                    $message = $request->notification_text;
                    $args = array(
                        'body' => $message,
                        'title' => $title
                    );
                    $to = $device_token;
                    app('App\Http\Controllers\apis\UserController')->set_push_notification($to, $args);
                }
            }
        }
        // dd($sent_users); die;
        $sent_merchants = array();
        if (!empty($request->merchant_ids)) {
            // die('user');
            $values = $request->merchant_ids;
            foreach ($values as $item) {
                //   echo $item ." , ";

                $token = User::where(['id' => $item])->select('id', 'device_token')->get();
                $device_token = $token[0]->device_token;

                //   continue;
                //dd(strlen($device_token)); die;
                if (strlen($device_token) == 163) {
                    $title = $request->title;
                    $message = $request->notification_text;
                    $args = array(
                        'body' => $message,
                        'title' => $title
                    );
                    $to = $device_token;
                    app('App\Http\Controllers\apis\UserController')->set_push_notification($to, $args);
                    $sent_merchants[] = $token[0]->id;
                }
            }
        }
        // dd($sent_merchants); die;
        $user_ids    = implode(",", $sent_users);
        $merchant_ids    = implode(",", $sent_merchants);
        // dd($request->description);

        $notification = new Notification([
            'user_ids' => $user_ids,
            'merchant_ids' => $merchant_ids,
            'title' => $request->get('title'),
            'notification_text' => $request->get('notification_text'),
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ]);
        $notification->save();
        return redirect()->route('super_admin.notification.index')->with('success', 'Data Added');
    }


    public function notification_view($id)
    {
        $notification = Notification::findOrFail($id);
        //dd($notification->user_ids);
        $users = array();
        $user_ids = explode(",", $notification->user_ids);
        foreach ($user_ids as $value) {
            $result = User::find($value);
            $users[$value] = "";
            if (!empty($result)) {
                $users[$value] = $result['first_name'] . " " . $result['last_name'] . "|" . $result['email'];
            }
        }
        $merchants = array();
        $merchant_ids = explode(",", $notification->merchant_ids);
        foreach ($merchant_ids as $value) {
            $result = User::find($value);
            $merchants[$value] = "";
            if (!empty($result)) {
                $merchants[$value] = $result->first_name . " " . $result->last_name . "|" . $result->email;
            }
        }
        return view('super_admin.notification.notification_view', compact('users', 'merchants', 'notification'));
    }


    public function sub_admin_notification_index()
    {

        $notification = Notification::where('sub_admin_id', Auth::user()->id)->get();
        return view('sub_admin.notification.index', compact('notification'));
    }


    public function sub_admin_notification_create()
    {
        $id = Auth::user()->id;
        $sub_admin_details = User::where('id', $id)->select('location_lat', 'location_long', 'sub_admin_distance')->get();
        $sub_admin = $sub_admin_details[0];

        $all_users = User::where('user_type', '=', 'user')->get();
        $sa = array();
        $users = array();
        foreach ($all_users as $row) {
            if (isset($row['location_lat'])) {
                //echo $sub_admin->location_lat.", ".$sub_admin->location_long.", ".$row['location_lat'].", ".$row['location_long'].", "."K".", ".$sub_admin->sub_admin_distance; die;
                $result = $this->geo_distance($sub_admin->location_lat, $sub_admin->location_long, $row['location_lat'], $row['location_long'], "K", $sub_admin->sub_admin_distance, $row);
                //dd($result); die;
                //if(!empty($result)){
                $sa[] = $result;
                //}
            }
        }
        $users = $sa;
        //dd($sa); die;
        $all_merchant = User::where('user_type', '=', 'merchant')->get();
        $sa = array();
        foreach ($all_merchant as $row) {
            if (isset($row['location_lat'])) {

                //echo $sub_admin->location_lat.", ".$sub_admin->location_long.", ".$row['location_lat'].", ".$row['location_long']."distance".$sub_admin->sub_admin_distance."<br>"; 
                $result = $this->geo_distance($sub_admin->location_lat, $sub_admin->location_long, $row['location_lat'], $row['location_long'], "K", $sub_admin->sub_admin_distance, $row);
                if (!empty($result)) {
                    $sa[] = $result;
                    //dd($sa);
                }
            }
        }
        //dd($sa);
        $merchants = $sa;
        return view('sub_admin.notification.create', compact('merchants', 'users'));
    }


    //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
    // echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
    //echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";
    public function geo_distance($lat1, $lon1, $lat2, $lon2, $unit, $required_distance, $user)
    {
        //   if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        //     return 0;
        //   }
        //   else {
        $distance = 0;
        $users = array();
        //if($lat2=='29.979276'){
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        // print_r($near_by_store_distance); die;
        if ($unit == "K") {
            $distance = number_format((float)($miles * 1.609344), 2, '.', '');
        }
        //echo $lat1.", ".$lon1.", ".$lat2.", ".$lon2.", ".$unit.", ".$required_distance;

        if ($distance <= $required_distance) {
            $user['distance'] = $distance;
            $users = $user;
            // dd("inside");
        }
        //dd($users);   
        //}
        return $users;
        // else if ($unit == "N") {
        //   return ($miles * 0.8684);
        // } else {
        //   return $miles;
        // }
        //   }
    }


    public function sub_admin_notification_store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'notification_text' => 'required',
        ]);

        $sent_users = array();
        if (!empty($request->user_ids)) {
            // die('user');
            $values = $request->user_ids;
            foreach ($values as $item) {
                //   echo $item ." , ";

                $token = User::where(['id' => $item])->select('id', 'device_token')->get();
                $device_token = $token[0]->device_token;
                $sent_users[] = $token[0]->id;

                //   continue;
                //dd(strlen($device_token)); die;
                if (strlen($device_token) == 163) {
                    $title = $request->title;
                    $message = $request->notification_text;
                    $args = array(
                        'body' => $message,
                        'title' => $title
                    );
                    $to = $device_token;
                    app('App\Http\Controllers\apis\UserController')->set_push_notification($to, $args);
                }
            }
        }
        // dd($sent_users); die;
        $sent_merchants = array();
        if (!empty($request->merchant_ids)) {
            // die('user');
            $values = $request->merchant_ids;
            foreach ($values as $item) {
                //   echo $item ." , ";

                $token = User::where(['id' => $item])->select('id', 'device_token')->get();
                $device_token = $token[0]->device_token;

                //   continue;
                //dd(strlen($device_token)); die;
                if (strlen($device_token) == 163) {
                    $title = $request->title;
                    $message = $request->notification_text;
                    $args = array(
                        'body' => $message,
                        'title' => $title
                    );
                    $to = $device_token;
                    app('App\Http\Controllers\apis\UserController')->set_push_notification($to, $args);
                    $sent_merchants[] = $token[0]->id;
                }
            }
        }
        // dd($sent_merchants); die;
        $user_ids    = implode(",", $sent_users);
        $merchant_ids    = implode(",", $sent_merchants);
        // dd($request->description);

        $notification = new Notification([
            'user_ids' => $user_ids,
            'merchant_ids' => $merchant_ids,
            'sub_admin_id' => Auth::user()->id,
            'title' => $request->get('title'),
            'notification_text' => $request->get('notification_text'),
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ]);
        $notification->save();
        return redirect()->route('sub_admin.notification.index')->with('success', 'Data Added');
    }
    

    public function sub_admin_notification_view($id)
    {
        $notification = Notification::findOrFail($id);
        //dd($notification->user_ids);
        $users = array();
        $user_ids = explode(",", $notification->user_ids);
        foreach ($user_ids as $value) {
            $result = User::find($value);
            $users[$value] = $result['first_name'] . " " . $result['last_name'] . "|" . $result['email'];
        }
        $merchants = array();
        if (!empty($notification->merchant_ids)) {
            $merchant_ids = explode(",", $notification->merchant_ids);
            foreach ($merchant_ids as $value) {
                $result = User::find($value);
                $merchants[$value] = $result->first_name . " " . $result->last_name . "|" . $result->email;
            }
        }
        return view('sub_admin.notification.notification_view', compact('users', 'merchants', 'notification'));
    }


    
    public function set_fcm_web_notification($fields){
        $url ="https://fcm.googleapis.com/fcm/send";
        $key ="AAAAc-NazW4:APA91bHrvP2TO5YJKmmLGav5a7NclrUAm5GGGDpCrry63W4kEllNSv_umexZrBZo31afKrsi-YUE6kKiT52fCO7jZGuHNZRBLu6_B45rLOAUkrAzCfSyTGptfzrj-0-FiA1KmveCT6ga";
        $headers=array(
            'Authorization: key='.$key,
            'Content-Type:application/json'
        );
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($fields));
        $result=curl_exec($ch);
        curl_close($ch);
        $output=json_decode($result);
        if($output->success==1){
            //echo "Sent message";
        }
    }


    public function ajax_set_fcm_web_token(Request $request)
    {
        User::where('id',Auth::user()->id)->update(['fcm_web_token'=>$request->token]);
        $request->session()->put('token',$request->token);
    }
    
    
}
