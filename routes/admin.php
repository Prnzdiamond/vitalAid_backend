<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminDonationController;
use App\Http\Controllers\Admin\AdminConsultationController;
use App\Http\Controllers\Admin\AdminCommunityController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Admin\AdminReportController;

// Admin Routes



Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.welcome');
    });

    // Admin welcome page (accessible without authentication)
    Route::get('/welcome', [AdminAuthController::class, 'showWelcome'])->name('welcome');

    // Guest admin routes (not authenticated)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    // Authenticated admin routes
    Route::middleware(['auth:admin', 'admin'])->group(function () {
        // Dashboard routes
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/monthly-stats', [AdminController::class, 'getMonthlyStats'])->name('monthly-stats');

        // Profile and authentication routes
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [AdminAuthController::class, 'showProfile'])->name('profile');
        Route::put('/profile', [AdminAuthController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [AdminAuthController::class, 'changePassword'])->name('password.change');

        // User Management Routes
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/export', [AdminUserController::class, 'export'])->name('export');
            Route::post('/bulk-action', [AdminUserController::class, 'bulkAction'])->name('bulk-action');
        });

        // Verification Management Routes
        Route::prefix('verifications')->name('verifications.')->group(function () {
            Route::get('/', [AdminVerificationController::class, 'index'])->name('index');
            Route::get('/statistics', [AdminVerificationController::class, 'statistics'])->name('statistics');
            Route::post('/bulk-reject', [AdminVerificationController::class, 'bulkReject'])->name('bulk-reject');
            Route::post('/bulk-approve', [AdminVerificationController::class, 'bulkApprove'])->name('bulk-approve');
            Route::get('/{user}', [AdminVerificationController::class, 'show'])->name('show');
            Route::post('/{user}/approve', [AdminVerificationController::class, 'approve'])->name('approve');
            Route::post('/{user}/reject', [AdminVerificationController::class, 'reject'])->name('reject');
            Route::match(['POST', 'PATCH'], '/{user}/reset', [AdminVerificationController::class, 'reset'])->name('reset');
            Route::get('/{user}/document/{documentType}', [AdminVerificationController::class, 'downloadDocument'])->name('download-document');
            Route::get('/{user}/view-document/{documentType}', [AdminVerificationController::class, 'viewDocument'])->name('view-document');
        });

        // Event Management Routes
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('/', [AdminEventController::class, 'index'])->name('index');
            Route::get('/{event}', [AdminEventController::class, 'show'])->name('show');
            Route::get('/{event}/edit', [AdminEventController::class, 'edit'])->name('edit');
            Route::put('/{event}', [AdminEventController::class, 'update'])->name('update');
            Route::delete('/{event}', [AdminEventController::class, 'destroy'])->name('destroy');
            Route::patch('/{event}/status', [AdminEventController::class, 'updateStatus'])->name('update-status');
            Route::get('/{event}/participants', [AdminEventController::class, 'participants'])->name('participants');
            Route::delete('/{event}/participants/{participant}', [AdminEventController::class, 'removeParticipant'])->name('remove-participant');
            Route::post('/bulk-action', [AdminEventController::class, 'bulkAction'])->name('bulk-action');
        });

        // Donation Management Routes
        Route::prefix('donations')->name('donations.')->group(function () {
            Route::get('/', [AdminDonationController::class, 'index'])->name('index');
            Route::get('/analytics', [AdminDonationController::class, 'analytics'])->name('analytics');
            Route::get('/{donation}', [AdminDonationController::class, 'show'])->name('show');
        });

        Route::prefix('donation-requests')->name('donation-requests.')->group(function () {
            Route::get('/', [AdminDonationController::class, 'requestsIndex'])->name('index');
            Route::get('/{donationRequest}', [AdminDonationController::class, 'requestsShow'])->name('show');
            Route::patch('/{donationRequest}/status', [AdminDonationController::class, 'updateRequestStatus'])->name('update-status');
        });

        // Consultation Management Routes
        Route::prefix('consultations')->name('consultations.')->group(function () {
            Route::get('/', [AdminConsultationController::class, 'index'])->name('index');
            Route::get('/analytics', [AdminConsultationController::class, 'analytics'])->name('analytics');
            Route::get('/doctor-performance', [AdminConsultationController::class, 'doctorPerformance'])->name('doctor-performance');
            Route::get('/follow-up-requests', [AdminConsultationController::class, 'followUpRequests'])->name('follow-up-requests');
            Route::get('/{consultation}', [AdminConsultationController::class, 'show'])->name('show');
        });

        // Community Management Routes
        Route::prefix('communities')->name('communities.')->group(function () {
            Route::get('/', [AdminCommunityController::class, 'index'])->name('index');
            Route::get('/analytics', [AdminCommunityController::class, 'analytics'])->name('analytics');
            Route::get('/{community}', [AdminCommunityController::class, 'show'])->name('show');
            Route::get('/{community}/members', [AdminCommunityController::class, 'members'])->name('members');
        });

        // Withdrawal Management Routes
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', [AdminWithdrawalController::class, 'index'])->name('index');
            Route::get('/analytics', [AdminWithdrawalController::class, 'analytics'])->name('analytics');
            Route::get('/{withdrawal}', [AdminWithdrawalController::class, 'show'])->name('show');
            Route::post('/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('approve');
            Route::post('/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('reject');
        });

        // Reports Routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminReportController::class, 'index'])->name('index');
            Route::get('/user-growth', [AdminReportController::class, 'userGrowth'])->name('user-growth');
            Route::get('/platform-usage', [AdminReportController::class, 'platformUsage'])->name('platform-usage');
            Route::get('/financial-summary', [AdminReportController::class, 'financialSummary'])->name('financial-summary');
            Route::get('/verification-stats', [AdminReportController::class, 'verificationStats'])->name('verification-stats');
        });
    });
});