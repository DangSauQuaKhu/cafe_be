<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RateController extends Controller
{
    public function getRate(Request $request)
    {
       $allRate = Rate::where("cafeShop_id","=",$request->cafeShop_id)->paginate(20);
       foreach($allRate as $rate)
       {
           $user =User::where("id","=",$rate->user_id)->first();
           $rate->user = $user;
       }
       return $allRate;
    }
    public function createRate(Request $request)
    {
        $rule = array(
            'cafeShop_id' => 'required|integer',
            'star' =>  'required|integer',
            'content' => 'required|string',
        );
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
          return response()->json(['error'=> true,'message'=>$validator->errors()]);
        }
        $dataInsert = [
            'cafeShop_id' => $request->cafeShop_id,
            'star' => $request->star,
            'content' => $request->content,
            'user_id' => 3
        ];
        $newRate = Rate::create($dataInsert);
        // echo $dataInsert['photoURL'];
        return $newRate;
    }
}
