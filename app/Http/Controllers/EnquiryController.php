<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use Auth;
use App\Enquiry;

class EnquiryController extends Controller
{
    
    public function __construct(){
        $this->middleware('auth');
    }
 
    public function show_enquiry()
    {
        $enquiries=Enquiry::join('users','users.id','=','enquiries.user_id')
                            ->select('enquiries.*','users.first_name as first_name','users.last_name as last_name')->paginate(25);
        return view('super_admin.enquiries.show_enquiry',compact('enquiries'));
    }
 
    public function show_enquiry_sub_admin()
    {
        $sub_admin_enquiries=Enquiry::join('users','users.id','=','enquiries.user_id')
                            ->where('users.user_type','sub_admin')
                            ->select('enquiries.*','users.first_name as first_name','users.last_name as last_name')->paginate(25);
        return view('super_admin.enquiries.sub_admin_show_enquiry',compact('sub_admin_enquiries'));
    }
 
    public function sub_admin_create_enquiry()
    {
        return view('sub_admin.enquiries.sub_admin_create_enquiry');
    }


    public function sub_admin_store_enquiry(Request $request){
            $this->validate($request,[
		'email'=>'required',
		'description'=>'required',
	]);
	  $enquiry=new Enquiry([
		'user_id'=>Auth::user()->id,
		'email'=>$request->get('email'),
		'description'=>$request->get('description'),
		'created_at'=>date('Y-m-d'),
	]);
        $enquiry->save();
        return redirect()->route('sub_admin.enquiries.sub_admin_create_enquiry')->with('success','Data Added');
    }
    
}