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

Route::get('/login', [LoginController::class, 'login'])->name('login');

Route::post('/login', [LoginController::class, 'authenticate'])->name('login');

Route::middleware('auth:sanctum')->get('/info', [LoginController::class, 'info'])->name('info_about_user');

Route::middleware('auth:sanctum')->get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth:sanctum')->get('/test', function (Request $request) {
   return response()->json([
       'message' => 'You are logged.',
       'admin' => \Illuminate\Support\Facades\Auth::user()->tokenCan('admin'),
       'client' => \Illuminate\Support\Facades\Auth::user()->tokenCan('client')
   ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
