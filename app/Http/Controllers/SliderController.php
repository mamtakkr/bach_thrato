<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Slider;
class SliderController extends Controller
{
    
    public function __construct(){
        $this->middleware('auth');
    }
    
    public function admin_index(){
        $sliders=Slider::all();
        return view('admin.sliders.index',compact('sliders'));
    }


    public function admin_create(){
            return view('admin.sliders.create');
    }


    public function admin_store(Request $request){
        $this->validate($request,[
    		'title'=>'required',
    		'image_url'=>'required',
    		'description'=>'required',
    	]);
    	$image=$request->file('image_url');
        $new_name=rand().'.'.$image->getClientOriginalExtension();
        $image->move(public_path('images/sliders/'),$new_name);
    	  $sliders=new Slider([
    		'title'=>$request->get('title'),
    		'image_url'=>$new_name,
    		'description'=>$request->get('description'),
    		'created_at'=>date('Y-m-d'),
    		'updated_at'=>date('Y-m-d'),
    	]);
        $sliders->save();
        //$request->user()->Slider()->create($request->only('title','image_url','description','created_at','updated_at'));
        return redirect()->route('admin.sliders.index')->with('success','Data Added');
    }


    public function admin_edit($id){
        $sliders=Slider::findOrFail($id);
        return view('admin.sliders.edit',compact('sliders'));
    }


    public function admin_update(Request $request){	
        $image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
	    if($image!=''){
		 $this->validate($request,[
		       'title'=>'required',
		        'new_image_url'=>'required|image|max:2045',
		        'description'=>'required',
		]);
		$st=explode('.',$image_name); $ext=end($st);            
		$image_name=str_replace($ext,$image->getClientOriginalExtension(),$image_name);
		$image->move('public/images/sliders',$image_name);
        }else{
            $this->validate($request,['title'=>'required',
    		'description'=>'required',
    		'created_at'=>'required',
    		'updated_at'=>'required',
		]);
        }         
        $sliders=Slider::findOrFail($request->get('id'));
	    $sliders->title=$request->get('title');
        $sliders->image_url=$image_name;
	    $sliders->description=$request->get('description');
        $sliders->created_at=date('Y-m-d');
	    $sliders->updated_at=date('Y-m-d');
	             
        $sliders->save();
        //$request->user()->Slider()->update($request->only('title','image_url','description','created_at','updated_at'));
        return redirect()->route('admin.sliders.index')->with('success','Data Updated');
    }


    public function admin_destroy($id){
        $sliders=Slider::findOrFail($id);
	    $sliders->delete();
        return redirect()->route('admin.sliders.index')->with('error','Data Deleted');
    }
}
