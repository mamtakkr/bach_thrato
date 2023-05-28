<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\DefaultNotification;
use App\User;

class DefaultNotificationController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function index(){
        $default_notifications=DefaultNotification::orderBy('id','DESC')->get();
        return view('super_admin.default_notifications.index',compact('default_notifications'));
    }
    
    public function get_notification($key,$user_id=null){
        $default_language_code="es";
        if(!empty($user_id)){
            $default_language_code=User::find($user_id)->current_language_code;
        }
        $value=DefaultNotification::where(['key'=>$key])->first();
        return $value->$default_language_code;
        
    }


    public function create(){
            return view('super_admin.default_notifications.create');
    }


    public function store(Request $request){
            $this->validate($request,[
		'key'=>'required',
		'en'=>'required',
	]);
	
	  $default_notifications=new DefaultNotification([
		'key'=>$request->get('key'),
		'en'=>$request->get('en'),
		'es'=>$request->get('es'),
		'pt'=>$request->get('pt'),
	]);
        $default_notifications->save();
        return redirect()->route('super_admin.default_notifications.index')->with('success','Data Added');
    }


    public function edit($id){
        $default_notifications=DefaultNotification::findOrFail($id);
        return view('super_admin.default_notifications.edit',compact('default_notifications'));
    }


    public function update(Request $request){
        
        $this->validate($request,[ 
        	'en'=>'required',
        ]);
        
        $default_notifications=DefaultNotification::findOrFail($request->get('id'));
    	$default_notifications->key=$request->get('key');
    	$default_notifications->en=$request->get('en');
    	$default_notifications->es=$request->get('es');
    	$default_notifications->pt=$request->get('pt');
       
        $default_notifications->save();
        return redirect()->route('super_admin.default_notifications.index')->with('success','Data Updated');
    }


    public function destroy($id){
        $default_notifications=DefaultNotification::findOrFail($id); 
    //     $file=public_path('/images/categories'."/".$categories->image_url);
    //     if(file_exists($file)){
    //             unlink($file);
    //     }
    	$categories->delete();
        return redirect()->route('super_admin.default_notifications.index')->with('success','Data Deleted');
    }
}
