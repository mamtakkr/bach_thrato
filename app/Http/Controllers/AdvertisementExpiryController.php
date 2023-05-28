<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\AdvertisementExpiry;
use Auth;
use Image;
class AdvertisementExpiryController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }
    
    
    public function index(){
        $advertisement_expiry=AdvertisementExpiry::all();
        return view('super_admin.advertisement_expiry.index',compact('advertisement_expiry'));
    }


    public function create(){
            return view('super_admin.advertisement_expiry.create');
    }


    public function store(Request $request){
            $this->validate($request,[
		'title'=>'required',
		'num_of_days'=>'required',
	]);
	
	   $advertisement_expiry=new AdvertisementExpiry([
		'title'=>$request->get('title'),
		'num_of_days'=>$request->get('num_of_days'),
		'created_at'=>date('Y-m-d'),
	]);
        $advertisement_expiry->save();
        // dd($advertisements); die;
        return redirect()->route('super_admin.advertisement_expiry.index')->with('success','Data Added');
    }


    public function edit($id){
        $advertisement_expiry=AdvertisementExpiry::findOrFail($id);
        return view('super_admin.advertisement_expiry.edit',compact('advertisement_expiry'));
    }


    public function update(Request $request){
        $this->validate($request,[ 
              'title'=>'required',
              'num_of_days'=>'required',
        ]);
             
        $advertisement_expiry=AdvertisementExpiry::findOrFail($request->get('id'));
	    $advertisement_expiry->title=$request->get('title');
	    $advertisement_expiry->num_of_days=$request->get('num_of_days');
        $advertisement_expiry->created_at=date('Y-m-d');
	         
        $advertisement_expiry->save();
        return redirect()->route('super_admin.advertisement_expiry.index')->with('success','Data Updated');
    }


    public function destroy($id){
        $advertisement_expiry=AdvertisementExpiry::findOrFail($id);
	    $advertisement_expiry->delete();
        return redirect()->route('super_admin.advertisement_expiry.index')->with('success','Data Deleted');
    }
}
