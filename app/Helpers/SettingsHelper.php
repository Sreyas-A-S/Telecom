<?php

use App\Models\Setting;

if (!function_exists('get_setting')) {
    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get_setting($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}

if (!function_exists('calculate_travel_allowance')) {
    /**
     * Calculate travel allowance based on distance and vehicle type
     *
     * @param float $distance Distance in kilometers
     * @param string $vehicleType Vehicle type from visits (idle, walk, bike, car, bus, train, other)
     * @return float
     */
    function calculate_travel_allowance($distance, $vehicleType = 'other')
    {
        $vehicleType = strtolower((string) $vehicleType);

        // Preserve idle as non-payable travel state.
        if ($vehicleType === 'idle') {
            return 0.0;
        }

        // Backward compatibility for legacy vehicle names.
        $legacyMap = [
            'two_wheeler' => 'bike',
            'four_wheeler' => 'car',
        ];
        if (isset($legacyMap[$vehicleType])) {
            $vehicleType = $legacyMap[$vehicleType];
        }

        $vehicleRateKeys = [
            'walk' => 'travel_allowance_walk',
            'bike' => 'travel_allowance_bike',
            'car' => 'travel_allowance_car',
            'bus' => 'travel_allowance_bus',
            'train' => 'travel_allowance_train',
            'other' => 'travel_allowance_other',
        ];

        $defaultRate = (float) get_setting('travel_allowance_other', get_setting('travel_allowance_rate', 0));
        $rateKey = $vehicleRateKeys[$vehicleType] ?? 'travel_allowance_other';
        $rate = (float) get_setting($rateKey, $defaultRate);
        $maxDaily = (float) get_setting('travel_allowance_max_daily', 0);

        $amount = $distance * $rate;

        // Apply daily limit if set
        if ($maxDaily > 0 && $amount > $maxDaily) {
            $amount = $maxDaily;
        }

        return round($amount, 2);
    }
}
