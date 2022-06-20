<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

include __DIR__ . "/apiX.php";

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

// public routes
Route::get('/auth/me', [AuthController::class, 'me']);

// authenticated routes
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/token/create', [TokenController::class, 'create']);
});


Route::get('/{any}', function () {
  return response()->json(['message' => 'Not found'], 404);
})->where('any', '.*');
