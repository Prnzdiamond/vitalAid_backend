<?php

use App\Http\Controllers\VitalAid\EventController;
use App\Http\Controllers\VitalAid\UserController;
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
        Route::post('/{id}/end', [ConsultationController::class, 'endConsultation']);
        Route::post('/{id}/takeover', [ConsultationController::class, 'takeOver']);
    });

    Route::get('/events', [EventController::class, 'index']); // Fetch events
    Route::post('/events', [EventController::class, 'store']); // Create event (Admin only)
    Route::post('/events/{eventId}/join', [EventController::class, 'joinEvent']); // Join event
    Route::get('/events/{eventId}/participants', [EventController::class, 'getEventParticipants']); // Fetch event participant
    Route::get('/events/{eventId}', [EventController::class, 'show']); // Fetch single event
    Route::get('/user/events', [EventController::class, 'userEvents']); // Fetch user events
    Route::get('/user/events/created', [EventController::class, 'createdEvents']); // Fetch user created events
    Route::put('/events/{eventId}/update', [EventController::class, 'updateEvent']); // Update event (Owner only)
    Route::delete('/events/{eventId}/delete', [EventController::class, 'deleteEvent']); // Delete event (Owner only)
    Route::post('/events/{eventId}/leave', [EventController::class, 'leaveEvent']); // Add event participant (Admin only)

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/user/notifications', [UserController::class, 'getNotifications']);
    Route::post('/user/notifications/{id}/mark-as-read', [UserController::class, 'markNotificationAsRead']);
    Route::post('/broadcasting/auth', function (Request $request) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        Log::info("✅ Broadcasting auth success", ['user_id' => $user->id]);

        return Broadcast::auth($request);
    });

});




// Debug: Check if authentication works
