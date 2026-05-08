# Travel Allowance Settings System

## Overview
A settings system is available to manage travel allowance rates used for travel expense reimbursements.

## Features Implemented

### 1. Database Structure
- **Table**: `settings`
- **Columns**: `id`, `name`, `description`, `key` (unique), `value`, timestamps

### 2. Settings Available
- **Other Rate (Default)**: Default fallback rate per kilometer
- **Maximum Daily Travel Allowance**: Daily claim limit
- **Walk Rate**: Rate for walking travel
- **Bike Rate**: Rate for bike travel
- **Car Rate**: Rate for car travel
- **Bus Rate**: Rate for bus travel
- **Train Rate**: Rate for train travel

Note: `idle` remains a non-payable state and does not have a configurable rate.

### 3. Files Created/Modified

#### Controllers
- `app/Http/Controllers/SettingController.php`
  - `index()`: Display settings page
  - `update()`: Save settings
  - `getSetting($key)`: Retrieve specific setting

#### Models
- `app/Models/Setting.php`

#### Views
- `resources/views/settings/index.blade.php`
  - Form for managing travel allowance settings
  - Input validation
  - Tooltips for guidance
  - Real-time updates via AJAX

#### Routes
- `GET /settings` - View settings page
- `POST /settings` - Update settings
- `GET /settings/{key}` - Get specific setting value

#### Helpers
- `app/Helpers/SettingsHelper.php`
  - `get_setting($key, $default)`: Retrieve setting value
  - `calculate_travel_allowance($distance, $vehicleType)`: Calculate allowance

#### Seeders
- `database/seeders/TravelAllowanceSettingsSeeder.php`
  - Populates default travel allowance settings

## Usage Examples

### In Controllers/Models
```php
// Get a setting value
$defaultRate = get_setting('travel_allowance_other', 10);

// Calculate travel allowance
$distance = 50; // km
$vehicleType = 'car'; // walk, bike, car, bus, train, other
$allowance = calculate_travel_allowance($distance, $vehicleType);
```

### In Blade Templates
```php
{{ get_setting('travel_allowance_other') }}
```

### In JavaScript/AJAX
```javascript
// Get setting via API
$.get('/settings/travel_allowance_other', function(value) {
    console.log('Other/default rate:', value);
});
```

## Accessing the Settings Page

1. Navigate to: `/settings`
2. Update the travel allowance rates as needed
3. Click `Save Settings`

## Integration with Expense Requests

```php
$distance = $request->distance;
$vehicleType = $request->vehicle_type; // walk, bike, car, bus, train, other, idle

$calculatedAmount = calculate_travel_allowance($distance, $vehicleType);

$maxDaily = get_setting('travel_allowance_max_daily');
if ($maxDaily && $calculatedAmount > $maxDaily) {
    $calculatedAmount = $maxDaily;
}
```

## Database Seeding

```bash
php artisan db:seed --class=TravelAllowanceSettingsSeeder
```

## Notes
- Values are stored as strings in DB.
- Helper functions handle casting.
- Legacy support for `two_wheeler` and `four_wheeler` is retained in helper mapping.
