<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Zone::select('zones.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function($row){
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="view"><a title="View" href="#" data-bs-toggle="modal" data-bs-target="#viewZoneModal" data-id="'.$row->id.'"><i class="icon-eye"></i></a></li>';
                    $btn .= '<li class="edit"><a href="#" title="Edit" data-bs-toggle="modal" data-bs-target="#editZoneModal" data-id="'.$row->id.'"><i class="icon-pencil-alt"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteZoneModal" data-id="'.$row->id.'"><i class="icon-trash"></i></a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('zones.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        Zone::create($request->all());

        return response()->json(['message' => 'Zone created successfully.'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Zone $zone)
    {
        return response()->json($zone);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zone $zone)
    {
        return response()->json($zone);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $zone->update($request->all());

        return response()->json(['message' => 'Zone updated successfully.', 'zone' => $zone], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zone $zone)
    {
        $zone->delete();

        return response()->json(['message' => 'Zone deleted successfully.'], 200);
    }
}
