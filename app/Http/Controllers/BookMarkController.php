<?php

namespace App\Http\Controllers;

use App\Models\UserBookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class BookMarkController extends Controller
{
    public function create(Request $request)
    {
        if(Auth::check())
        {
            $userid = Auth::id();
        }
        else{
            return response()->json(['error'=> true,'message'=>"Login to Continue"]);
        }
        $rule = array(
            'cafeShop_id' => 'required|integer',
        );
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return $validator->errors();
        }
     
        $dataInsert = [
            'cafeShop_id'=>$request->cafeShop_id,
            'user_id'=>$userid
        ];
        $newBookmark = UserBookmark::create($dataInsert);
        // echo $dataInsert['photoURL'];
        return $newBookmark;
    }
    public function delete(Request $request)
    {
        $rule = array(
            'cafeShop_id' => 'required|integer',
        );
        if(Auth::check())
        {
            $userid = Auth::id();
        }
        else{
            return response()->json(['error'=> true,'message'=>"Login to Continue"]);
        }
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            
            return response()->json(['error'=> true,'message'=>$validator->errors()]);
        }
        $bookmark = UserBookmark::where(
            [
                ['cafeShop_id', '=', $request->cafeShop_id],
                ['user_id', '=',  $userid]
            ]
        )->delete();
    
        // return $request->cafeShop_id;
        return response()->json(['status'=>"unbookmark successfully!"]);
       
       
    }
    public function getBookMark()
    {
        if(Auth::check())
        {
            $userid = Auth::id();
        }
        else{
            return response()->json(['error'=> true,'message'=>"Login to Continue"]);
        }
        $bookmark = UserBookmark::where(
            [
                ['user_id', '=',  $userid]
            ]
        )->paginate(4);
        return $bookmark;
    }
}
