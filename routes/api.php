<?php

use App\Http\Controllers\LoginController;
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

Route::middleware('localization')->group(function () {
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login');
});

Route::middleware('localization')->middleware('auth:sanctum')->group(function () {
    Route::get('/info', [LoginController::class, 'info'])->name('info_about_user');

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware('localization')->middleware(['auth:sanctum', 'ability:admin'])->group(function () {

});

Route::middleware('localization')->middleware(['auth:sanctum', 'ability:client'])->group(function () {

});
