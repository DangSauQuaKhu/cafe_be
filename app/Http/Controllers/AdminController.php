<?php

namespace App\Http\Controllers;

use App\Models\CafeShop;
use App\Models\Rate;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserBookmark;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class AdminController extends Controller
{
    public function getSubAdmin()
    {
        if(Auth::check())
        {
            $userid = Auth::id();
        }
        else{
            return response()->json(['error'=> true,'message'=>"Login to Continue"]);
        }
        $users = User::where('role','=','1')->paginate(5);
        return $users;
    }
    public function getUser()
    {
        if(Auth::check())
        {
            $userid = Auth::id();
        }
        else{
            return response()->json(['error'=> true,'message'=>"Login to Continue"]);
        }
        $users = User::where('role','=','2')->paginate(5);
        return $users;
    }
   public function deleteUser(Request $request)
   {
    if(Auth::check())
    {
        $userid = Auth::id();
    }
    else{
        return response()->json(['error'=> true,'message'=>"Login to Continue"]);
    }
       User::where('id','=',$request->id)->delete();
       CafeShop::where('user_id','=',$request->id)->delete();
       Rate::where('user_id','=',$request->id)->delete();
       UserBookmark::where('user_id','=',$request->id)->delete();
   }
   public function registerSubAdmin(Request $request) {
    if(Auth::check())
    {
        $userid = Auth::id();
    }
    else{
        return response()->json(['error'=> true,'message'=>"Login to Continue"]);
    }
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|between:2,100',
        'email' => 'required|string|email|max:100|unique:users',
        'password' => 'required|string|min:6',
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $user = User::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)],['role'=>1]
            ));

    return response()->json([
        'message' => 'User successfully registered',
        'user' => $user
    ], 201);
}
 public function approve(Request $request)
 {
    if(Auth::check())
    {
        $userid = Auth::id();
    }
    else{
        return response()->json(['error'=> true,'message'=>"Login to Continue"]);
    }
    $shop = CafeShop::find($request->cafeShop_id);
    $dataInsert = [
        'approve' => 1
    ];
    // echo $dataInsert['photoURL'];
    $shop->update($dataInsert);
    return $shop;

 }

}
