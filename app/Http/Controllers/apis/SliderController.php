<?php
namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Slider;
class SliderController extends Controller
{

    public function add_slider(Request $request){
        $response=array();
        if(empty($request->get('title')) || empty($request->get('description'))){
            $response=['responseCode'=>201,'message'=>"Please fill all fields"];
            return $response;
        }
       
        else{
    	    $slider=new Slider([
        		'title'=>$request->get('title'),
        		'description'=>$request->get('description'),
        		'created_at'=>date('Y-m-d'),
    	    ]);
        
            $slider->save();
            $response=['responseCode'=>200,'message'=>"Slider Created Successfully",'data'=>$slider];
            return $response;
        }
    }
    
    public function show_slider()
    { 
        $response=array();
        
            $slider=Slider::get();
            if(count($slider)){
                $response=['responseCode'=>200,'message'=>'Slider Found','data'=>$slider];
                return $response;
            }else{
                
            $response=['responseCode'=>201,'message'=>'Slider not Found'];
            return $response;
            }
    }
}