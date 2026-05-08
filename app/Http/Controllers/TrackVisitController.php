<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserGpsTrace;

class TrackVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getDataTableData(Request $request)
    {
        $query = UserGpsTrace::with('user');

        // Apply search filters
        if ($request->has('search') && !empty($request->input('search')['value'])) {
            $searchValue = $request->input('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('remarks', 'like', '%' . $searchValue . '%')
                  ->orWhereHas('user', function ($qr) use ($searchValue) {
                      $qr->where('name', 'like', '%' . $searchValue . '%');
                  });
            });
        }

        // dd($query->toSql() , $query->getBindings());

        // Get total records before pagination
        $totalRecords = $query->count();
        

        // Apply sorting
        if ($request->has('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            $columns = $request->input('columns');
            $orderColumnName = $columns[$orderColumnIndex]['data'];

            // Map datatable column names to database column names if necessary
            if ($orderColumnName === 'user.name') {
                $query->join('users', 'user_gps_traces.user_id', '=', 'users.id')
                      ->orderBy('users.name', $orderDirection)
                      ->select('user_gps_traces.*'); // Select user_gps_traces.* to avoid column ambiguity
            } else {
                $query->orderBy($orderColumnName, $orderDirection);
            }
        }

        // Apply pagination
        $start = $request->input('start');
        $length = $request->input('length');

        $traces = $query->offset($start)->limit($length)->get();

        $formattedTraces = $traces->map(function ($trace, $key) use ($start) {
            $trace->DT_RowIndex = $start + $key + 1;
            $trace->user_name = $trace->user ? $trace->user->name : 'N/A'; // Add user_name for DataTables
            $trace->action = '<button class="btn btn-sm btn-info view-trace" data-id="' . $trace->id . '">View</button>'; // Placeholder for action buttons
            return $trace;
        });

      

        return response()->json([
            'data' => $formattedTraces,
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords, // For now, recordsFiltered is same as totalRecords after search
        ]);
    }

    /**
     * Get GPS traces for a specific visit.
     */
    public function getVisitTraces($visitId)
    {
        // This method should retrieve GPS traces associated with the given visitId.
        // Assuming visitId can be used to query UserGpsTrace records.
        $traces = UserGpsTrace::where('visit_id', $visitId)->get();

        return response()->json($traces);
    }
}