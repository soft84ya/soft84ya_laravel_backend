<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TempImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




Route::post('/authenticate', [AuthenticationController::class, 'authenticate']);

Route::group(['middleware' => ['auth:sanctum']],function(){
    //protected Routes
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/logout', [AuthenticationController::class, 'logout']);
    //service route
    Route::post('/services', [ServiceController::class, 'store']);
    Route::get('services', [ServiceController::class, 'index']);
    Route::put('services/{id}', [ServiceController::class, 'update']);
    Route::get('services/{id}', [ServiceController::class, 'show']);
    Route::delete('services/{id}', [ServiceController::class, 'destroy']);


   // Temp image upload route
   Route::post('temp-images', [TempImageController::class, 'store']);


});
