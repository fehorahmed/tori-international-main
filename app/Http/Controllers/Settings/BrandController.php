<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\Brand;
use App\Models\Settings\Product;
use App\Models\Settings\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('settings.brand.index');
    }
    public function dataTable()
    {
        $query = Brand::where('sister_concern_id',auth()->user()->activeConcernId());
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Brand $brand) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('brand_edit')) {
                    $btn .= '<a href="' . route('brand.edit', ['brand' => $brand->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('brand_delete')) {
                    $btn .= ' <a role="button" data-id="' . $brand->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);

            })

            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('brand_create')) {
            abort(403, 'Unauthorized');
        }
        return view('settings.brand.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('brand_create')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('brands')
                    ->where('sister_concern_id',auth()->user()->activeConcernId())
            ],
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {

            // Create a new Brand record in the database
            $validatedData['sister_concern_id'] = auth()->user()->activeConcernId();
            Brand::create($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('brand.index')->with('success', 'Brand created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('brand.create')->with('error', 'An error occurred while creating the Brand: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        if (!auth()->user()->hasPermissionTo('brand_edit')) {
            abort(403, 'Unauthorized');
        }else{
            if($brand->sister_concern_id != auth()->user()->activeConcernId()){
                abort(403, 'Unauthorized');
            }
        }
        try {
            // If the Product exists, display the edit view
            return view('settings.brand.edit', compact('brand'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Product is not found
            return redirect()->route('brand.index')->with('error', 'Brand not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        if (!auth()->user()->hasPermissionTo('brand_edit')) {
            abort(403, 'Unauthorized');
        }else{
            if($brand->sister_concern_id != auth()->user()->activeConcernId()){
                abort(403, 'Unauthorized');
            }
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('brands')
                    ->where('sister_concern_id',auth()->user()->activeConcernId())
                    ->ignore($brand)
            ],
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the Brand record in the database
            $validatedData['sister_concern_id'] = auth()->user()->activeConcernId();
            $brand->update($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('brand.index')->with('success', 'Brand updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('brand.edit',['brand'=>$brand->id])->with('error', 'An error occurred while updating the Brand: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        try {
            if (!auth()->user()->hasPermissionTo('brand_delete')) {
                abort(403, 'Unauthorized');
            }
            $product = Product::where('brand_id',$brand->id)->first();
            if ($product) {
                // If a related Supplier exists, return an error message
                return response()->json(['success' => false, 'message' => 'It has product logs. Cannot delete.'], Response::HTTP_OK);
            }
            // Delete the Product record
            $brand->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Brand deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Brand not found: '.$e->getMessage()], Response::HTTP_OK);

        }
    }
}
