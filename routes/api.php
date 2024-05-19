<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;

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

Route::post('login', [UserController::class,'login']);
Route::post('register', [UserController::class,'register']);

Route::prefix('oauth/register')->group(function () {
    Route::get('', [GoogleAuthController::class, 'redirect']);
    Route::get('call-back', [GoogleAuthController::class, 'callbackGoogle']);
});

Route::middleware(['admin.jwt'])->group(function () {
    Route::prefix('products')->group(function () {
        Route::post('', [ProductsController::class,'create']);
        Route::get('', [ProductsController::class,'read']);
        Route::put('{id}', [ProductsController::class,'update']);
        Route::delete('{id}', [ProductsController::class,'delete']);
    });

    Route::prefix('categories')->group(function () {
        Route::post('', [CategoriesController::class,'create']);
        Route::get('', [CategoriesController::class,'read']);
        Route::put('{id}', [CategoriesController::class,'update']);
        Route::delete('{id}', [CategoriesController::class,'delete']);
    });
});
