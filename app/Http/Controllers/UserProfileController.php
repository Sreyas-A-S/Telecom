<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('employee.department', 'employee.role', 'employee.dealership', 'employee.zone', 'employee.reporter');

        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_pic' => 'nullable|string',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($request->profile_pic) {
            if ($user->profile_pic) {
                Storage::disk('public')->delete($user->profile_pic);
            }
            $data = $request->profile_pic;
            $image_parts = explode(';base64,', $data);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1] ?? 'png';
            $image_base64 = base64_decode($image_parts[1]);
            $fileName = 'profile_pictures/' . uniqid() . '.' . $image_type;
            Storage::disk('public')->put($fileName, $image_base64);
            $user->profile_pic = $fileName;
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully.']);
    }
}
