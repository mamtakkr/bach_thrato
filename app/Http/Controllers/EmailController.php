<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Mail\SendMail;
class EmailController extends Controller
{
    public function index($to=null){
        if(empty($to)){
            $to='newmotivetechnology@gmail.com';
         }
        $to_name="User";
        $from=env('MAIL_USERNAME');
        $from_name=env('MAIL_FROM_NAME');
        $subject='Notification alert';
        $data=[
            'greeting'=>"<b>Hi user,</b><br>",
            'title'=>"<b>Notification:</b><br>",
            'body'=>"You have been created at ".env('APP_URL').". Check the website for further details.<br><br><br>",
            'footer'=>"<b>From: </b>Support team"        
        ];
        //$this->send_email($to,$to_name,$from,$from_name,$subject,$data);
        //echo $to."<br>".$to_name."<br>".$from."<br>".$from_name."<br>".$subject;
        //dd($data);
        app('App\Http\Controllers\EmailController')->send_email($to,$to_name,$from,$from_name,$subject,$data);
    }
    
    public function send_email($to,$to_name,$from,$from_name,$subject,$data){
        \Mail::send('email',$data,function($message) use ($to,$to_name,$from,$from_name,$subject){
            $message->to($to,$to_name)->subject($subject);
            $message->from($from,$from_name);
        });
        Echo "Email Sent successfully";
    }
}
