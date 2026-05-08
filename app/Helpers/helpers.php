<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Permission;
use App\Models\MenuGroup;
use App\Models\Menu;
use Illuminate\Support\Facades\Session;
use App\Models\Log; // Added
use Illuminate\Support\Facades\Request; // Added
use Symfony\Component\HttpFoundation\File\UploadedFile; // Added

use App\Models\User; // Added
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

if (!function_exists('storePublicFile')) {
    /**
     * Store a file using the redundant storage strategy (Storage disk + Public path copy)
     * 
     * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\File $file
     * @param string $directory The directory name (e.g., 'task_followups')
     * @param string|null $filename Optional custom filename
     * @return string The public URL path (e.g., 'storage/task_followups/filename.jpg')
     */
    function storePublicFile($file, $directory, $filename = null)
    {
        if (!$filename) {
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        }

        // Ensure public storage directory exists
        $publicDir = public_path('storage/' . $directory);
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0777, true, true);
        }

        // 1. Store to storage 'public' disk (storage/app/public/$directory)
        $path = Storage::disk('public')->putFileAs($directory, $file, $filename);

        // 2. Exact duplicate at public/storage/$directory
        File::copy(storage_path('app/public/' . $path), public_path('storage/' . $path));

        return 'storage/' . $path;
    }
}

if (!function_exists('calculateDistance')) {
    function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // Distance in meters

        return $distance;
    }
}

if (!function_exists('checkMenuGroup')) {
    function checkMenuGroup($role_id, $menu_group_id)
    {
        // return true; // Temporarily allow all menu groups
        if (!Auth::check()) {
            return false;
        }
        if (Auth::user()->user_type === 'admin') {
            return true;
        }

        $hasPermission = Permission::where('role_id', $role_id)
            ->whereHas('menu', function ($query) use ($menu_group_id) {
                $query->where('menu_group_id', $menu_group_id);
            })
            ->where(function ($query) {
                $query->where('can_create', true)
                    ->orWhere('can_read', true)
                    ->orWhere('can_update', true)
                    ->orWhere('can_delete', true);
            })
            ->count() > 0;

        return $hasPermission;
    }
}
if (!function_exists('checkMenu')) {
    // $user = Auth::id();
    // var_dump($user);
    function checkMenu($role_id, $menu_id, $action)
    {
        // return true;
// echo "Role ID: " . $role_id . ", Menu ID: " . $menu_id . ", Action: " . $action . "\n"; // Debugging line to check input values
        if (!Auth::check()) {
            return false;
        }
        if (Auth::user()->user_type === 'admin') {
            return true;
        }

        $permission = Permission::where('role_id', $role_id)
            ->where('menu_id', $menu_id)
            ->first();

        //    echo($permission); // Debugging line to check the value of $permission

        return $permission ? (bool) $permission->{'can_' . $action} : false;
    }
}

if (!function_exists('log_action')) {
    function log_action($action)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = Request::ip();

        Log::create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'action' => $action,
        ]);
    }
}

if (!function_exists('base64ToFile')) {
    function base64ToFile($base64String, $name = null)
    {
        // Extract the mime type and base64 data from the string
        if (preg_match('/^data:(.*?);base64,(.*)$/', $base64String, $matches)) {
            $fileType = $matches[1];
            $base64Data = $matches[2];
        } else {
            // If no mime type is provided, assume a default or throw an error
            return null;
        }

        $decodedData = base64_decode($base64Data);

        if ($decodedData === false) {
            return null; // Decoding failed
        }

        // Determine file extension from mime type
        $extension = '';
        switch ($fileType) {
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/gif':
                $extension = 'gif';
                break;
            case 'image/svg+xml':
                $extension = 'svg';
                break;
            // Add other types as needed
            default:
                $extension = 'bin'; // Default binary
                break;
        }

        // Create a temporary file
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'base64_');
        file_put_contents($tmpFilePath, $decodedData);

        // Create an UploadedFile instance
        $originalName = $name ?: uniqid() . '.' . $extension;
        $mimeType = $fileType;
        $error = null;
        $test = true; // This indicates that the file is a test file and not a real upload

        return new UploadedFile($tmpFilePath, $originalName, $mimeType, $error, $test);
    }
}
