<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Category;
use App\Store;
use App\User;
use Image;
use Auth;
class StoreController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function index(){
        $stores=Store::join('categories','categories.id','=','stores.cat_id')
                        ->where('stores.is_deleted',0)
                        ->select('stores.*','categories.title as cat_title')->paginate(10);
        return view('super_admin.stores.index',compact('stores'));
    }


    public function create(){
            $categories =Category::where('is_deleted',0)->get();
            $users = User::where('user_type', '=', 'merchant')->get();
            return view('super_admin.stores.create',compact('users','categories'));
    }


    public function store(Request $request){
        
        $this->validate($request,[
            'title'=>'required',
            'merchant_id'=>'required',
            'cat_id'=>'required',
            'location'=>'required',
            'location_lat'=>'required',
            'timings'=>'required',
            'image_url'=>'required|image|max:2048',
        ],[
            'location_lat.required' => 'Please enable location in browser first.'
        ]);
        //echo '<pre>'; var_dump($_POST); die;
        $image=$request->file('image_url');
        $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/stores/').$new_name);
        
	  $stores=new Store([
		'title'=>$request->get('title'),
		'merchant_id'=>$request->get('merchant_id'),
		'cat_id'=>$request->get('cat_id'),
		'location'=>$request->get('location'),
		'timings'=>$request->get('timings'),
		'image_url'=>$new_name,
		'created_at'=>date('Y-m-d'),
		'updated_at'=>date('Y-m-d'),
	]);
        $stores->save();
        return redirect()->route('super_admin.stores.index')->with('success','Data Added');
    }


    public function edit($id){
        $stores=Store::findOrFail($id);
        $categories =Category::where('is_deleted',0)->get();
        $merchant = User::where('user_type', '=', 'merchant')->get();
        return view('super_admin.stores.edit',compact('stores'), compact('merchant','categories'));
    }


    public function update(Request $request){
        $image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
        $new_name="";
    if($image!='')
    {
        $this->validate($request,[ 
        		'title'=>'required',
        		'timings'=>'required',
        		'merchant_id'=>'required',
        		'location'=>'required',
        		'location_lat'=>'required',
        		'cat_id'=>'required',
		        'new_image_url'=>'required|image|max:2045',
        ],[
            'location_lat.required' => 'Please enable location in browser first.'
        ]);
        $new_name=rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/stores/').$new_name);
        }else{
            $this->validate($request,[
        		'title'=>'required',
        		'location'=>'required',
        		'location_lat'=>'required',
        		'merchant_id'=>'required',
        		'cat_id'=>'required',
        		'timings'=>'required',
		
            ],[
                'location_lat.required' => 'Please enable location in browser first.'
            ]);
        }        
        $store=Store::findOrFail($request->get('id'));
    	$store->title=$request->get('title');
    	$store->merchant_id=$request->get('merchant_id');
    	$store->cat_id=$request->get('cat_id');
        $store->location=$request->get('location');
    	$store->location_lat=$request->get('location_lat');
        $store->location_long=$request->get('location_long');
        $store->timings=$request->get('timings');
        if(isset($image)){
        $store->image_url=$new_name;
        }
        $store->created_at=date('Y-m-d');
	    $store->updated_at=date('Y-m-d');
	         
        $store->save();
        return redirect()->route('super_admin.stores.index')->with('success','Data Updated');
    }


    public function destroy($id){
        $store=Store::where('id',$id)->update(['is_deleted'=>1]);
        // $file=public_path('/images/stores/'."/".$store->image_url);
        // if(file_exists($file)){
        //         unlink($file);
        // }
	   // $store->delete();
        return redirect()->route('super_admin.stores.index')->with('success','Data Deleted');
    }
}