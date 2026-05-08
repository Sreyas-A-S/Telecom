<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the user's profile page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Update the user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['nullable', 'required_with:password', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password provided does not match your actual password.');
                }
            }],
            'password' => ['nullable', 'required_with:current_password', 'confirmed', Password::defaults()],
            'profile_pic' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        // Only update password if provided
        if ($request->filled('password') && $request->filled('current_password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_pic')) {
            $path = $request->file('profile_pic')->store('profile-pictures', 'public');
            $user->profile_pic = $path;
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully.']);
    }
}