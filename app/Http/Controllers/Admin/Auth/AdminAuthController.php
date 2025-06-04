<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Models\Admin\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Show admin welcome page
     */
    public function showWelcome()
    {
        // If admin is already logged in, redirect to dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('welcome');
    }

    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        // If admin is already logged in, redirect to dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $admin = Admin::where($loginField, $request->login)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$admin->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Your account is inactive. Please contact the system administrator.'],
            ]);
        }

        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        $admin->updateLastLogin();
        $request->session()->regenerate();

        // Always redirect to admin dashboard after successful login
        return redirect()->route('admin.dashboard')->with('success', 'Welcome back, ' . $admin->first_name . '!');
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.welcome')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show admin profile
     */
    public function showProfile()
    {
        return view('admin.auth.profile');
    }

    /**
     * Update admin profile
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->_id . ',_id',
            'username' => 'required|string|max:255|unique:admins,username,' . $admin->_id . ',_id',
        ]);

        $admin->update($request->only(['first_name', 'last_name', 'email', 'username']));

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Change admin password
     */
    public function changePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password changed successfully.');
    }
}
