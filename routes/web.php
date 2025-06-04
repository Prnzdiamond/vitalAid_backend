<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

// General welcome page (for regular users)
Route::get('/', function () {
    // Redirect to frontend if this is meant for regular users
    return redirect(env('FRONTEND_URL'));
})->name('home');

// Regular user login redirect to frontend
Route::get('/login', function () {
    return redirect(env('FRONTEND_URL'));
})->name('user.login');

// Admin specific routes
require __DIR__ . '/admin.php';
