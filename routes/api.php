<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(["prefix"=>"todo", 'middleware' => ['role:user', 'jwt.auth']],function(){
    Route::get("/get/{id}",[TodoController::class,"get"]);
    Route::get("/gets",[TodoController::class,"gets"]);
    Route::post("/store",[TodoController::class,"store"]);
    Route::put("/update/{id}",[TodoController::class,"update"]);
    Route::delete("/delete/{id}",[TodoController::class,"delete"]);
});
Route::group(['middleware' => ['jwt.auth']], function() {
    Route::post('role/create', [RoleController::class, 'create']);
    Route::post('user/create', [UserController::class, 'create']);
});

// auth
Route::post('register', [AuthController::class, 'register']);
Route::post('otp-verify', [AuthController::class, 'tokenVerify']);
Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::group([ 'middleware' => 'jwt.auth'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('me', [AuthController::class, 'me']);

});
