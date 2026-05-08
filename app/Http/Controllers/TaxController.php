<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Tax::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="view"><a title="View" href="javascript:void(0)" data-id="' . $row->id . '" class="view-tax-btn"><i class="icon-eye"></i></a></li>';
                    $btn .= '<li class="edit"><a href="javascript:void(0)" title="Edit" data-id="' . $row->id . '" class="edit-tax-btn"><i class="icon-pencil-alt"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" data-tax-name="' . $row->name . '" class="delete-tax-btn"><i class="icon-trash"></i></a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('about-products.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:taxes,name',
            'rate' => 'required|numeric',
        ]);

        $tax = Tax::create($request->all());

        return response()->json(['message' => 'Tax created successfully.', 'tax' => $tax]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tax $tax)
    {
        return response()->json($tax);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tax $tax)
    {
        $request->validate([
            'name' => 'required|unique:taxes,name,' . $tax->id,
            'rate' => 'required|numeric',
        ]);

        $tax->update($request->all());

        return response()->json(['message' => 'Tax updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax)
    {
        $tax->delete();

        return response()->json(['message' => 'Tax deleted successfully.']);
    }
}
