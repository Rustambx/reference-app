<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetHolderController;
use App\Http\Controllers\Api\SwiftController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\TreasuryAccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);
Route::post('refresh',  [AuthController::class, 'refresh']);

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/swift/import', [SwiftController::class, 'import']);
    Route::resource('swift', SwiftController::class);
    Route::resource('budget-holders', BudgetHolderController::class);
    Route::resource('treasury-accounts', TreasuryAccountController::class);


    Route::get('/test', [TestController::class, 'index']);
});
