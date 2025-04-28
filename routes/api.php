<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExampleController;
use App\Http\Controllers\Api\NumberCategoryController;

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

// User route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Number Categorization API
Route::post('/categorize', [NumberCategoryController::class, 'categorize']); 