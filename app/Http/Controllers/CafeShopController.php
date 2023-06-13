<?php

namespace App\Http\Controllers;

use App\Http\Resources\CafeShopResource;
use App\Models\CafeShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CafeShopController extends Controller
{
    public function index()
    {
        //
        $shops = CafeShop::paginate(1);
        foreach($shops as $shop)
        {
            $star = DB::table('rate')
            ->selectRaw('ROUND(AVG(`rate`.`star`) ,1) AS `star`')
            ->where("cafeShop_id","=",$shop->id)->first();
            $shop->star= $star->star;
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
            $ext = $file->getClientOriginalName();
            $filename =  "/ShopImage/".time() . '.' . $ext;
            $file->move('C:/xampp/htdocs/chillcafe/chillcafe-fe/public/ShopImage/', $filename);
            $Shop->photoUrl = $filename;
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
        $avg = DB::table('rate')
        ->selectRaw('ROUND(AVG(`rate`.`star`) ,1) AS `star`')
        ->where("cafeShop_id","=",$id)->first();
        $shop->star = $avg->star;
        return new CafeShopResource($shop);
        // return $avg;
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
            $ext = $file->getClientOriginalName();
            $filename = "/ShopImage/".time() . '.' . $ext;
            $file->move('C:/xampp/htdocs/chillcafe/chillcafe-fe/public/ShopImage/', $filename);
            $newShop->photoUrl = $filename;
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
                $shops = DB::table('rate')
                    ->Join('cafe_shops', 'cafe_shops.id', '=', 'rate.cafeShop_id')
                    ->selectRaw('`cafe_shops`.*, `rate`.`cafeShop_id`,ROUND(AVG(`rate`.`star`) ,1) AS `star`')
                    ->groupByRaw('cafeShop_id')
                    ->having('star', '>=', $keyword->star)
                    ->where(
                        [
                            ['name', 'like', "%$keyword->name%"],
                            ['city', 'like', "%$keyword->address%"]
                        ]
                    )->paginate(1);
            } else {
                $shops = DB::table('cafe_shops')
                    ->where(
                        [
                            ['name', 'like', "%$keyword->name%"],
                            ['city', 'like', "%$keyword->address%"]
                        ]
                    )->paginate(1);
            }
        } else {
            if ($keyword->star != null) {
                $shops = DB::table('rate')
                    ->Join('cafe_shops', 'cafe_shops.id', '=', 'rate.cafeShop_id')
                    ->selectRaw('`cafe_shops`.*, `rate`.`cafeShop_id`, ROUND(AVG(`rate`.`star`) ,1) AS `star`')
                    ->groupByRaw('cafeShop_id')
                    ->having('star', '>=', $keyword->star)
                    ->where(
                        [
                            ['name', 'like', "%$keyword->name%"],
                            ['city', 'like', "%$keyword->address%"],
                            ['air_conditioner', '=', $keyword->air_conditioner]
                        ]
                    )->paginate(1);
            } else {
                $shops = DB::table('cafe_shops')
                    ->where(
                        [
                            ['name', 'like', "%$keyword->name%"],
                            ['city', 'like', "%$keyword->address%"],
                            ['air_conditioner', '=', $keyword->air_conditioner]
                        ]
                    )->paginate(1);
            }
        }
        
        return $shops;
    }
}
