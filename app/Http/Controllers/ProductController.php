<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\Tax;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Use leftJoin to flatten the hierarchy and handle missing relationships gracefully
            // Base the query on Product to ensure we see all machines
            $query = Product::leftJoin('product_models', 'products.id', '=', 'product_models.product_id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id')
                ->leftJoin('taxes', 'products.tax_id', '=', 'taxes.id')
                ->select([
                    'products.id as product_id',
                    'product_models.id as model_id',
                    'products.name as machine_name',
                    'products.brand as brand',
                    'product_models.price as product_price',
                    'products.unit_type',
                    'products.hsn_sac as hsn_sac',
                    'product_models.name as model_name',
                    'categories.name as category_name',
                    'sub_categories.name as sub_category_name',
                    'taxes.name as tax_name'
                ])
                ->orderBy('products.created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="view"><a title="View" href="javascript:void(0)" data-id="' . $row->product_id . '" data-model-id="' . $row->model_id . '" class="view-product-btn"><i class="icon-eye"></i></a></li>';
                    $btn .= '<li class="edit"><a href="javascript:void(0)" title="Edit" data-id="' . $row->product_id . '" data-model-id="' . $row->model_id . '" class="edit-product-btn"><i class="icon-pencil-alt"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->product_id . '" data-model-id="' . $row->model_id . '" data-product-name="' . addslashes($row->machine_name) . ' (' . addslashes($row->model_name) . ')" class="delete-product-btn"><i class="icon-trash"></i></a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->editColumn('machine_model', function ($row) {
                    return $row->model_name ?? 'N/A';
                })
                ->filterColumn('machine_model', function($query, $keyword) {
                    $query->whereRaw("product_models.name like ?", ["%{$keyword}%"]);
                })
                ->orderColumn('machine_model', function($query, $order) {
                    $query->orderBy('product_models.name', $order);
                })

                ->addColumn('name', function ($row) {
                    return $row->machine_name;
                })
                ->addColumn('category_sub_category', function ($row) {
                    $cat = $row->category_name ?? 'N/A';
                    $sub = $row->sub_category_name ?? 'N/A';
                    return $cat . ' - ' . $sub;
                })
                ->addColumn('price_unit', function ($row) {
                    $price = $row->product_price;
                    $unit = $row->unit_type ?? 'PCS';
                    return number_format($price, 2) . ' / ' . $unit;
                })
                ->addColumn('tax', function ($row) {
                    return $row->tax_name ?? 'N/A';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $categories = Category::all();
        $subCategories = SubCategory::all();
        $taxes = Tax::all();
        $productModels = \App\Models\ProductModel::all();
        $dealerships = \App\Models\Dealership::where('brand', 1)->get();

        $user = auth()->user();
        $userDealershipName = null;
        if ($user && $user->employee && $user->employee->dealership) {
            $userDealershipName = ucfirst($user->employee->dealership->name);
        }

        return view('products.index', compact('categories', 'subCategories', 'taxes', 'productModels', 'dealerships', 'userDealershipName'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'brand' => 'nullable|string',
            'brochure' => 'nullable|mimes:pdf|max:2048',
        ]);

        // Handle Machine (Product) consolidation
        $name = $request->name;
        if ($request->brand && !str_contains(strtolower($name), strtolower($request->brand))) {
            $name = $request->brand . ' ' . $name;
        }

        $product = Product::firstOrCreate(['name' => $name]);
        $product->hsn_sac = $request->hsn_sac;
        $product->brand = $request->brand;
        
        $description = $request->description;
        if ($request->brand) {
            $brandMention = "Brand: " . $request->brand;
            if ($description && !str_contains($description, $brandMention)) {
                $description = $brandMention . "\n" . $description;
            } elseif (!$description) {
                $description = $brandMention;
            }
        }
        $product->description = $description;
        $product->unit_type = $request->unit_type;

        // Handle Brochure Upload
        if ($request->hasFile('brochure')) {
            $file = $request->file('brochure');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/brochures'), $filename);
            $product->brochure = 'uploads/brochures/' . $filename;
        }

        // Handle Category
        $category = null;
        if ($request->category) {
            $category = Category::firstOrCreate(['name' => $request->category]);
            $product->category_id = $category->id;
        }

        // Handle SubCategory
        if ($request->sub_category) {
            $subCategory = SubCategory::where('name', $request->sub_category)->first();
            if (!$subCategory && $category) {
                $subCategory = SubCategory::create([
                    'name' => $request->sub_category,
                    'category_id' => $category->id
                ]);
            }
            if ($subCategory) {
                $product->sub_category_id = $subCategory->id;
            }
        }

        // Handle Tax
        if ($request->tax) {
            $tax = Tax::where('name', $request->tax)->first();
            if (!$tax) {
                $tax = Tax::create(['name' => $request->tax, 'rate' => 0]);
            }
            $product->tax_id = $tax->id;
        }

        $product->save();

        if ($request->model) {
            $productModel = \App\Models\ProductModel::updateOrCreate([
                'name' => $request->model,
                'product_id' => $product->id
            ], [
                'price' => $request->price,
                'description' => $request->description
            ]);
        }

        return response()->json(['message' => 'Product created successfully.']);
    }

    public function show(Product $product, Request $request)
    {
        $modelId = $request->query('model_id');
        $product->load(['category', 'subCategory', 'tax', 'models']);

        $categories = Category::all();
        $subCategories = SubCategory::all();
        $taxes = Tax::all();

        $targetModel = $modelId 
            ? $product->models->where('id', $modelId)->first() 
            : $product->models->first();

        if ($targetModel) {
            $product->price = $targetModel->price;
            if ($targetModel->description) {
                $product->description = $targetModel->description;
            }
        }

        return response()->json([
            'product' => $product,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'taxes' => $taxes,
            'model' => $targetModel ? $targetModel->name : '',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'brand' => 'nullable|string',
            'brochure' => 'nullable|mimes:pdf|max:2048',
        ]);

        $name = $request->name;
        if ($request->brand && !str_contains(strtolower($name), strtolower($request->brand))) {
            $name = $request->brand . ' ' . $name;
        }
        $product->name = $name;

        $description = $request->description;
        if ($request->brand) {
            $brandMention = "Brand: " . $request->brand;
            if ($description && !str_contains($description, $brandMention)) {
                $description = $brandMention . "\n" . $description;
            } elseif (!$description) {
                $description = $brandMention;
            }
        }
        $product->description = $description;
        $product->brand = $request->brand;
        
        $product->hsn_sac = $request->hsn_sac;
        $product->unit_type = $request->unit_type;
        $product->category_id = $request->category_id;
        $product->sub_category_id = $request->sub_category_id;
        $product->tax_id = $request->tax_id;

        // Handle Brochure Upload
        if ($request->hasFile('brochure')) {
            // Delete old brochure if exists
            if ($product->brochure && file_exists(public_path($product->brochure))) {
                unlink(public_path($product->brochure));
            }

            $file = $request->file('brochure');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/brochures'), $filename);
            $product->brochure = 'uploads/brochures/' . $filename;
        }

        $product->save();

        if ($request->model) {
            $productModel = \App\Models\ProductModel::updateOrCreate([
                'name' => $request->model,
                'product_id' => $product->id
            ], [
                'price' => $request->price,
                'description' => $request->description
            ]);
        }

        return response()->json(['message' => 'Product updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->brochure && file_exists(public_path($product->brochure))) {
            unlink(public_path($product->brochure));
        }
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function getProductsList()
    {
        $products = Product::all();
        return response()->json(['data' => $products]);
    }

    public function getProductModelsByProductIds(Request $request)
    {
        $productIds = $request->input('product_ids', []);

        $productIds = is_array($productIds)
            ? $productIds
            : (is_numeric($productIds) ? [$productIds] : []);

        $productIds = array_filter($productIds, fn($id) => !empty($id) && is_numeric($id));

        if (empty($productIds)) {
            return response()->json(['models' => []]);
        }

        $productModels = \App\Models\ProductModel::with('product')->whereIn('product_id', $productIds)->get();

        return response()->json(['models' => $productModels]);
    }

    public function getProductModels(Product $product)
    {
        return response()->json(['product_models' => $product->models()->with('product')->get()]);
    }

    public function storeProductModel(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:product_models,name',
            'product_id' => 'required|exists:products,id',
        ]);
        $productModel = \App\Models\ProductModel::create([
            'name' => $request->name,
            'product_id' => $request->product_id,
        ]);
        return response()->json($productModel);
    }
}
