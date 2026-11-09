<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;

use App\Models\Settings\Brand;
use App\Models\Inventory\Inventory;
use App\Models\Settings\Product;
use App\Models\Settings\Category;
use App\Models\Settings\SubCategory;
use App\Models\Supplier;
use App\Models\Settings\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;
use Str;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\UploadTrait;

class ProductController extends Controller
{
    use UploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $pageTitle = 'Products';

        $permissionCreate = 'product_create';
        if (!auth()->user()->hasPermissionTo('product')) {
            abort(403, 'Unauthorized');
        }

        $categories = Category::where('status', 1)->get();
        $products = Product::where('status', 1)->get();

        return view('settings.product.index',compact('pageTitle',
        'permissionCreate', 'categories', 'products'));
    }
    public function dataTable(Request $request)
    {
        $query = Product::with('unit','brand','supplier','category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('product_id')) {
            $query->where('id', $request->product_id);
        }
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Product $product) {
                $btn = '';

                if(auth()->user()->can('product_edit')) {
                    $btn .= '<a href="' . route('product.edit', ['product' => $product->id,'product_type'=>$product->product_type]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if(auth()->user()->can('product_delete')) {
                    $btn .= ' <a role="button" data-id="' . $product->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);

            })
            ->editColumn('image', function (Product $product) {
                return renderZoomGalleryImage($product->image, $product->name);
            })
            ->addColumn('product_category_name', function(Product $product) {
               return $product->category->name ?? '';
            })
            ->addColumn('unit_name', function(Product $product) {
               return $product->unit->name ?? '';
            })
            ->addColumn('brand_name', function(Product $product) {
               return $product->brand->name ?? '';
            })
            ->addColumn('supplier_name', function(Product $product) {
               return $product->supplier->name ?? '';
            })

            ->rawColumns(['action','image'])
            ->toJson();
    }

    public function getProductsByCategory(Request $request)
    {
        $products = Product::where('status', 1);
        if ($request->category_id) {
            $products->where('category_id', $request->category_id);
        }
        return response()->json($products->get());
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        if (!auth()->user()->hasPermissionTo('product_create')) {
            abort(403, 'Unauthorized');
        }

        $pageTitle = 'Product';
        
        $categories = Category::all();
        $units = Unit::where('status',1)->get();
        $brands = Brand::where('status',1)->get();
        $companies = Supplier::where('type',1)->get();
        return view('settings.product.create',compact('units',
            'brands','companies','pageTitle','categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('product_create')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('products')->whereNull('deleted_at')
            ],
            'product_category' => 'required',
            'sub_category' => 'nullable',
            'unit' => 'required',
            'brand' => 'nullable',
            'status' => 'required|boolean', // Ensure 'status' is boolean
            'image' => [
                'nullable', 'mimes:jpg,png,jpeg,webp'
            ],
            'has_warranty' => 'required|boolean',
            'warranty_duration' => 'nullable|integer|min:1',
            'warranty_type' => 'nullable|in:Month,Year',
            'emi_interest' => 'nullable|numeric|min:0',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $category = Category::findOrFail($request->product_category);
            
            $productName = $request->name;
            if ($request->filled('sub_category')) {
                $subCategory = SubCategory::find($request->sub_category);
                if ($subCategory) {
                    $productName = $productName . ' ' . $subCategory->name;
                }
            }
            $validatedData['name'] = $productName;

            $newCode = Product::generateProductCode($category->name, $request->name, $category->code); // Use original name and category code for code generation

            $image = null;
            if ($request->hasFile('image')){
                $image = $this->uploadImage($request->file('image'), 'product/image');
            }
            $validatedData['image'] = $image;
            $validatedData['code'] = $newCode;
            $validatedData['slug'] = Str::slug($request->name);
            $validatedData['product_type'] = $request->product_type; // Finished Product
            $validatedData['category_id'] = $request->product_category;
            $validatedData['sub_category_id'] = $request->sub_category;
            $validatedData['unit_id'] = $request->unit;
            $validatedData['brand_id'] = $request->brand;
            $validatedData['has_warranty'] = $request->has_warranty;
            $validatedData['warranty_duration'] = $request->has_warranty ? $request->warranty_duration : null;
            $validatedData['warranty_type'] = $request->has_warranty ? $request->warranty_type : 'Month';
            $validatedData['emi_interest'] = $request->emi_interest ?? 0;
            unset($validatedData['product_category']); // Remove the 'channel' key
            unset($validatedData['sub_category']);
            unset($validatedData['unit']); // Remove the 'channel' key
            unset($validatedData['brand']); // Remove the 'channel' key

            // Create a new Product record in the database
            $product = Product::create($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('product.index',['product_type'=>$request->product_type])->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the Product: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product,Request $request)
    {

        if (!auth()->user()->hasPermissionTo('product_edit')) {
            abort(403, 'Unauthorized');
        }

        $pageTitle = 'Product';
        
        try {
            $categories = Category::all();
            $units = Unit::where('status',1)->get();
            $brands = Brand::where('status',1)->get();
            $companies = Supplier::where('type',1)->get();
            // If the Product exists, display the edit view
            return view('settings.product.edit', compact('product',
                'units','brands','companies','pageTitle','categories'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Product is not found
            return redirect()->route('product.index')->with('error', 'Product not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        if (!auth()->user()->hasPermissionTo('product_edit')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('products')
                    ->whereNull('deleted_at')
                    ->ignore($product)
            ],
            'product_category' => 'required',
            'sub_category' => 'nullable',
            'unit' => 'required',
            'brand' => 'nullable',
            'status' => 'required|boolean', // Ensure 'status' is boolean
            'image' => [
                'nullable', 'mimes:jpg,png,jpeg,webp'
            ],
            'has_warranty' => 'required|boolean',
            'warranty_duration' => 'nullable|integer|min:1',
            'warranty_type' => 'nullable|in:Month,Year',
            'emi_interest' => 'nullable|numeric|min:0',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $productName = $request->name;
            if ($request->filled('sub_category')) {
                $subCategory = SubCategory::find($request->sub_category);
                if ($subCategory) {
                    $productName = $productName . ' ' . $subCategory->name;
                }
            }
            $validatedData['name'] = $productName;

            $image = $product->image;
            if ($request->hasFile('image')){
                $this->deleteFile($product->image);
                $image = $this->uploadImage($request->file('image'), 'product/image');
            }
            $validatedData['image'] = $image;
            $validatedData['product_type'] = $request->product_type; // Finished Product
            $validatedData['category_id'] = $validatedData['product_category'];
            $validatedData['sub_category_id'] = $request->sub_category;
            $validatedData['unit_id'] = $validatedData['unit'];
            $validatedData['brand_id'] = $validatedData['brand'];
            $validatedData['has_warranty'] = $request->has_warranty;
            $validatedData['warranty_duration'] = $request->has_warranty ? $request->warranty_duration : null;
            $validatedData['warranty_type'] = $request->has_warranty ? $request->warranty_type : 'Month';
            $validatedData['emi_interest'] = $request->emi_interest ?? 0;
            unset($validatedData['product_category']);
            unset($validatedData['sub_category']);
            unset($validatedData['unit']);
            unset($validatedData['brand']);
            // Update the Product record in the database
            $product->update($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('product.index',['product_type'=>$request->product_type])->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while updating the Product: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            if (!auth()->user()->hasPermissionTo('product_delete')) {
                abort(403, 'Unauthorized');
            }
            $inventory = Inventory::where('product_id',$product->id)->first();
            if ($inventory) {
                // If a related Supplier exists, return an error message
                return response()->json(['success' => false, 'message' => 'It has stock logs. Cannot delete.'], Response::HTTP_OK);
            }
            // Delete the image file
            $this->deleteFile($product->image);

            // Delete the Product record
            $product->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Product deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Product not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
