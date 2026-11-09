<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;

use App\Models\Settings\Product;
use App\Models\Settings\Category;
use App\Models\Ecommerce\Filter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $permissionCreate = 'product_category_create';
        if (!auth()->user()->hasPermissionTo('product_category')) {
            abort(403, 'Unauthorized');
        }
        $pageTitle = 'Product Categories';
        
        return view('settings.product_categories.index', compact('pageTitle',
        'permissionCreate'));
    }

    public function dataTable()
    {
        $query = Category::query();
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Category $category) {
                $btn = '';
                if(auth()->user()->can('product_category_edit')) {
                    $btn .= '<a href="' . route('product-categories.edit', ['product_category' => $category->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                    $btn .= ' <a href="' . route('product-categories.add_edit_filter', ['product_category' => $category->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm btn-edit">Add/Edit Filter</a>';
                }
                if(auth()->user()->can('product_category_delete')) {
                    $btn .= ' <a role="button" data-id="' . $category->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);

            })
            ->editColumn('image', function (Category $category) {
                return '<img height="50px" src="'.asset(file_exists($category->image) ? $category->image : 'img/no-image.webp').'">';
            })
            ->addColumn('status', function (Category $category) {
                $route = route('product-categories.toggle_status', $category->id);
                $badgeClass = $category->status ? 'badge-success' : 'badge-danger';
                $label = $category->status ? 'Active' : 'Inactive';
                return '<a href="' . $route . '" class="badge ' . $badgeClass . '">' . $label . '</a>';
            })
            ->rawColumns(['action','image','status'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('product_category_create')) {
            abort(403, 'Unauthorized');
        }

        $pageTitle = 'Product Category';
        return view('settings.product_categories.create', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('product_category_create')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' => [
                'required', 'max:255',
                Rule::unique('categories')->whereNull('deleted_at')
            ],
            'slug' => [
                'required', 'max:255',
                Rule::unique('categories')->whereNull('deleted_at')
            ],
            'image' => [
                'nullable', 'mimes:jpg,png,jpeg,webp'
            ],
        ]);

        DB::beginTransaction();

        try {

            $image = null;
            if ($request->hasFile('image')){
                // Upload Image
                $file = $request->file('image');
                $filename = Uuid::uuid1()->toString() . '.' . $file->extension();
                $destinationPath = 'uploads/product_category/image';
                $file->move(public_path($destinationPath), $filename);
                $image = 'uploads/product_category/image/' . $filename;
            }
            $validatedData['image'] = $image;
            $validatedData['create_user_id'] = auth()->id();

            $lastCategory = Category::orderBy('id', 'desc')->first();
            $lastCode = $lastCategory ? intval($lastCategory->code) : 0;
            $validatedData['code'] = str_pad($lastCode + 1, 2, '0', STR_PAD_LEFT);

            // Create a new Product record in the database
            $product = Category::create($validatedData);

            DB::commit();
            // Redirect to the index page with a success message
            return redirect()->route('product-categories.index', ['product_type' => $request->product_type])->with('success', 'Product category created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error and redirect with an error message
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the Product category: ' . $e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $product_category, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('product_category_edit')) {
            abort(403, 'Unauthorized');
        }

        $pageTitle = 'Product Category';
        return view('settings.product_categories.edit', compact('pageTitle',
            'product_category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $product_category)
    {
        if (!auth()->user()->hasPermissionTo('product_category_edit')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' => [
                'required', 'max:255',
                Rule::unique('categories')
                    ->ignore($product_category)
                    ->whereNull('deleted_at')
            ],
            'slug' => [
                'required', 'max:255',
                Rule::unique('categories')
                    ->ignore($product_category)
                    ->whereNull('deleted_at')
            ],
            'image' => [
                'nullable', 'mimes:jpg,png,jpeg,webp'
            ],
        ]);

        DB::beginTransaction();

        try {
            $image = $product_category->image;
            if ($request->hasFile('image')){
                if (file_exists($product_category->image)){
                    unlink($product_category->image);
                }
                // Upload Image
                $file = $request->file('image');
                $filename = Uuid::uuid1()->toString() . '.' . $file->extension();
                $destinationPath = 'uploads/product_category/image';
                $file->move(public_path($destinationPath), $filename);
                $image = 'uploads/product_category/image/' . $filename;
            }
            $validatedData['image'] = $image;
            $validatedData['alter_user_id'] = auth()->id();

            $product_category->update($validatedData);

            DB::commit();
            // Redirect to the index page with a success message
            return redirect()->route('product-categories.index', ['product_type' => $request->product_type])->with('success', 'Product category updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error and redirect with an error message
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while updating the Product category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Category $product_category)
    {
        try {
            if (!auth()->user()->hasPermissionTo('product_category_delete')) {
                abort(403, 'Unauthorized');
            }
            $product = Product::where('category_id', $product_category->id)->first();
            if ($product) {
                // If a related Supplier exists, return an error message
                return response()->json(['success' => false, 'message' => 'It has product logs. Cannot delete.'], Response::HTTP_OK);
            }
            // Delete the Product record
            $product_category->delete();
            // Return a JSON success response
            return response()->json(['success' => true, 'message' => 'Product category deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success' => false, 'message' => 'Product category not found: ' . $e->getMessage()], Response::HTTP_OK);
        }
    }

    public function toggleStatus(Category $product_category)
    {
        try {
            $product_category->status = !$product_category->status;
            $product_category->save();

            return redirect()->route('product-categories.index')->with('success', 'Category Status Updated Successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something Went Wrong!');
        }
    }

    public function addEditFilter(Category $product_category)
    {
        try {
            $filters = Filter::all();
            // selected filters for this category
            $selectedFilters = $product_category->filters->pluck('id')->toArray();
            return view('settings.product_categories.add_edit_filter', compact('product_category', 'filters', 'selectedFilters'));
        } catch (\Exception $e) {
            return redirect()->route('product-categories.index')->with('error', 'Category not found: ' . $e->getMessage());
        }
    }

    public function addEditFilterPost(Request $request, Category $product_category)
    {
        $validatedData = $request->validate([
            'filters' => 'nullable|array',
            'filters.*' => 'exists:filters,id',
        ]);

        // Sync the selected filters with the category
        $product_category->filters()->sync($validatedData['filters'] ?? []);

        return redirect()->route('product-categories.index')->with('success', 'Category filters updated successfully');
    }
}
