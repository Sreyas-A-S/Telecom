<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;
use App\Models\ProductModel;
use App\Models\Tax;
use App\Models\Employee;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PartController extends Controller
{
    public function index()
    {
        $permissions = [
            'can_create' => checkMenu(Session::get('role_id'), 20, 'create'),
            'can_edit' => checkMenu(Session::get('role_id'), 20, 'update'),
            'can_delete' => checkMenu(Session::get('role_id'), 20, 'delete'),
            'can_read' => checkMenu(Session::get('role_id'), 20, 'read'),
        ];
        return view('parts.index', compact('permissions'));
    }

    public function getDataTableData()
    {
        $parts = Part::with('tax', 'productModels', 'products')->orderBy('id', 'desc');

        return DataTables::of($parts)
            ->addIndexColumn()
            ->addColumn('products_info', function (Part $part) {
                $productNames = $part->products->pluck('name')->implode(', ');
                return $productNames;
            })
            ->addColumn('product_models_info', function (Part $part) {
                return $part->productModels->pluck('name')->implode(', ');
            })

            ->addColumn('status', function (Part $part) {
                return $part->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function (Part $part) {
                $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                //check if user has edit permission
                if (checkMenu(Session::get('role_id'), 20, 'update')) {
                    $btn .= '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' . $part->id . '" class="edit-part-btn"><i class="icon-pencil"></i></a></li>';
                }
                if (checkMenu(Session::get('role_id'), 20, 'delete')) {
                    $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $part->id . '" data-name="' . $part->part_number . '" class="delete-part-btn"><i class="icon-trash"></i></a></li>';
                }
                if (checkMenu(Session::get('role_id'), 20, 'read')) {
                    $btn .= '<li class="view"><a title="View" href="javascript:void(0)" data-id="' . $part->id . '" class="view-part-btn"><i class="icon-eye"></i></a></li>';
                }
                $btn .= '</ul>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $productModels = ProductModel::all();
        $taxes = Tax::all();
        return response()->json(['productModels' => $productModels, 'taxes' => $taxes]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'material_description' => 'nullable|string',
            'tax_id' => 'nullable|exists:taxes,id',
            'unit_price' => 'required|numeric|min:0',
            'hsn' => 'nullable|string',
            'machine' => 'nullable|string',
            'dealer' => 'nullable|string',
            'bin' => 'nullable|string',
            'part_number' => 'required|string',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
            'product_models' => 'nullable|array',
            'product_models.*' => 'exists:product_models,id',

        ]);

        $dealershipId = null;
        $user = Auth::user();

        if ($user) {
            Log::info('Authenticated User:', ['user_id' => $user->id, 'user_type' => $user->user_type]);
            if ($user->user_type === 'employee') {
                $user->load('employee');
                if ($user->employee) {
                    Log::info('Employee found:', ['employee_id' => $user->employee->id, 'dealership_id' => $user->employee->dealership_id]);
                    $dealershipId = $user->employee->dealership_id;
                } else {
                    Log::warning('Authenticated user is an employee but has no associated employee record.');
                }
            } else {
                Log::info('Authenticated user is not an employee, skipping dealership_id assignment from user.');
            }
        } else {
            Log::warning('No authenticated user found for part creation.');
        }

        Log::info('Final dealershipId for part:', ['dealership_id' => $dealershipId]);

        $part = Part::create(array_merge($request->only([
            'material_description',
            'tax_id',
            'unit_price',
            'hsn',
            'machine',
            'dealer',
            'bin',
            'part_number',
            'stock_quantity',
            'is_active',
        ]), ['dealership_id' => $dealershipId]));

        if ($request->has('products')) {
            $part->products()->sync($request->input('products'));
        }
        if ($request->has('product_models')) {
            $part->productModels()->sync($request->input('product_models'));
        }


        return response()->json(['message' => 'Part created successfully.']);
    }

    public function show(Part $part)
    {
        $part->load('products', 'tax', 'productModels');
        return response()->json($part);
    }

    public function edit(Part $part)
    {
        $part->load('products', 'tax', 'productModels');
        $taxes = Tax::all();
        $productModels = ProductModel::all();
        return response()->json(['part' => $part, 'taxes' => $taxes, 'productModels' => $productModels]);
    }

    public function update(Request $request, Part $part)
    {
        $request->validate([
            'material_description' => 'nullable|string',
            'tax_id' => 'nullable|exists:taxes,id',
            'unit_price' => 'required|numeric|min:0',
            'hsn' => 'nullable|string',
            'machine' => 'nullable|string',
            'dealer' => 'nullable|string',
            'bin' => 'nullable|string',
            'part_number' => 'required|string',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
            'product_models' => 'nullable|array',
            'product_models.*' => 'exists:product_models,id',

        ]);

        $dealershipId = Auth::user()->employee->dealership_id ?? null;

        $part->update(array_merge($request->only([
            'material_description',
            'tax_id',
            'unit_price',
            'hsn',
            'machine',
            'dealer',
            'bin',
            'part_number',
            'stock_quantity',
            'is_active',
        ]), ['dealership_id' => $dealershipId]));

        if ($request->has('products')) {
            $part->products()->sync($request->input('products'));
        } else {
            $part->products()->detach();
        }
        if ($request->has('product_models')) {
            $part->productModels()->sync($request->input('product_models'));
        } else {
            $part->productModels()->detach();
        }


        return response()->json(['message' => 'Part updated successfully.']);
    }

    public function destroy(Part $part)
    {
        $part->delete();
        return response()->json(['message' => 'Part deleted successfully.']);
    }

    public function getPartsList()
    {
        $parts = Part::all();
        return response()->json(['data' => $parts]);
    }

    /**
     * Search for parts by part_number or material_description.
     *
     * @OA\Get(
     *      path="/parts/search-web",
     *      summary="Search for parts (Web)",
     *      tags={"Parts (Web)"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="query",
     *          in="query",
     *          description="Keyword to search for in part numbers or material descriptions",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="exclude",
     *          in="query",
     *          description="Comma-separated list of part IDs to exclude from results",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Part")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      )
     * )
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $exclude = $request->input('exclude', []);

        $partsQuery = Part::where(function ($q) use ($query) {
            $q->where('part_number', 'LIKE', "%{$query}%")
                ->orWhere('material_description', 'LIKE', "%{$query}%");
        });

        if (!empty($exclude)) {
            $partsQuery->whereNotIn('id', $exclude);
        }

        $parts = $partsQuery->limit(10)->get();

        return response()->json($parts);
    }
}
