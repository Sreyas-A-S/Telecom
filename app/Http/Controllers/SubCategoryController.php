<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SubCategory::with('category')->select('sub_categories.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="view"><a title="View" href="javascript:void(0)" data-id="' . $row->id . '" class="view-sub-category-btn"><i class="icon-eye"></i></a></li>';
                    $btn .= '<li class="edit"><a href="javascript:void(0)" title="Edit" data-id="' . $row->id . '" class="edit-sub-category-btn"><i class="icon-pencil-alt"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" data-sub-category-name="' . $row->name . '" class="delete-sub-category-btn"><i class="icon-trash"></i></a></li>';
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
            'name' => 'required|unique:sub_categories,name',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subCategory = SubCategory::create($request->all());

        return response()->json(['message' => 'Sub Category created successfully.', 'subCategory' => $subCategory]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SubCategory $subCategory)
    {
        $subCategory->load('category');
        return response()->json($subCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubCategory $subCategory)
    {
        $request->validate([
            'name' => 'required|unique:sub_categories,name,' . $subCategory->id,
            'category_id' => 'required|exists:categories,id',
        ]);

        $subCategory->update($request->all());

        return response()->json(['message' => 'Sub Category updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subCategory)
    {
        $subCategory->delete();

        return response()->json(['message' => 'Sub Category deleted successfully.']);
    }
}
