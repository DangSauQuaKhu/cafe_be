<?php

namespace App\Http\Controllers;

use App\Models\UserBookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class BookMarkController extends Controller
{
    public function create(Request $request)
    {
        $rule = array(
            'cafeShop_id' => 'required|integer',
        );
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return $validator->errors();
        }
     
        $dataInsert = [
            'cafeShop_id'=>$request->cafeShop_id,
            'user_id'=>3
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
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            
            return response()->json(['error'=> true,'message'=>$validator->errors()]);
        }
        $bookmark = UserBookmark::where(
            [
                ['cafeShop_id', '=', $request->cafeShop_id],
                ['user_id', '=', 3]
            ]
        )->delete();
    
        // return $request->cafeShop_id;
        return response()->json(['status'=>"unbookmark successfully!"]);
       
       
    }
}
