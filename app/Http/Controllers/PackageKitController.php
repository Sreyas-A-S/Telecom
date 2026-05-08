<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PackageKit;

class PackageKitController extends Controller
{
    public function index()
    {
        return view('service-kits.index');
    }

    public function getDataTableData()
    {
        $packageKits = PackageKit::with('parts')->select(['id', 'name', 'description', 'price', 'features', 'is_active', 'created_at', 'updated_at']);

        return datatables()->of($packageKits)
            ->addColumn('parts_list', function (PackageKit $packageKit) {
                return $packageKit->parts->map(function ($part) {
                    return $part->part_number . ' (Qty: ' . $part->pivot->quantity . ')';
                })->implode(', ');
            })
            ->addColumn('features_list', function (PackageKit $packageKit) {
                if (is_array($packageKit->features)) {
                    return implode(', ', $packageKit->features);
                }
                return '';
            })
            ->addColumn('status', function (PackageKit $packageKit) {
                return $packageKit->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function (PackageKit $packageKit) {
                $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                $btn .= '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' . $packageKit->id . '" class="edit-package-kit-btn"><i class="icon-pencil"></i></a></li>';
                $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $packageKit->id . '" data-name="' . $packageKit->name . '" class="delete-package-kit-btn"><i class="icon-trash"></i></a></li>';
                $btn .= '<li class="view"><a title="View" href="javascript:void(0)" data-id="' . $packageKit->id . '" class="view-package-kit-btn"><i class="icon-eye"></i></a></li>';
                $btn .= '</ul>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'features' => 'nullable|array',
            'parts' => 'nullable|array',
            'parts.*.part_id' => 'required|exists:parts,id',
            'parts.*.quantity' => 'required|integer|min:1',
        ]);

        $packageKit = PackageKit::create($request->only('name', 'description', 'price', 'features', 'is_active'));

        if ($request->has('parts')) {
            $parts = [];
            foreach ($request->parts as $part) {
                $parts[$part['part_id']] = ['quantity' => $part['quantity']];
            }
            $packageKit->parts()->sync($parts);
        }

        return response()->json(['message' => 'Package kit created successfully.']);
    }

    public function show(PackageKit $packageKit)
    {
        $packageKit->load('parts');
        return response()->json($packageKit);
    }

    public function update(Request $request, PackageKit $packageKit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'features' => 'nullable|array',
            'parts' => 'nullable|array',
            'parts.*.part_id' => 'required|exists:parts,id',
            'parts.*.quantity' => 'required|integer|min:1',
        ]);

        $packageKit->update($request->only('name', 'description', 'price', 'features', 'is_active'));

        if ($request->has('parts')) {
            $parts = [];
            foreach ($request->parts as $part) {
                $parts[$part['part_id']] = ['quantity' => $part['quantity']];
            }
            $packageKit->parts()->sync($parts);
        } else {
            $packageKit->parts()->detach();
        }

        return response()->json(['message' => 'Package kit updated successfully.']);
    }

    public function destroy(PackageKit $packageKit)
    {
        $packageKit->delete();
        return response()->json(['message' => 'Package kit deleted successfully.']);
    }
}
