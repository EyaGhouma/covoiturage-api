<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarpoolTripNoticeController;
use App\Http\Controllers\CarPoolTripController;

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/verify-driving-license/{id}', [AuthController::class, 'verifyDrivingLicense']);
Route::middleware('auth:sanctum')->get('/driver/{id}', [AuthController::class, 'getUserWithComments']);

// User route (appliqué le middleware ici pour éviter la duplication)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



// carpoolTrip routes
Route::post('/car-pool-trip-search', [CarPoolTripController::class, 'index']);
Route::get('/car-pool-trip/{id}', [CarPoolTripController::class, 'getById']);
Route::controller(CarPoolTripController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/car-pool-trip', [CarPoolTripController::class, 'store']);
    Route::post('/car-pool-trip-booking-request', [CarPoolTripController::class, 'addBookingRequest']);
    Route::get('/my-booking-request', [CarPoolTripController::class, 'getMyBookingRequests']);
    Route::get('/my-booking', [CarPoolTripController::class, 'getMyBookings']);
    Route::post('/accept-booking/{id}', [CarPoolTripController::class, 'acceptBookingRequest']);
    Route::post('/refuse-booking/{id}', [CarPoolTripController::class, 'refuseBookingRequest']);
    });



// carpoolTrip notices routes
Route::controller(CarpoolTripNoticeController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/car-trip-notice', 'store');
});


