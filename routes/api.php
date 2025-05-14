<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\VitalAid\CommunityMember;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\VitalAid\AuthController;
use App\Http\Controllers\VitalAid\UserController;
use App\Http\Controllers\VitalAid\EventController;
use App\Http\Controllers\VitalAid\DonationController;
use App\Http\Controllers\VitalAid\CommunityController;
use App\Http\Controllers\VitalAid\DashboardController;
use App\Http\Controllers\VitalAid\ConsultationController;
use App\Http\Controllers\VitalAid\EventReactionController;
use App\Http\Controllers\VitalAid\DonationRequestController;

/*
|--------------------------------------------------------------------------
| VitalAid API Routes
|--------------------------------------------------------------------------
|
| Here is where you register API routes for the VitalAid platform.
| These are grouped by functionality and properly documented.
|
*/

/**
 * Public (Guest) Routes
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/webhook/paystack', 'App\Http\Controllers\VitalAid\PaystackWebhookController@handleWebhook');
Route::prefix('donations')->group(function () {
    // Put the verification endpoint here
    Route::get('/verify/{id}', 'App\Http\Controllers\VitalAid\DonationController@verify');
});

/**
 * Protected Routes - Requires Sanctum Authentication
 */
Route::middleware('auth:sanctum')->group(function () {

    /**
     * Authenticated User Routes
     */
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    /**
     * Notifications
     */
    Route::get('/user/notifications', [UserController::class, 'getNotifications']);
    Route::post('/user/notifications/{id}/mark-as-read', [UserController::class, 'markNotificationAsRead']);
    Route::get('/dashboard', [UserController::class, 'index']);


    /**
     * Broadcasting Auth
     */
    Route::post('/broadcasting/auth', function (Request $request) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        Log::info("✅ Broadcasting auth success", ['user_id' => $user->id]);

        return Broadcast::auth($request);
    });

    /**
     * Consultations
     */
    Route::prefix('consultations')->group(function () {
        Route::post('/request', [ConsultationController::class, 'requestConsultation']);
        Route::get('/history', [ConsultationController::class, 'consultationHistory']);
        Route::get('/expert', [ConsultationController::class, 'expertConsultations']);
        Route::get('/{id}', [ConsultationController::class, 'getConsultation']);
        Route::post('/{id}/accept', [ConsultationController::class, 'acceptConsultation']);
        Route::post('/{id}/message', [ConsultationController::class, 'sendMessage']);
        Route::post('/{id}/end', [ConsultationController::class, 'endConsultation']);
        Route::post('/{id}/takeover', [ConsultationController::class, 'takeOver']);
        Route::post('/{id}/follow-up', [ConsultationController::class, 'requestFollowUp']);
        Route::post('/{id}/accept-follow-up', [ConsultationController::class, 'acceptFollowUp']);
        Route::post('/{id}/rate', [ConsultationController::class, 'rateConsultation']);

    });

    /**
     * Events
     */
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']); // Admin only
        Route::get('/search', [EventController::class, 'search']);
        // User-specific event views
        Route::get('/user/attending', [EventController::class, 'userEvents']);
        Route::get('/user/created', [EventController::class, 'createdEvents']);
        Route::get('/{eventId}', [EventController::class, 'show']);
        Route::put('/{eventId}/update', [EventController::class, 'updateEvent']); // Owner only
        Route::delete('/{eventId}/delete', [EventController::class, 'deleteEvent']); // Owner only
        Route::post('/{eventId}/join', [EventController::class, 'joinEvent']);
        Route::post('/{eventId}/leave', [EventController::class, 'leaveEvent']);
        Route::get('/{eventId}/participants', [EventController::class, 'getEventParticipants']);
        // Event completion
        Route::post('{eventId}/complete', [EventController::class, 'completeEvent']);

        // Event reactions
        Route::post('{eventId}/reactions', [EventReactionController::class, 'addReaction']);
        Route::get('{eventId}/reactions', [EventReactionController::class, 'getEventReactions']);
        Route::delete('{eventId}/reactions', [EventReactionController::class, 'deleteReaction']);
        Route::get('{eventId}/reaction-summary', [EventReactionController::class, 'getReactionSummary']);


    });

    /**
     * Donations & Donation Requests
     */
    Route::prefix('donations')->group(function () {
        // Donation requests (for orgs)
        Route::get('/request', [DonationRequestController::class, 'index']);
        Route::post('/request', [DonationRequestController::class, 'store']);
        Route::get('/request/{id}', [DonationRequestController::class, 'show']);
        Route::patch('/request/{id}', [DonationRequestController::class, 'update']);
        Route::delete('/request/{id}', [DonationRequestController::class, 'destroy']);
        Route::get('/organization/requests', [DonationRequestController::class, 'getOrganizationRequests']);
        Route::get('/request/{id}/donations', [DonationRequestController::class, 'getRequestDonations']);
        Route::get('/request/mark-as-complete/{id}', [DonationRequestController::class, 'markAsCompleted']);

        // Donations
        Route::post('/donate', [DonationController::class, 'donate']);
        Route::get('/user', [UserController::class, 'userDonations']);
        Route::get('/organization/{id}', [DonationController::class, 'getOrganizationDonations']);
    });

    Route::prefix('withdrawals')->group(function () {
        Route::post('/request', 'App\Http\Controllers\VitalAid\WithdrawalRequestController@requestWithdrawal');
        Route::get('/', 'App\Http\Controllers\VitalAid\WithdrawalRequestController@list');
        Route::get('/all', 'App\Http\Controllers\VitalAid\WithdrawalRequestController@listAll');
        Route::get('/banks', 'App\Http\Controllers\VitalAid\WithdrawalRequestController@getBanks');
        Route::get('/check/{id}', 'App\Http\Controllers\VitalAid\WithdrawalRequestController@checkStatus');
    });


    // Community
    Route::prefix('community')->group(function () {
        // List all communities (public endpoint)
        Route::get('/list', [CommunityController::class, 'listCommunities']);

        Route::get('/my/list', [CommunityController::class, 'myCommunitiesList']);
        // Get single community details (public endpoint)
        Route::post('/notify-members', [CommunityController::class, 'notifyCommunityMembers']);
        Route::get('/{communityId}', [CommunityController::class, 'getCommunity']);

        // Get a list of communities the authenticated user is a member of

        // Join a community
        Route::post('/{communityId}/join', [CommunityController::class, 'joinCommunity']);

        // Leave a community
        Route::post('/{communityId}/leave', [CommunityController::class, 'leaveCommunity']);

        // Get community members
        Route::get('/{communityId}/members', [CommunityController::class, 'getCommunityMembers']);

        // Send notification to community members (community admins only)
    });

});
