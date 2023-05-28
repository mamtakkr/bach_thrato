<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Category;
use Image;
class CategoryController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function index(){
        $categories=Category::where('is_deleted',0)->orderBy('id','asc')->get();
        return view('super_admin.categories.index',compact('categories'));
    }


    public function create(){
            return view('super_admin.categories.create');
    }


    public function store(Request $request){
            $this->validate($request,[
		'title'=>'required',
		'title_es'=>'required',
		'title_pt'=>'required',
		'image_url'=>'required|image|max:2048',
	]);
        $image=$request->file('image_url');
        $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/categories/').$new_name);
        
	  $categories=new Category([
		'title'=>$request->get('title'),
		'title_es'=>$request->get('title_es'),
		'title_pt'=>$request->get('title_pt'),
		'image_url'=>$new_name,
		'created_at'=>date('Y-m-d'),
		'updated_at'=>date('Y-m-d'),
	]);
        $categories->save();
        return redirect()->route('super_admin.categories.index')->with('success','Data Added');
    }


    public function edit($id){
        $categories=Category::findOrFail($id);
        return view('super_admin.categories.edit',compact('categories'));
    }


    public function update(Request $request){
        $image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
        $new_name="";
    if($image!='')
    {
        $this->validate($request,[ 
              'title'=>'required',
              'title_es'=>'required',
              'title_pt'=>'required',
		      'new_image_url'=>'required|image|max:2045',
        ]);
        
        $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/categories/').$new_name);
        }else{
            $this->validate($request,[
                'title'=>'required',
                'title_es'=>'required',
                'title_pt'=>'required',
		
		]);
        } 
                
        $categories=Category::findOrFail($request->get('id'));
    	$categories->title=$request->get('title');
    	$categories->title_es=$request->get('title_es');
    	$categories->title_pt=$request->get('title_pt');
    	if(isset($image)){
        $categories->image_url=$new_name;
        }
        $categories->created_at=date('Y-m-d');
    	$categories->updated_at=date('Y-m-d');
	         
        $categories->save();
        return redirect()->route('super_admin.categories.index')->with('success','Data Updated');
    }


    public function destroy($id){
        $categories=Category::where('id',$id)->update(['is_deleted'=>1]); 
    //     $file=public_path('/images/categories'."/".$categories->image_url);
    //     if(file_exists($file)){
    //             unlink($file);
    //     }
    // 	$categories->delete();
        return redirect()->route('super_admin.categories.index')->with('success','Data Deleted');
    }
}
