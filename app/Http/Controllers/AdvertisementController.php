<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Advertisement;
use App\AdvertisementExpiry;
use Auth;
use Image;
class AdvertisementController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function index(){
        $advertisements=Advertisement::where(['is_deleted'=>0,'sub_admin_id'=>Auth::user()->id])->get();
        return view('sub_admin.advertisements.index',compact('advertisements'));
    }


    public function create(){
            $advertisement_expiries=AdvertisementExpiry::all();
            return view('sub_admin.advertisements.create',compact('advertisement_expiries'));
    }


    public function store(Request $request){
            $this->validate($request,[
// 		'title'=>'required',
		'image_url'=>'required|image|max:2048',
		'location'=>'required',
	]);
        $image=$request->file('image_url');
        $new_name=rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(320,320);
        $img->save(public_path('/images/advertisements/').$new_name);
        
        $advertisement_expiries=AdvertisementExpiry::where('id',$request->ad_expriy_id)->get();
        $days=$advertisement_expiries[0]->num_of_days;
        $date = new \DateTime(date('Y-m-d'));
        $date->add(new \DateInterval('P'.$days.'D'));
        $expiry_date=$date->format('Y-m-d H:i:s');
        
	   $advertisements=new Advertisement([
// 		'title'=>$request->get('title'),
		'image_url'=>$new_name,
		'link'=>$request->get('link'),
		'location'=>$request->get('location'),
		'sub_admin_id'=>Auth::user()->id,
		'expiry_date'=>$expiry_date,
		'created_at'=>date('Y-m-d'),
		'updated_at'=>date('Y-m-d'),
	]);
        $advertisements->save();
        
        return redirect()->route('sub_admin.advertisements.index')->with('success','Data Added');
    }
    

    public function edit($id){
        $advertisements=Advertisement::findOrFail($id);
        $advertisement_expiries=AdvertisementExpiry::all();
        return view('sub_admin.advertisements.edit',compact('advertisements','advertisement_expiries'));
    }


    public function update(Request $request){
        $image_name=$request->old_image_url;
        $image=$request->file('new_image_url');
        $new_name="";
    if($image!='')
    {
        $this->validate($request,[ 
            //   'title'=>'required',
		      'new_image_url'=>'required|image|max:2045',
              'location'=>'required',
        ]);
        $new_name=rand().'.'.$image->getClientOriginalExtension();
        $img = Image::make($image)->resize(320,320);
        $img->save(public_path('/images/advertisements/').$new_name);
        }else{
            $this->validate($request,[
            //   'title'=>'required',
              'location'=>'required',
		]);
        }     
        
        $advertisement_expiries=AdvertisementExpiry::where('id',$request->ad_expriy_id)->get();
        $days=$advertisement_expiries[0]->num_of_days;
        $date = new \DateTime(date('Y-m-d'));
        $date->add(new \DateInterval('P'.$days.'D'));
        $expiry_date=$date->format('Y-m-d H:i:s');
        
        $advertisements=Advertisement::findOrFail($request->get('id'));
	   // $advertisements->title=$request->get('title');
        if(isset($image)){
            $advertisements->image_url=$new_name;
        }
        $advertisements->link=$request->get('link');
        $advertisements->location=$request->get('location');
		$advertisements->sub_admin_id=Auth::user()->id;
		$advertisements->expiry_date=$expiry_date;
        $advertisements->created_at=date('Y-m-d');
	    $advertisements->updated_at=date('Y-m-d');
	         
        $advertisements->save();
        return redirect()->route('sub_admin.advertisements.index')->with('success','Data Updated');
    }


    public function destroy($id){
        $advertisements=Advertisement::where('id',$id)->update(['is_deleted'=>1]);
    //     $file=public_path('/images/$advertisements/'."/".$advertisements->image_url);
    //     if(file_exists($file)){
    //             unlink($file);
    //     }
    // 	$advertisements->delete();
        return redirect()->route('sub_admin.advertisements.index')->with('success','Data Deleted');
    }
    
    
    public function sub_admin_ads_status_edit(Request $request){
        // echo $request->status; die;
        Advertisement::where('id',$request->id)->update(['status'=>$request->status]);
    
        return redirect()->route('sub_admin.advertisements.index');
    }
 
    
    public function sub_admin_ads_update_status(Request $request){
       
        return Advertisement::where('id',$request->id)->update([
            'status'=>($request->status)
        ]);
    }
}
