<?php

namespace App\Http\Controllers;

use App\Models\CafeShop;
use App\Models\UserBookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Image;
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
        
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            
            return response()->json(['error'=> true,'message'=>$validator->errors()]);
        }
        if(Auth::check())
        {
            $userid = Auth::id();
        }
        else{
            return response()->json(['error'=> true,'message'=>"Login to Continue"]);
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
        $bookmarks = UserBookmark::where(
            [
                ['user_id', '=',  $userid]
            ]
        )->paginate(3);
        foreach ($bookmarks as $bookmark)
        {
            $bookmark->shop = CafeShop::where("id",'=',$bookmark->cafeShop_id)->first();
            $bookmark->isOpen = $this->testDate($bookmark->shop['time_open'], $bookmark->shop['time_close']);
            $bookmark->photoUrl = Image::where('cafeShop_id', '=', $bookmark->cafeShop_id)->selectRaw('photoUrl')->first()->photoUrl;
            $star = DB::table('rates')
            ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
            ->where("cafeShop_id", "=", $bookmark->cafeShop_id)->first();
            $bookmark->star = $star->star;
        }
        return $bookmarks;
    }
    public function testDate($time1, $time2)
    {
        $mytime = Carbon::now('GMT+7')->format('H:i');
        $mytime = explode(":", $mytime);
        $time1 = explode(":", $time1);
        $time2 = explode(":", $time2);
        if ($time1[0] < $mytime[0] || ($time1[0] == $mytime[0] && $time1[1] <= $mytime[1])) {
            if ($time2[0] > $mytime[0] || ($time2[0] == $mytime[0] && $time2[1] >= $mytime[1]))
                return true;
        }
        return false;
    }
    public function searchBookMark(Request $request)
    {
        $mytime = Carbon::now('GMT+7')->format('H:i');
        $mytime = explode(":", $mytime);
        if(Auth::check())
        {
            $userid = Auth::id();
        }
        else{
            return response()->json(['error'=> true,'message'=>"Login to Continue"]);
        }
        if($request->select==0)
        {
            $bookmarks = $this->getBookMark();
            return $bookmarks;
        }
        if($request->select==1)
        {
            $bookmarks = DB::table('user_bookmarks')
            ->Join('cafe_shops', 'cafe_shops.id', '=', 'user_bookmarks.cafeShop_id')
            ->where(
                [
                    ['user_bookmarks.user_id', '=', $userid],
                   
                ]
            )->whereRaw('(hour(cafe_shops.time_close) >= ? and hour(cafe_shops.time_open) <= ?) 
           
                ', [$mytime[0],$mytime[0]])
            ->paginate(3);
            foreach ($bookmarks as $bookmark)
            {
                $bookmark->shop = CafeShop::where("id",'=',$bookmark->cafeShop_id)->first();
                $bookmark->isOpen = $this->testDate($bookmark->shop['time_open'], $bookmark->shop['time_close']);
                $bookmark->photoUrl = Image::where('cafeShop_id', '=', $bookmark->cafeShop_id)->selectRaw('photoUrl')->first()->photoUrl;
                $star = DB::table('rates')
                ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
                ->where("cafeShop_id", "=", $bookmark->cafeShop_id)->first();
                $bookmark->star = $star->star;
            }
            return $bookmarks;
        }
        if($request->select==2)
        {
            $bookmarks = DB::table('user_bookmarks')
            ->Join('cafe_shops', 'cafe_shops.id', '=', 'user_bookmarks.cafeShop_id')
            ->where(
                [
                    ['user_bookmarks.user_id', '=', $userid],
                    ['air_conditioner', '=', 0],
                ]
            )
            ->paginate(3);
            foreach ($bookmarks as $bookmark)
            {
                $bookmark->shop = CafeShop::where("id",'=',$bookmark->cafeShop_id)->first();
                $bookmark->isOpen = $this->testDate($bookmark->shop['time_open'], $bookmark->shop['time_close']);
                $bookmark->photoUrl = Image::where('cafeShop_id', '=', $bookmark->cafeShop_id)->selectRaw('photoUrl')->first()->photoUrl;
                $star = DB::table('rates')
                ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
                ->where("cafeShop_id", "=", $bookmark->cafeShop_id)->first();
                $bookmark->star = $star->star;
            }
            return $bookmarks;
        }
        if($request->select==3)
        {
            $bookmarks = DB::table('user_bookmarks')
            ->Join('cafe_shops', 'cafe_shops.id', '=', 'user_bookmarks.cafeShop_id')
            ->where(
                [
                    ['user_bookmarks.user_id', '=', $userid],
                    ['air_conditioner', '=', 1],
                ]
            )
            ->paginate(3);
            foreach ($bookmarks as $bookmark)
            {
                $bookmark->shop = CafeShop::where("id",'=',$bookmark->cafeShop_id)->first();
                $bookmark->isOpen = $this->testDate($bookmark->shop['time_open'], $bookmark->shop['time_close']);
                $bookmark->photoUrl = Image::where('cafeShop_id', '=', $bookmark->cafeShop_id)->selectRaw('photoUrl')->first()->photoUrl;
                $star = DB::table('rates')
                ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
                ->where("cafeShop_id", "=", $bookmark->cafeShop_id)->first();
                $bookmark->star = $star->star;
            }
            return $bookmarks;
        }
    }
}
