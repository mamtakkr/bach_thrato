<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Setting;
use App\User;
class SettingController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function index(){
        $settings=Setting::all();
        return view('super_admin.settings.index',compact('settings'));
    }


    public function create(){
            return view('super_admin.settings.create');
    }


    public function store(Request $request){
            $this->validate($request,[
		'near_by_store_distance'=>'required',
		'site_title'=>'required',
		'support_email'=>'required',
		'contact'=>'required',
		'address1'=>'required',
		'address2'=>'required',
		'num_of_free_products'=>'required',
	]);
	  $settings=new Setting([
		'near_by_store_distance'=>$request->get('near_by_store_distance'),
		'site_title'=>$request->get('site_title'),
		'support_email'=>$request->get('support_email'),
		'contact'=>$request->get('contact'),
		'address1'=>$request->get('address1'),
		'address2'=>$request->get('address2'),
		'num_of_free_products'=>$request->get('num_of_free_products'),
		'created_at'=>date('Y-m-d'),
		'updated_at'=>date('Y-m-d'),
	]);
        $settings->save();
        return redirect()->route('super_admin.settings.index')->with('success','Data Added');
    }


    public function edit($id){
        $settings=Setting::findOrFail($id);
        return view('super_admin.settings.edit',compact('settings'));
    }


    public function update(Request $request){
           
        $settings=Setting::findOrFail($request->get('id'));
       
       
        if($settings->num_of_free_products!=$request->get('num_of_free_products')){
            User::where('no_of_products',$settings->num_of_free_products)->update(['no_of_products'=>$request->get('num_of_free_products')]);
        }
        if($settings->num_of_free_stores!=$request->get('num_of_free_stores')){
            User::where('no_of_stores',$settings->num_of_free_stores)->update(['no_of_stores'=>$request->get('num_of_free_stores')]);
        }
        $settings->near_by_store_distance=$request->get('near_by_store_distance');
        $settings->site_title=$request->get('site_title');
        $settings->support_email=$request->get('support_email');
        $settings->contact=$request->get('contact');
        $settings->address1=$request->get('address1');
        $settings->address2=$request->get('address2');
        $settings->num_of_free_products=$request->get('num_of_free_products');
        $settings->num_of_free_stores=$request->get('num_of_free_stores');
        $settings->created_at=date('Y-m-d');
	    $settings->updated_at=date('Y-m-d');
	         
	   
        $settings->save();
        
        return redirect()->route('super_admin.settings.index')->with('success','Data Updated');
    }


    public function destroy($id){
        $settings=Setting::findOrFail($id);
	$settings->delete();
        return redirect()->route('super_admin.settings.index')->with('success','Data Deleted');
    }
    
    
    public function term_condition_index(){
        $settings=Setting::select('id','term_condition')->get();
        return view('super_admin.settings.term_condition.index',compact('settings'));
    }


    public function term_condition_edit($id){
        $settings=Setting::findOrFail($id);
        return view('super_admin.settings.term_condition.edit',compact('settings'));
    }


    public function term_condition_update(Request $request){
           
        $settings=Setting::findOrFail($request->get('id'));
	    $settings->term_condition=$request->get('term_condition');
	    $settings->updated_at=date('Y-m-d');
	         
        $settings->save();
        return redirect()->route('super_admin.settings.term_condition.index')->with('success','Data Updated');
    }

}
