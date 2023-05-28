<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Service;
class ServiceController extends Controller
{
    
    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function admin_index(){
        $services=Service::all();
        return view('admin.services.index',compact('services'));
    }


    public function admin_create(){
            return view('admin.services.create');
    }


    public function admin_store(Request $request){
            $this->validate($request,[
		'title'=>'required',
		'heading'=>'required',
		'price'=>'required',
		'body'=>'required',
		'image_url'=>'required','image','max:2048',
	]);
        $image=$request->file('image_url');
        $new_name=rand().'.'.$image->getClientOriginalExtension();
        $image->move(public_path('/images/services/'),$new_name);
		
	  $services=new Service([
		'title'=>$request->get('title'),
		'heading'=>$request->get('heading'),
		'price'=>$request->get('price'),
		'body'=>$request->get('body'),
		'image_url'=>$new_name,
		'created_at'=>date('Y-m-d'),
		'updated_at'=>date('Y-m-d'),
	]);
        $services->save();
        //$request->user()->Service()->create($request->only('title','heading','price','body','image_url','created_at','updated_at'));
        return redirect()->route('admin.services.index')->with('success','Data Added');
    }


    public function admin_edit($id){
        $services=Service::findOrFail($id);
        return view('admin.services.edit',compact('services'));
    }


    public function admin_update(Request $request){	$image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
	if($image!=''){
		 $request->validate([
        'title'=>'required',
		'heading'=>'required',
		'price'=>'required',
		'body'=>'required',
		'new_image_url'=>'required|image|max:2045',
		]);
		$st=explode('.',$image_name); $ext=end($st);            
		$image_name=str_replace($ext,$image->getClientOriginalExtension(),$image_name);
		$image->move(public_path('/images/services/'),$image_name);
        }else{
            $this->validate($request,['title'=>'required',
		'heading'=>'required',
		'price'=>'required',
		'body'=>'required',
		'created_at'=>'required',
		'updated_at'=>'required',
		
		]);
        }         
        $services=Service::findOrFail($request->get('id'));
	    $services->title=$request->get('title');
        $services->heading=$request->get('heading');
        $services->price=$request->get('price');
        $services->body=$request->get('body');
        $services->image_url=$image_name;
	    $services->created_at=date('Y-m-d');
	    $services->updated_at=date('Y-m-d');
	         
        $services->save();
        //$request->user()->Service()->update($request->only('title','heading','price','body','image_url','created_at','updated_at'));
        return redirect()->route('admin.services.index')->with('success','Data Updated');
    }


    public function admin_destroy($id){
        $services=Service::findOrFail($id);$file=public_path('/images/services/'."/".$services->image_url);
        if(file_exists($file)){
                unlink($file);
        }
	$services->delete();
        return redirect()->route('admin.services.index')->with('success','Data Deleted');
    }
}