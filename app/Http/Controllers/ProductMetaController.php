<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Tax;
use App\Models\ProductModel;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class ProductMetaController extends Controller
{
    public function index(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'read')) {
            return redirect()->back()->with('error', 'You do not have permission to view About Products.');
        }
        $categories = Category::all();
        $subCategories = SubCategory::all();
        $taxes = Tax::all();
        $products = Product::all();

        $permissions = [
            'can_read' => checkMenu(Session::get('role_id'), 7, 'read'),
            'can_create' => checkMenu(Session::get('role_id'), 7, 'create'),
            'can_update' => checkMenu(Session::get('role_id'), 7, 'update'),
            'can_delete' => checkMenu(Session::get('role_id'), 7, 'delete'),
        ];

        return view('about-products.index', compact('categories', 'subCategories', 'taxes', 'products', 'permissions'));
    }

    public function getBrands(Request $request)
    {
        $brands = \App\Models\Dealership::whereNotNull('brand')->distinct()->pluck('brand');
        return response()->json($brands);
    }

    public function getCategories(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($request->ajax()) {
            $data = Category::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function storeCategory(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'create')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        $request->validate(['name' => 'required|unique:categories,name']);
        $category = Category::create(['name' => $request->name]);
        return response()->json(['message' => 'Category created successfully.', 'category' => $category]);
    }

    public function getSubCategories(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($request->ajax()) {
            $data = SubCategory::with('category')->select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function storeSubCategory(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'create')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        $request->validate([
            'name' => 'required|unique:sub_categories,name',
            'category_id' => 'required|exists:categories,id'
        ]);
        $subCategory = SubCategory::create([
            'name' => $request->name,
            'category_id' => $request->category_id
        ]);
        return response()->json(['message' => 'Sub Category created successfully.', 'subCategory' => $subCategory]);
    }

    public function getTaxes(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($request->ajax()) {
            $data = Tax::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function storeTax(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'create')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        $request->validate([
            'name' => 'required|unique:taxes,name',
            'rate' => 'required|numeric'
        ]);
        $tax = Tax::create([
            'name' => $request->name,
            'rate' => $request->rate
        ]);
        return response()->json($tax);
    }

    // Product Models
    public function getProductModels(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $data = ProductModel::with('product')->orderBy('created_at', 'desc');
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                $btn .= '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' . $row->id . '" class="edit-product-model-btn"><i class="icon-pencil-alt"></i></a></li>';
                $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" data-product-model-name="' . $row->name . '" class="delete-product-model-btn"><i class="icon-trash"></i></a></li>';
                $btn .= '</ul>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeProductModel(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'create')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        $request->validate(['product_id' => 'required|exists:products,id', 'name' => 'required|unique:product_models,name']);
        $productModel = ProductModel::create($request->all());
        return response()->json(['message' => 'Product Model created successfully.', 'productModel' => $productModel]);
    }

    public function editProductModel(ProductModel $productModel)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($productModel->load('product'));
    }

    public function updateProductModel(Request $request, ProductModel $productModel)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $request->validate(['product_id' => 'required|exists:products,id', 'name' => 'required|unique:product_models,name,' . $productModel->id]);
        $productModel->update($request->all());
        return response()->json($productModel);
    }

    public function deleteProductModel(ProductModel $productModel)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $productModel->delete();
        return response()->json(['message' => 'Product Model deleted successfully.']);
    }

    public function getModelsByProduct(Request $request)
    {
    
        $productId = $request->input('product_id');
        if (!$productId) {
            return response()->json([]);
        }
        $productModels = ProductModel::where('product_id', $productId)->get();
        return response()->json($productModels);
    }

    public function getProductsByDealership(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'read')) { // Assuming read permission for products is tied to Product Meta
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $dealershipId = $request->input('dealership_id');
        if (!$dealershipId) {
            return response()->json([]);
        }
        // Assuming Product model has a relationship or a way to filter by dealership
        // This might need adjustment based on your actual Product-Dealership relationship
        $products = Product::whereHas('dealerships', function ($query) use ($dealershipId) {
            $query->where('dealerships.id', $dealershipId);
        })->get();
        return response()->json($products);
    }

    // Model Series
    public function getModelSeries(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $data = \App\Models\ModelSeries::with('productModel.product')->orderBy('created_at', 'desc');
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                $btn .= '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' . $row->id . '" class="edit-model-series-btn"><i class="icon-pencil-alt"></i></a></li>';
                $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" data-model-series-name="' . $row->name . '" class="delete-model-series-btn"><i class="icon-trash"></i></a></li>';
                $btn .= '</ul>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeModelSeries(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'create')) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }
        $request->validate(['product_model_id' => 'required|exists:product_models,id', 'name' => 'required|unique:model_series,name']);
        $modelSeries = \App\Models\ModelSeries::create($request->all());
        return response()->json(['message' => 'Model Series created successfully.', 'modelSeries' => $modelSeries]);
    }

    public function editModelSeries(\App\Models\ModelSeries $modelSeries)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($modelSeries->load('productModel.product'));
    }

    public function updateModelSeries(Request $request, \App\Models\ModelSeries $modelSeries)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $request->validate(['product_model_id' => 'required|exists:product_models,id', 'name' => 'required|unique:model_series,name,' . $modelSeries->id]);
        $modelSeries->update($request->all());
        return response()->json(['message' => 'Model Series updated successfully.', 'modelSeries' => $modelSeries]);
    }

    public function deleteModelSeries(\App\Models\ModelSeries $modelSeries)
    {
        if (!checkMenu(Session::get('role_id'), 7, 'delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $modelSeries->delete();
        return response()->json(['message' => 'Model Series deleted successfully.']);
    }
}
