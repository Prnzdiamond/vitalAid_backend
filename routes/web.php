<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', function () {
    return response()->json(['message' => 'Please login via the frontend'], 401);
})->name('login');