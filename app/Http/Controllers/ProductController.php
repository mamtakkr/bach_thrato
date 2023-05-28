<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Product;
use App\Store;
use DB;
use App\User;
use Image;
use Auth;
class ProductController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function index(){
        $products=Product::where('is_deleted',0)->paginate(25);
        return view('super_admin.products.index',compact('products'));
    }
    
    public function getmerchantstore(Request $request)
    {
        $subcat = DB::table("stores")
        ->where("merchant_id",$request->merchantid)
        ->pluck("title","id");
     
        return response()->json($subcat);
    }


    public function create(){
            $merchant = User::where('user_type', '=', 'merchant')->get();
            $stores = Store::where('is_deleted',0)->get();
            // dd($stores);
            return view('super_admin.products.create',compact('merchant','stores'));
    }


    public function store(Request $request){
            $this->validate($request,[
        		'image_url'=>'required|image|max:2048',
        		'title'=>'required',
        		'store_id'=>'required',
        		'merchant_id'=>'required',
        		'price'=>'required',
        		'quantity'=>'required',
        // 		'code'=>'required',
        		'description'=>'required',
        	]);
            $image=$request->file('image_url');
            $new_name=str_replace(" ", "-",$request->get('title'))." ".rand().'.'.$image->getClientOriginalExtension();
            $img = Image::make($image)->resize(600,650);
            $image->move('public/images/products',$new_name);
            
    	  $products=new Product([
    		'image_url'=>$new_name,
    		'title'=>$request->get('title'),
    		'merchant_id'=>$request->get('merchant_id'),
    		'store_id'=>$request->get('store_id'),
    		'price'=>$request->get('price'),
    		'description'=>$request->get('description'),
    		'quantity'=>$request->get('quantity'),
    		'code'=>$request->get('code'),
    		'created_at'=>date('Y-m-d'),
    		'updated_at'=>date('Y-m-d'),
    	]);
        $products->save();
        return redirect()->route('super_admin.products.index')->with('success','Data Added');
    }


    public function edit($id){
        $products=Product::findOrFail($id);
        $merchant = User::where('user_type', '=', 'merchant')->get();
        $stores = Store::findOrFail($products->store_id); 
         
        return view('super_admin.products.edit',compact('products','merchant','stores'));
    }


    public function update(Request $request){ 
        $image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
        $new_name="";
    if($image!=''){
		 $this->validate($request,[
		      'title'=>'required',
    		  'new_image_url'=>'required|image|max:2045',
    		  'price'=>'required',
    		  'description'=>'required',
    		  'quantity'=>'required',
    		  //'code'=>'required',
		]);
        $new_name=str_replace(" ", "-",$request->get('title'))."-|-".rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(600,650);
        $img->save(public_path('/images/products/').$new_name);
        }else{
            $this->validate($request,[
                'title'=>'required',
        		'price'=>'required',
        		'description'=>'required',
        		'quantity'=>'required',
		      //  'code'=>'required'
		]);
        }        
                
        $products=Product::findOrFail($request->get('id'));
        $products->title=$request->get('title');
        $products->merchant_id=$request->get('merchant_id');
        $products->store_id=$request->get('store_id');
        $products->price=$request->get('price');
        if(isset($image)){
        $products->image_url=$new_name;
        }
        $products->description=$request->get('description');
        $products->quantity=$request->get('quantity');
        $products->code=$request->get('code');
	    $products->updated_at=date('Y-m-d');
	         
        $products->save();
        return redirect()->route('super_admin.products.index')->with('success','Data Updated');
    }


    public function destroy($id){
        $products=Product::where('id',$id)->update(['is_deleted'=>1]);
    //     $file=public_path('/images/products/'."/".$products->image_url);
    //     if(file_exists($file)){
    //             unlink($file);
    //     }
	   // $products->delete();
        return redirect()->route('super_admin.products.index')->with('success','Data Deleted');
    }
    
}
