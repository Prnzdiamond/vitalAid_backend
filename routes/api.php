<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Broadcasting\BroadcastController;
use App\Http\Controllers\VitalAid\AuthController;
use App\Http\Controllers\VitalAid\ConsultationController;


/**
 * Vital Aid Guest Routes
 */



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/*
 * Vital Aid Auth Routes
 *
 * Add your Vital Aid routes here.
 *
 */
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => '/consultations'], function () {
        Route::post('/request', [ConsultationController::class, 'requestConsultation']);
        Route::post('/{id}/accept', [ConsultationController::class, 'acceptConsultation']);
        Route::post('/{id}/message', [ConsultationController::class, 'sendMessage']);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate']);

});




// Debug: Check if authentication works