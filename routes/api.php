<?php

use App\Http\Controllers\BookMarkController;
use App\Http\Controllers\CafeShopController;
use App\Http\Controllers\RateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group([
    'middleware' => 'api',
    'prefix' => 'shop'

], function () {
    Route::get('/',[CafeShopController::class,'index']);
    Route::post('/store',[CafeShopController::class,'store']);
    Route::post('/update/{id}',[CafeShopController::class,'update']);
    Route::get('/show/{id}',[CafeShopController::class,'show']);
    Route::delete('/delete/{id}',[CafeShopController::class,'destroy']);
    Route::post('/search',[CafeShopController::class,'searchShop']);
    Route::get('/date',[CafeShopController::class,'testDate']);
    Route::post('/rate',[RateController::class,'getRate']);
    Route::post('/createRate',[RateController::class,'createRate']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'bookmark'

], function () {
    Route::post('/create',[BookMarkController::class,'create']);
  
   Route::post('/delete',[BookMarkController::class,'delete']);
   
  
});


