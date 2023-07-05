<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookMarkController;
use App\Http\Controllers\CafeShopController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\AuthController;
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
    Route::get('/', [CafeShopController::class, 'index']);
    Route::post('/store', [CafeShopController::class, 'store']);
    Route::post('/update/{id}', [CafeShopController::class, 'update']);
    Route::get('/show/{id}', [CafeShopController::class, 'show']);
    Route::post('/delete/{id}', [CafeShopController::class, 'destroy']);
    Route::post('/search', [CafeShopController::class, 'searchShop']);
    Route::post('/rate', [RateController::class, 'getRate']);
    Route::post('/createRate', [RateController::class, 'createRate']);
    Route::get('/unapprove', [CafeShopController::class, 'getUnApprove']);
    Route::get('/subShop', [CafeShopController::class, 'subAdminCafe']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'bookmark'

], function () {
    Route::post('/create', [BookMarkController::class, 'create']);

    Route::post('/delete', [BookMarkController::class, 'delete']);
    Route::get('/getall', [BookMarkController::class, 'getBookMark']);
    Route::post('/bookmarksearch', [BookMarkController::class, 'searchBookMark']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::post('/change-pass', [AuthController::class, 'changePassWord']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'admin'

], function () {
    Route::get('/getsubAdmin', [AdminController::class, 'getSubAdmin']);
    Route::get('/getUser', [AdminController::class, 'getUser']);
    Route::post('/delete', [AdminController::class, 'deleteUser']);
    Route::post('/addsubadmin', [AdminController::class, 'registerSubAdmin']);
    Route::post('/approve', [AdminController::class, 'approve']);
});
