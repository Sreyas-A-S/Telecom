<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session; // Assuming checkMenu uses Session

class DistrictController extends Controller
{
    public function getDistrictsByState(Request $request, $stateName)
    {


        $state = State::where('name', $stateName)->first();

        if (!$state) {
            return response()->json([], 404); // State not found
        }

        $districts = $state->districts()->get(['id', 'name']);

        return response()->json($districts);
    }
}
