<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Country;
use App\State;
use Image;
class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */ 
    public function __construct(){
        $this->middleware('auth');
    }
    
    public function super_admin_profile(){
        $state_name="";
        if(isset(State::find(Auth::user()->state)->name)){
            $state_name=State::find(Auth::user()->state)->name;
        }
        $country_name="";
        if(isset(Country::find(Auth::user()->country)->name)){
            $country_name=Country::find(Auth::user()->country)->name;
        }
        return view('super_admin.profile.super_admin_profile',compact('state_name','country_name'));
    }

    
    public function super_admin_profile_update($id){
        $user=User::find($id);
        $countries=Country::all();
        $states=State::where('country_id',$user->country)->get();
        return view('super_admin.profile.super_admin_profile_edit_form',compact('user','countries','states'));
    }
    
    
    public function super_admin_profile_update_action(Request $request){
        $this->validate($request,[ 
              'first_name'=>'required',
              'last_name'=>'required',
              'contact'=>'required',
              'address_line1'=>'required',
            //   'address_line2'=>'required',
              'city'=>'required',
              'state'=>'required',
              'country'=>'required',
        ]);
        $users=User::findOrFail($request->get('id'));
	    $users->first_name=$request->get('first_name');
        $users->last_name=$request->get('last_name');
        $users->contact=$request->get('contact');
        $users->address_line1=$request->get('address_line1');
        // $users->address_line2=$request->get('address_line2');
        $users->city=$request->get('city');
        $users->state=$request->get('state');
        $users->country=$request->get('country');
	    $users->updated_at=date('Y-m-d');
	         
        $users->save();
        return redirect()->route('super_admin.profile.super_admin_profile')->with('success','Data Updated');
    }   
    
    public function ajax_get_states(Request $request){
        return $states=State::where('country_id',$request->country_id)->get();
    }
    
    public function super_admin_profile_image_insert(){
        return view('super_admin.profile.super_admin_profile_image_insert');
    }
    
    public function super_admin_profile_image_insert_action(Request $request)
    {
        $new_name="";
        if($_FILES['new_image_url']['name']){
            $image=$request->file('new_image_url');
            $new_name=Auth::user()->first_name." ".Auth::user()->last_name."-".Auth::user()->id.'.'.$image->getClientOriginalExtension();
            //$new_name=rand().'.'.$image->getClientOriginalExtension();
            $img = Image::make($image)->resize(320, 320);
            $img->save("public/images/users/".$new_name);
        }
            $user=User::find(Auth::user()->id);
            $user->image_url=$new_name;
            $user->save();
      return redirect()->route('super_admin.profile.super_admin_profile')->with('success','Your Photo Updated Successfully');
    }
    
    
    
    public function merchant_profile(){
        $state_name="";
        if(isset(State::find(Auth::user()->state)->name)){
            $state_name=State::find(Auth::user()->state)->name;
        }
        $country_name="";
        if(isset(Country::find(Auth::user()->country)->name)){
            $country_name=Country::find(Auth::user()->country)->name;
        }
        return view('merchant.profile.merchant_profile',compact('state_name','country_name'));
    }

    
    public function merchant_profile_update($id){
        $user=User::find($id);
        $countries=Country::all();
        $states=State::where('country_id',$user->country)->get();
        return view('merchant.profile.merchant_profile_edit_form',compact('user','countries','states'));
    }
    
    
    public function merchant_profile_update_action(Request $request){
        $this->validate($request,[ 
              'first_name'=>'required',
              'last_name'=>'required',
              'contact'=>'required',
              'address_line1'=>'required',
              'address_line2'=>'required',
            //   'city'=>'required',
            //   'state'=>'required',
            //   'country'=>'required',
        ]);
        $users=User::findOrFail($request->get('id'));
	    $users->first_name=$request->get('first_name');
        $users->last_name=$request->get('last_name');
        $users->contact=$request->get('contact');
        $users->address_line1=$request->get('address_line1');
        $users->address_line2=$request->get('address_line2');
        // $users->city=$request->get('city');
        // $users->state=$request->get('state');
        // $users->country=$request->get('country');
	    $users->updated_at=date('Y-m-d');
	         
        $users->save();
        return redirect()->route('merchant.profile.merchant_profile')->with('success','Data Updated');
    }  
    
    public function merchant_profile_image_insert(){
        return view('merchant.profile.merchant_profile_image_insert');
    }
    
    public function merchant_profile_image_insert_action(Request $request)
    {
        $new_name="";
        if($_FILES['new_image_url']['name']){
            $image=$request->file('new_image_url');
            $new_name=Auth::user()->first_name." ".Auth::user()->last_name."-".Auth::user()->id.'.'.$image->getClientOriginalExtension();
            //$new_name=rand().'.'.$image->getClientOriginalExtension();
            $img = Image::make($image)->resize(320, 320);
            $img->save("public/images/users/".$new_name);
        }
            $user=User::find(Auth::user()->id);
            $user->image_url=$new_name;
            $user->save();
      return redirect()->route('merchant.profile.merchant_profile')->with('success','Your Photo Updated Successfully');
    }
    
    public function sub_admin_profile(){
        $state_name="";
        if(isset(State::find(Auth::user()->state)->name)){
            $state_name=State::find(Auth::user()->state)->name;
        }
        $country_name="";
        if(isset(Country::find(Auth::user()->country)->name)){
            $country_name=Country::find(Auth::user()->country)->name;
        }
        return view('sub_admin.profile.sub_admin_profile',compact('state_name','country_name'));
    }

    
    public function sub_admin_profile_update($id){
        $user=User::find($id);
        $countries=Country::all();
        $states=State::where('country_id',$user->country)->get();
        return view('sub_admin.profile.sub_admin_profile_edit_form',compact('user','countries','states'));
    }
    
    
    public function sub_admin_profile_update_action(Request $request){
        $this->validate($request,[ 
              'first_name'=>'required',
              'last_name'=>'required',
              'contact'=>'required',
              'address_line1'=>'required',
            //   'address_line2'=>'required',
              'city'=>'required',
              'state'=>'required',
              'country'=>'required',
        ]);
        $users=User::findOrFail($request->get('id'));
	    $users->first_name=$request->get('first_name');
        $users->last_name=$request->get('last_name');
        $users->contact=$request->get('contact');
        $users->address_line1=$request->get('address_line1');
        // $users->sub_admin_distance=$request->get('sub_admin_distance');
        $users->city=$request->get('city');
        $users->state=$request->get('state');
        $users->country=$request->get('country');
	    $users->updated_at=date('Y-m-d');
	         
        $users->save();
        return redirect()->route('sub_admin.profile.sub_admin_profile')->with('success','Data Updated');
    }   
    
    public function sub_admin_profile_image_insert(){
        return view('sub_admin.profile.sub_admin_profile_image_insert');
    }
    
    public function sub_admin_profile_image_insert_action(Request $request)
    {
        $new_name="";
        if($_FILES['new_image_url']['name']){
            $image=$request->file('new_image_url');
            $new_name=Auth::user()->first_name." ".Auth::user()->last_name."-".Auth::user()->id.'.'.$image->getClientOriginalExtension();
            //$new_name=rand().'.'.$image->getClientOriginalExtension();
            $img = Image::make($image)->resize(320, 320);
            $img->save("public/images/users/".$new_name);
        }
            $user=User::find(Auth::user()->id);
            $user->image_url=$new_name;
            $user->save();
      return redirect()->route('sub_admin.profile.sub_admin_profile')->with('success','Your Photo Updated Successfully');
    }
}