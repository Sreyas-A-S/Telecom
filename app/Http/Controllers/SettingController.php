<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        $canLogTravelHistory = Schema::hasTable('travel_allowance_histories');
        $changedBy = Auth::id();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        foreach ($validated['settings'] as $settingData) {
            $existing = Setting::where('key', $settingData['key'])->first();
            $oldValue = $existing?->value;

            Setting::updateOrCreate(
                ['key' => $settingData['key']],
                [
                    'value' => $settingData['value'],
                    'name' => $settingData['name'] ?? null,
                    'description' => $settingData['description'] ?? null,
                ]
            );

            if (
                $canLogTravelHistory &&
                str_starts_with($settingData['key'], 'travel_allowance_') &&
                (string) ($oldValue ?? '') !== (string) ($settingData['value'] ?? '')
            ) {
                DB::table('travel_allowance_histories')->insert([
                    'setting_key' => $settingData['key'],
                    'old_value' => $oldValue,
                    'new_value' => $settingData['value'],
                    'changed_by' => $changedBy,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'success' => true
        ]);
    }

    public function getSetting($key)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : null;
    }
}
