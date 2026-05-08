<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationSettingController extends Controller
{
    public function index()
    {
        // Retrieve current settings or set defaults for the view
        $settings = [
            'organization_name' => Setting::firstOrCreate(['key' => 'organization_name'], ['value' => 'Admiro Tech'])->value,
            'organization_address' => Setting::firstOrCreate(['key' => 'organization_address'], ['value' => '123 Business Street'])->value,
            'organization_phone' => Setting::firstOrCreate(['key' => 'organization_phone'], ['value' => '+91 98765 43210'])->value,
            'organization_website' => Setting::firstOrCreate(['key' => 'organization_website'], ['value' => 'www.admirotech.com'])->value,
            'organization_logo' => Setting::firstOrCreate(['key' => 'organization_logo'], ['value' => ''])->value,
        ];

        return view('settings.organization', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_address' => 'required|string|max:500',
            'organization_phone' => 'required|string|max:50',
            'organization_website' => 'nullable|string|max:255',
            'organization_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('organization_logo')) {
            $oldLogo = Setting::where('key', 'organization_logo')->value('value');
            if ($oldLogo && str_starts_with($oldLogo, 'storage/')) {
                $oldStoragePath = substr($oldLogo, strlen('storage/'));
                if (Storage::disk('public')->exists($oldStoragePath)) {
                    Storage::disk('public')->delete($oldStoragePath);
                }
            }

            $storedPath = $request->file('organization_logo')->store('organization-logo', 'public');
            $data['organization_logo'] = 'storage/' . $storedPath;
        } else {
            unset($data['organization_logo']);
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'name' => str_replace('_', ' ', $key)] // storing a human friendly name too
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Organization details updated successfully.']);
        }

        return redirect()->back()->with('success', 'Organization details updated successfully.');
    }
}
