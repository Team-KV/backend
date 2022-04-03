<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RecordController;
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
    Route::get('/client', [ClientController::class, 'list'])->name('collection_of_clients');

    Route::post('/client', [ClientController::class, 'create'])->name('create_client');

    Route::get('/client/{id}', [ClientController::class, 'detail'])->name('detail_of_client');

    Route::put('/client/{id}', [ClientController::class, 'update'])->name('update_client');

    Route::delete('/client/{id}', [ClientController::class, 'delete'])->name('delete_client');

    Route::post('/client/{id}/user', [ClientController::class, 'createUser'])->name('create_client_user');


    Route::get('/event-type', [EventTypeController::class, 'list'])->name('collection_of_event_types');

    Route::post('/event-type', [EventTypeController::class, 'create'])->name('create_event_type');

    Route::get('/event-type/{id}', [EventTypeController::class, 'detail'])->name('detail_of_event_type');

    Route::put('/event-type/{id}', [EventTypeController::class, 'update'])->name('update_event_type');

    Route::delete('/event-type/{id}', [EventTypeController::class, 'delete'])->name('delete_event_type');


    Route::get('/event', [EventController::class, 'list'])->name('collection_of_events');

    Route::post('/event', [EventController::class, 'create'])->name('create_event');

    Route::get('/event/{id}', [EventController::class, 'detail'])->name('detail_of_event');

    Route::put('/event/{id}', [EventController::class, 'update'])->name('update_event');

    Route::delete('/event/{id}', [EventController::class, 'delete'])->name('delete_event');

    Route::get('/event/{id}/record', [RecordController::class, 'list'])->name('collection_of_records');

    Route::post('/event/{id}/record', [RecordController::class, 'create'])->name('create_record');

    Route::get('/record/{id}', [RecordController::class, 'detail'])->name('detail_of_record');

    Route::put('/record/{id}', [RecordController::class, 'update'])->name('update_record');
});

Route::middleware('localization')->middleware(['auth:sanctum', 'ability:client'])->group(function () {

});
