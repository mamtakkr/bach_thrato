<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use Hash;
use App\User;
use App\Product;
use App\Order;

class TestController extends Controller
{
    public function search(Request $request)
    {
        $request->query = "";
        $searchResults = User::where('first_name','LIKE','%'.$request->query.'%')
                ->get();
                dd($searchResults);
        return view('search', compact('searchResults'));
    }
    
}