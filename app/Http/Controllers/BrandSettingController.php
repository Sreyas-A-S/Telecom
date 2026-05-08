<?php

namespace App\Http\Controllers;



use App\Models\Dealership;
use App\Models\DealershipSetting;
use App\Models\Setting;

use Illuminate\Http\Request;

class BrandSettingController extends Controller
{
    public function index($dealership_id = null)
    {
        $dealerships = Dealership::where('brand', true)->get();
        $selectedDealershipId = $dealership_id;
        $setting = Setting::firstOrCreate(
            ['key' => 'task_continuation_approval'],
            [
                'name' => 'task continuation approval',
                'description' => 'Enable or disable task continuation approval.'
            ]
        );
        $dealershipSetting = null;

        if ($selectedDealershipId && $setting) {
            $dealershipSetting = DealershipSetting::where('dealership_id', $selectedDealershipId)
                                                  ->where('setting_id', $setting->id)
                                                  ->first();
        }

        return view('brand-settings.index', compact('dealerships', 'setting', 'dealershipSetting', 'selectedDealershipId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dealership_id' => 'required|exists:dealerships,id',
            'setting_id' => 'required|exists:settings,id',
            'enabled' => 'required|boolean',
        ]);

        DealershipSetting::updateOrCreate(
            [
                'dealership_id' => $request->dealership_id,
                'setting_id' => $request->setting_id,
            ],
            [
                'enabled' => $request->enabled,
            ]
        );

        return redirect()->route('brand-settings.index', ['dealership_id' => $request->dealership_id])
                         ->with('success', 'Setting updated successfully.');
    }

    public function getDealershipSettings(Request $request, $dealership_id)
    {
        $setting = Setting::firstOrCreate(
            ['key' => 'task_continuation_approval'],
            [
                'name' => 'task continuation approval',
                'description' => 'Enable or disable task continuation approval.'
            ]
        );

        $dealershipSetting = DealershipSetting::where('dealership_id', $dealership_id)
                                              ->where('setting_id', $setting->id)
                                              ->first();

        return response()->json([
            'dealershipSetting' => $dealershipSetting,
            'setting' => $setting,
        ]);
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'dealership_id' => 'required|exists:dealerships,id',
            'setting_id' => 'required|exists:settings,id',
            'enabled' => 'required|boolean',
        ]);

        try {
            DealershipSetting::updateOrCreate(
                [
                    'dealership_id' => $request->dealership_id,
                    'setting_id' => $request->setting_id,
                ],
                [
                    'enabled' => $request->enabled,
                ]
            );

            return response()->json(['success' => true, 'message' => 'Setting updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update setting.', 'error' => $e->getMessage()], 500);
        }
    }
}