<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session; // Import Session facade
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            log_action('User logged in successfully. User ID: ' . Auth::id());

                        $user = Auth::user();  
                        // dd($user->employee); // Debugging line to check the authenticated user

            if ($user->user_type === 'employee') {
                $employeeRoleId = Employee::where('employee_id', $user->employee->employee_id)->value('role_id');
                // dd($employeeRoleId);
                if ($employeeRoleId) {
                    Session::put('role_id', $employeeRoleId);
                } else {
                    Session::put('role_id', 0);
                }
            } else {
                Session::put('role_id', 0);
            }

            return redirect()->intended('dashboard');
        }

        $user = \App\Models\User::where('email', $request->input('email'))->first();

        if (!$user) {
            log_action('Failed login attempt: Email not found for ' . $request->input('email'));
            return back()->withErrors([
                'email' => 'The provided email address is not registered.',
            ])->onlyInput('email');
        }

        if (!Hash::check($request->input('password'), $user->password)) {
            log_action('Failed login attempt: Incorrect password for ' . $request->input('email'));
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ])->onlyInput('email');
        }

        // If user exists and password is correct, but Auth::attempt failed, it might be due to inactive user
        // Assuming 'status' field exists in User model and 'active' means user can log in
        if ($user->status === 'inactive') { // Adjust 'status' field name and 'inactive' value as per your User model
            log_action('Failed login attempt: Inactive user ' . $request->input('email'));
            return back()->withErrors([
                'email' => 'Your account is inactive. Please contact support.',
            ])->onlyInput('email');
        }

        log_action('Failed login attempt for email: ' . $request->input('email'));
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.', // Fallback for other issues
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        log_action('User logged out. User ID: ' . Auth::id());
        Auth::logout();
        Session::forget('user_role_id');

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
