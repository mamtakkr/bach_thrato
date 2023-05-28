<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Transition;
use Image;
class TransitionController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function merchant_index(){
        $transitions=Transition::where(['type'=>'merchant','is_deleted'=>0])->get();
        return view('super_admin.merchant_transitions.index',compact('transitions'));
    }


    public function merchant_create(){
            return view('super_admin.merchant_transitions.create');
    }


    public function merchant_store(Request $request){
            $this->validate($request,[
		'title'=>'required',
		'image_url'=>'required|image|max:2048',
		'description'=>'required',
	]);
        $image=$request->file('image_url');
        $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/transitions/merchant_transitions/').$new_name);
        
	  $transitions=new Transition([
		'title'=>$request->get('title'),
		'image_url'=>$new_name,
		'description'=>$request->get('description'),
		'type'=>'merchant',
		'created_at'=>date('Y-m-d'),
		'updated_at'=>date('Y-m-d'),
	]);
        $transitions->save();
        return redirect()->route('super_admin.merchant_transitions.index')->with('success','Data Added');
    }


    public function merchant_edit($id){
        $transitions=Transition::findOrFail($id);
        return view('super_admin.merchant_transitions.edit',compact('transitions'));
    }


    public function merchant_update(Request $request){
        $image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
        $new_name="";
    if($image!='')
    {
        $this->validate($request,[ 
              'title'=>'required',
		      'new_image_url'=>'required|image|max:2045',
              'description'=>'required',
        ]);
        $new_name=rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/transitions/merchant_transitions/').$new_name);
        }else{
            $this->validate($request,[
                'title'=>'required',
		        'description'=>'required',
		
		]);
        } 
        $transitions=Transition::findOrFail($request->get('id'));
    	$transitions->title=$request->get('title');
        if(isset($image)){
        $transitions->image_url=$new_name;
        }
        $transitions->description=$request->get('description');
        $transitions->created_at=date('Y-m-d');
	    $transitions->updated_at=date('Y-m-d');
	    
        $transitions->save();
        return redirect()->route('super_admin.merchant_transitions.index')->with('success','Data Updated');
    }


    public function merchant_destroy($id){
        $transitions=Transition::where('id',$id)->update(['is_deleted'=>1]);
    //     $file=public_path('/images/transitions/merchant_transitions/'."/".$transitions->image_url);
    //     if(file_exists($file)){
    //             unlink($file);
    //     }
	   // $transitions->delete();
        return redirect()->route('super_admin.merchant_transitions.index')->with('success','Data Deleted');
    }
    
    
    public function client_index(){
        $transitions=Transition::where(['type'=>'client','is_deleted'=>0])->get();
        return view('super_admin.client_transitions.index',compact('transitions'));
    }


    public function client_create(){
            return view('super_admin.client_transitions.create');
    }


    public function client_store(Request $request){
            $this->validate($request,[
		'title'=>'required',
		'image_url'=>'required|image|max:2048',
		'description'=>'required',
	]);
        $image=$request->file('image_url');
        $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/transitions/client_transitions/').$new_name);
        
	  $transitions=new Transition([
		'title'=>$request->get('title'),
		'image_url'=>$new_name,
		'description'=>$request->get('description'),
		'type'=>'client',
		'created_at'=>date('Y-m-d'),
		'updated_at'=>date('Y-m-d'),
	]);
        $transitions->save();
        return redirect()->route('super_admin.client_transitions.index')->with('success','Data Added');
    }


    public function client_edit($id){
        $transitions=Transition::findOrFail($id);
        return view('super_admin.client_transitions.edit',compact('transitions'));
    }


    public function client_update(Request $request){
        $image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
        $new_name="";
    if($image!='')
    {
        $this->validate($request,[ 
              'title'=>'required',
		      'new_image_url'=>'required|image|max:2045',
              'description'=>'required',
        ]);
        $new_name=rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/transitions/client_transitions/').$new_name);
        }else{
            $this->validate($request,[
                'title'=>'required',
		        'description'=>'required',
		
		]);
        } 
        $transitions=Transition::findOrFail($request->get('id'));
    	$transitions->title=$request->get('title');
        if(isset($image)){
        $transitions->image_url=$new_name;
        }
        $transitions->description=$request->get('description');
        $transitions->created_at=date('Y-m-d');
	    $transitions->updated_at=date('Y-m-d');
	    
        $transitions->save();
        return redirect()->route('super_admin.client_transitions.index')->with('success','Data Updated');
    }


    public function client_destroy($id){
        $transitions=Transition::where('id',$id)->update(['is_deleted'=>1]);
    //     $file=public_path('/images/transitions/client_transitions/'."/".$transitions->image_url);
    //     if(file_exists($file)){
    //             unlink($file);
    //     }
	   // $transitions->delete();
        return redirect()->route('super_admin.client_transitions.index')->with('success','Data Deleted');
    }
}
