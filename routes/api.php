<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
    Route::get('/check-authentication', 'checkAuthentication');
});

Route::controller(ListController::class)->group(function () {
    Route::get('/', 'index')->name('lists.index');
    Route::post('/', 'store')->name('lists.store');
    Route::get('/{id}', 'show')->name('lists.show');
    Route::put('/{id}', 'update')->name('lists.update');
    Route::delete('/{id}', 'destroy')->name('lists.destroy');
    Route::post('/set-access', 'setAccess')->name('lists.setAccess');
});