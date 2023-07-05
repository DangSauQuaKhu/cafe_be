<?php

namespace App\Http\Controllers;

use App\Http\Resources\CafeShopResource;
use App\Models\CafeShop;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class CafeShopController extends Controller
{
    public function index()
    {
        //
        $shops = CafeShop::where('approve', '=', '1')->paginate(3);
        foreach ($shops as $shop) {
            $star = DB::table('rates')
                ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
                ->where("cafeShop_id", "=", $shop->id)->first();
            $shop->star = $star->star;
            $shop->isOpen = $this->testDate($shop->time_open, $shop->time_close);

            $shop->photoUrl = Image::where('cafeShop_id', '=', $shop->id)->selectRaw('photoUrl')->first()->photoUrl;
        }


        return CafeShopResource::collection($shops);
    }
    public function getUnApprove()
    {
        //
        $shops = CafeShop::where('approve', '=', '0')->paginate(3);
        foreach ($shops as $shop) {
            $shop->photoUrl = Image::where('cafeShop_id', '=', $shop->id)->selectRaw('photoUrl')->first()->photoUrl;
        }
        return CafeShopResource::collection($shops);
    }
    public function store(Request $request)
    {
        if (Auth::check()) {
            $userid = Auth::id();
        } else {
            return response()->json(['error' => true, 'message' => "Login to Continue"]);
        }
        $rule = array(
            'name' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required|string',
            'time_open' => 'required|string',
            'time_close' => 'required|string',
            'air_conditioner' => 'required|between:0,1',
        );
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return $validator->errors();
        }
        //  $Shop = new CafeShop();

        $dataInsert = [
            'name' => $request->name,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'time_open' => $request->time_open,
            'time_close' => $request->time_close,
            'air_conditioner' => $request->air_conditioner,
            'user_id' => $userid,
            //    'photoUrl' => $Shop->photoUrl
        ];
        $newShop = CafeShop::create($dataInsert);
        if ($files = $request->file('image')) {
            foreach ($files as $file) {
                $filePath = $file->store('images', 's3');
                Storage::disk('s3')->setVisibility($filePath, 'public');
                $photoUrl = Storage::disk('s3')->url($filePath);
                $data = [
                    'cafeShop_id' => $newShop->id,
                    'photoUrl' => $photoUrl
                ];
                Image::create($data);
            }
        }
        $newShop->photoUrl = Image::where('cafeShop_id', '=', $newShop->id)->selectRaw('photoUrl')->get();
        return $newShop;
    }
    public function show($id)
    {
        if (Auth::check()) {
            $userid = Auth::id();
        } else {
            return response()->json(['error' => true, 'message' => "Login to Continue"]);
        }
        $shop = CafeShop::findOrFail($id);
        $avg = DB::table('rates')
            ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
            ->where("cafeShop_id", "=", $id)->first();
        $shop->star = $avg->star;
        $bookmark = DB::table('user_bookmarks')
            ->selectRaw('`user_id`')
            ->where([["cafeShop_id", "=", $id], ["user_id", "=", $userid]])->count();
        if ($bookmark == 0)
            $shop->bookmark = false;
        else
            $shop->bookmark = true;
        $shop->isOpen = $this->testDate($shop->time_open, $shop->time_close);
        $shop->photoUrl = Image::where('cafeShop_id', '=', $shop->id)->selectRaw('photoUrl')->get();
        $shop->user = User::where('id','=',$shop->user_id)->first();
        return new CafeShopResource($shop);
    }
    public function update(Request $request, $id)
    {
        if (Auth::check()) {
            $userid = Auth::id();
        } else {
            return response()->json(['error' => true, 'message' => "Login to Continue"]);
        }
        $shoptUpdate = CafeShop::where([
            ['user_id', '=', $userid],
            ['id', '=', $id]
        ])->first();

        $rule = array(
            'name' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required|string',
            'time_open' => 'required|string',
            'time_close' => 'required|string',
            'air_conditioner' => 'required|between:0,1',
        );
        $validator =  Validator::make($request->all(), $rule);
        if ($validator->fails()) {
            return $validator->errors();
        }
        Image::where('cafeShop_id','=',$id)->delete();
        if ($files = $request->file('image')) {

            foreach ($files as $file) {
                $filePath = $file->store('images', 's3');
                Storage::disk('s3')->setVisibility($filePath, 'public');
                $photoUrl = Storage::disk('s3')->url($filePath);
                $data = [
                    'cafeShop_id' => $id,
                    'photoUrl' => $photoUrl
                ];
                Image::create($data);
            }
        }
        $dataInsert = [
            'name' => $request->name,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'time_open' => $request->time_open,
            'time_close' => $request->time_close,
            'air_conditioner' => $request->air_conditioner,
            'user_id' => $userid,
        ];
        // echo $dataInsert['photoURL'];
        $shoptUpdate->update($dataInsert);
        $shoptUpdate->photoUrl = Image::where('cafeShop_id', '=', $shoptUpdate->id)->selectRaw('photoUrl')->get();
        return $shoptUpdate;
    }
    public function destroy($id)
    {
        if (Auth::check()) {
            $userid = Auth::id();
        } else {
            return response()->json(['error' => true, 'message' => "Login to Continue"]);
        }
        $shopDelete = CafeShop::where([
            ['id', '=', $id],
            ['user_id', '=', $userid]
        ])->delete();

        // if($shopDelete->user->id != $userid ) return response()->json(['error' =>true,'message'=> 'Unauthorized'], 401);


        return $userid;
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
                $shops = $this->haveAirHaveStar($keyword);
            } else {
                $shops = $this->haveAirNoStar($keyword);
            }
        }
        foreach ($shops as $shop) {
            $star = DB::table('rates')
                ->selectRaw('ROUND(AVG(`rates`.`star`) ,1) AS `star`')
                ->where("cafeShop_id", "=", $shop->id)->first();
            $shop->star = $star->star;
            $shop->isOpen = $this->testDate($shop->time_open, $shop->time_close);
            $shop->photoUrl = Image::where('cafeShop_id', '=', $shop->id)->selectRaw('photoUrl')->first()->photoUrl;
        }


        return $shops;
    }
    public function haveAirNoStar($keyword)
    {
        $shops = DB::table('cafe_shops')
            ->where(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['air_conditioner', '=', $keyword->air_conditioner],
                    ['approve', '=', '1']
                ]
            )->paginate(3);
        return $shops;
    }
    public function haveAirHaveStar($keyword)
    {

        $shops = DB::table('rates')
            ->Join('cafe_shops', 'cafe_shops.id', '=', 'rates.cafeShop_id')
            ->selectRaw('`cafe_shops`.*, `rates`.`cafeShop_id`, ROUND(AVG(`rates`.`star`) ,1) AS `star`')
            ->groupByRaw('cafeShop_id')
            ->having('star', '>=', $keyword->star)
            ->where(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['air_conditioner', '=', $keyword->air_conditioner],
                    ['approve', '=', '1']
                ]
            )
            ->paginate(3);

        return $shops;
    }
    public function nullAirNoStar($keyword)
    {
        $shops = DB::table('cafe_shops')
            ->where(
                [
                    ['name', 'like', "%$keyword->name%"],
                    ['approve', '=', '1']
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
                    ['approve', '=', '1']
                ]
            )->paginate(3);
        return $shops;
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
    public function subAdminCafe()
    {
        if (Auth::check()) {
            $userid = Auth::id();
        } else {
            return response()->json(['error' => true, 'message' => "Login to Continue"]);
        }
        $shops = CafeShop::where(
            [
                ['user_id', '=', $userid]
            ]
        )->paginate(3);
        foreach ($shops as $shop) {
            $shop->photoUrl = Image::where('cafeShop_id', '=', $shop->id)->selectRaw('photoUrl')->first()->photoUrl;
        }
        return CafeShopResource::collection($shops);
    }
}
