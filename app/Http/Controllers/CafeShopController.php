<?php

namespace App\Http\Controllers;

use App\Http\Resources\CafeShopResource;
use App\Models\CafeShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;  
use Carbon\Carbon;

class CafeShopController extends Controller
{
    public function index()
    {
        //
        $shops = CafeShop::paginate(3);
        foreach($shops as $shop)
        {
            $star = DB::table('rates')
            ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
            ->where("cafeShop_id","=",$shop->id)->first();
            $shop->star= $star->star;
            $shop->isOpen = $this->testDate($shop->time_open,$shop->time_close);
        }
       

        return CafeShopResource::collection($shops);
    }
    public function store(Request $request)
    {
        $rule = array(
            'name' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'phone_number' => 'required|string',
            'time_open' => 'required|string',
            'time_close' => 'required|string',
            'air_conditioner' => 'required|between:0,1',
            'total_seats' => 'required|integer|min:1',
            'empty_seats' => 'required|integer|min:0',
            'user_id' => 'required|integer',
        );
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return $validator->errors();
        }
        $Shop = new CafeShop();
        if ($request->hasFile('image')) {  // echo "have file";
            $file = $request->file('image');
            $filePath = $file->store('images', 's3');
        Storage::disk('s3')->setVisibility($filePath, 'public');
        $Shop->photoUrl = Storage::disk('s3')->url($filePath);
        }
        $dataInsert = [
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
            'phone_number' => $request->phone_number,
            'time_open' => $request->time_open,
            'time_close' => $request->time_close,
            'air_conditioner' => $request->air_conditioner,
            'total_seats' => $request->total_seats,
            'empty_seats' => $request->empty_seats,
            'user_id' => $request->user_id,
            'photoUrl' => $Shop->photoUrl
        ];
        $newShop = CafeShop::create($dataInsert);
        // echo $dataInsert['photoURL'];
        return $newShop;
    }
    public function show($id)
    {
        $shop = CafeShop::findOrFail($id);
        $avg = DB::table('rates')
        ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
        ->where("cafeShop_id","=",$id)->first();
        $shop->star = $avg->star;
        $bookmark = DB::table('user_bookmarks')
        ->selectRaw('`user_id`')
        ->where([["cafeShop_id","=",$id],["user_id","=",3]])->count();
        if($bookmark==0)
        $shop->bookmark = false;
        else
        $shop->bookmark = true;
        $shop->isOpen = $this->testDate($shop->time_open,$shop->time_close);


        return new CafeShopResource($shop);
         
    }
    public function update(Request $request, $id)
    {
        $shoptUpdate = CafeShop::find($id);

        $rule = array(
            'name' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'phone_number' => 'required|string',
            'time_open' => 'required|string',
            'time_close' => 'required|string',
            'air_conditioner' => 'required|between:0,1',
            'total_seats' => 'required|integer|min:1',
            'empty_seats' => 'required|integer|min:0',
            'user_id' => 'required|integer',
        );
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return $validator->errors();
        }
        $newShop = new CafeShop();
        if ($request->hasFile('image')) {  // echo "have file";
            $file = $request->file('image');
            $filePath = $file->store('images', 's3');
        Storage::disk('s3')->setVisibility($filePath, 'public');
        $newShop->photoUrl = Storage::disk('s3')->url($filePath);
        }
        $dataInsert = [
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
            'phone_number' => $request->phone_number,
            'time_open' => $request->time_open,
            'time_close' => $request->time_close,
            'air_conditioner' => $request->air_conditioner,
            'total_seats' => $request->total_seats,
            'empty_seats' => $request->empty_seats,
            'user_id' => $request->user_id,
            'photoUrl' => $newShop->photoUrl
        ];
        // echo $dataInsert['photoURL'];
        $shoptUpdate->update($dataInsert);
        return $shoptUpdate;
    }
    public function destroy($id)
    {
        // if(Auth::check())
        // {
        //     $userid = Auth::id();
        // }
        // else{
        //     return response()->json(['error'=> true,'message'=>"Login to Continue"]);
        // }
        $shopDelete = CafeShop::find($id);
        // if($shopDelete->user->id != $userid ) return response()->json(['error' =>true,'message'=> 'Unauthorized'], 401);

        $shopDelete->delete();
        return $id;
    }
    public function searchShop(Request $keyword)
    {
        if ($keyword->air_conditioner == null) {
            if ($keyword->star != null) {
               $shops = $this->nullAirHaveStar($keyword);
            } else {
                $shops = $this->nullAirNoStar($keyword);
            }
        } else {
            if ($keyword->star != null) {
                $shops= $this->haveAirHaveStar($keyword);
                
            } else {
                $shops= $this->haveAirNoStar($keyword);
            }
        }
        foreach($shops as $shop)
        {
            $star = DB::table('rates')
            ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
            ->where("cafeShop_id","=",$shop->id)->first();
            $shop->star= $star->star;
            $shop->isOpen = $this->testDate($shop->time_open,$shop->time_close);

        }
       
        
        return $shops;
    }
    public function haveAirNoStar($keyword)
    {
        $mytime = Carbon::now('GMT+7')->format('H:i');
        // $mytime = explode(":", $mytime);
        if($keyword->air_conditioner==0)
        {
            $shops = DB::table('cafe_shops')
            ->where(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['city', 'like', "%$keyword->address%"],
                    ['air_conditioner', '=', $keyword->air_conditioner],
                ]
            )
            ->orWhere(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['city', 'like', "%$keyword->address%"],
                    ['time_close', '<', $mytime],
                ]
               
            )
            ->orWhere(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['city', 'like', "%$keyword->address%"],
                    ['time_open', '>', $mytime],
                ]
              
            )
            ->paginate(3);
        }
        else
        {
            $shops = DB::table('cafe_shops')
            ->where(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['city', 'like', "%$keyword->address%"],
                    ['air_conditioner', '=', $keyword->air_conditioner],
                
                ]
            )
            ->whereRaw('time_close >= ? and time_open <= ?', [$mytime,$mytime])
            ->paginate(3);
        }
       
                    return $shops;
    }
    public function haveAirHaveStar($keyword)
    {
        $mytime = Carbon::now('GMT+7')->format('H:i');
   
        if($keyword->air_conditioner==0)
        {
            $shops = DB::table('rates')
            ->Join('cafe_shops', 'cafe_shops.id', '=', 'rates.cafeShop_id')
            ->selectRaw('`cafe_shops`.*, `rates`.`cafeShop_id`, ROUND(AVG(`rates`.`star`) ,1) AS `star`')
            ->groupByRaw('cafeShop_id')
            ->having('star', '>=', $keyword->star)
            ->where(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['city', 'like', "%$keyword->address%"],
                    ['air_conditioner', '=', $keyword->air_conditioner]
                ]
            )
            ->orWhere(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['city', 'like', "%$keyword->address%"],
                    ['time_close', '<', $mytime],
                ]
               
            )
            ->orWhere(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['city', 'like', "%$keyword->address%"],
                    ['time_open', '>', $mytime],
                ]
              
            )->paginate(3);
        }
        else
        {
            $shops = DB::table('rates')
            ->Join('cafe_shops', 'cafe_shops.id', '=', 'rates.cafeShop_id')
            ->selectRaw('`cafe_shops`.*, `rates`.`cafeShop_id`, ROUND(AVG(`rates`.`star`) ,1) AS `star`')
            ->groupByRaw('cafeShop_id')
            ->having('star', '>=', $keyword->star)
            ->where(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['city', 'like', "%$keyword->address%"],
                    ['air_conditioner', '=', $keyword->air_conditioner]
                ]
            )->whereRaw('time_close >= ? and time_open <= ?', [$mytime,$mytime])
            ->paginate(3);
        }
        
       
                    return $shops;
    }
    public function nullAirNoStar($keyword)
    {
        $shops = DB::table('cafe_shops')
                    ->where(
                        [
                            ['name', 'like', "%$keyword->name%"],
                            ['city', 'like', "%$keyword->address%"]
                        ]
                    )->paginate(3);
        return $shops;
    }
    public function nullAirHaveStar($keyword)
    {
        $shops = DB::table('rates')
        ->Join('cafe_shops', 'cafe_shops.id', '=', 'rates.cafeShop_id')
        ->selectRaw('`cafe_shops`.*, `rates`.`cafeShop_id`,ROUND(AVG(`rates`.`star`) ,1) AS `star`')
        ->groupByRaw('cafeShop_id')
        ->having('star', '>=', $keyword->star)
        ->where(
            [
                ['name', 'like', "%$keyword->name%"],
                ['city', 'like', "%$keyword->address%"]
            ]
        )->paginate(3);
        return $shops;
    }
    public function testDate($time1, $time2)
    { 
        $mytime = Carbon::now('GMT+7')->format('H:i');
        $mytime = explode(":", $mytime);
        $time1 = explode(":",$time1);
        $time2 = explode(":",$time2);
        if($time1[0]<$mytime[0] || ($time1[0]==$mytime[0]&&$time1[1]<=$mytime[1]))
        {
            if($time2[0]>$mytime[0] || ($time2[0]==$mytime[0]&&$time2[1]>=$mytime[1]))
            return true;
        }
        return false;
    }
}
