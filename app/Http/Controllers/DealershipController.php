<?php

namespace App\Http\Controllers;

use App\Models\Dealership;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DealershipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Dealership::select('id', 'name')->where('brand', 1);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function($row){
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="view"><a title="View" href="#" data-bs-toggle="modal" data-bs-target="#viewDealershipModal" data-id="'.$row->id.'"><i class="icon-eye"></i></a></li>';
                    $btn .= '<li class="edit"><a href="#" title="Edit" data-bs-toggle="modal" data-bs-target="#editDealershipModal" data-id="'.$row->id.'"><i class="icon-pencil-alt"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteDealershipModal" data-id="'.$row->id.'"><i class="icon-trash"></i></a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('dealerships.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:dealerships|max:255',
        ]);

        Dealership::create($request->all());

        return response()->json(['message' => 'Dealership created successfully.'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Dealership $dealership)
    {
        return response()->json($dealership);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dealership $dealership)
    {
        return response()->json($dealership);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dealership $dealership)
    {
        $request->validate([
            'name' => 'required|unique:dealerships,name,' . $dealership->id . '|max:255',
        ]);

        $dealership->update($request->all());

        return response()->json(['message' => 'Dealership updated successfully.', 'dealership' => $dealership], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dealership $dealership)
    {
        $dealership->delete();

        return response()->json(['message' => 'Dealership deleted successfully.'], 200);
    }
}
