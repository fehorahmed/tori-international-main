<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\CustomerType;
use App\Models\Crm\CrmClient;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CustomerTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('crm.customer_type.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('customer_type_create')) {
            abort(403, 'Unauthorized');
        }
        return view('crm.customer_type.create');
    }

    public function dataTable()
    {
        $query = CustomerType::query();
        return DataTables::eloquent($query) 
            ->addIndexColumn()
            ->addColumn('action', function(CustomerType $customer_type) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('customer_type_edit')) {
                    $btn .= '<a href="' . route('customer-types.edit', ['customer_type' => $customer_type->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                // if (auth()->user()->hasPermissionTo('customer_type_delete')) {
                //     $btn .= ' <a role="button" data-id="' . $customer_type->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                // }
                return $btn;

            })
            ->editColumn('status', function(CustomerType $customer_type) {
                if ($customer_type->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }else {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })

            ->rawColumns(['action','status'])
            ->toJson();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('customer_type_create')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255', Rule::unique('customer_types', 'name')
            ],
            'status' => 'required'
        ]);

        try {
            CustomerType::create($validatedData);

            return redirect()->route('customer-types.index')->with('success', 'Customer type created successfully');
        } catch (\Exception $e) {
            return redirect()->route('customer-types.create')
                ->withInput()
                ->with('error',$e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomerType $customer_type)
    {
        if (!auth()->user()->hasPermissionTo('customer_type_edit')) {
            abort(403, 'Unauthorized');
        }
        return view('crm.customer_type.edit', compact('customer_type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerType $customer_type)
    {
        if (!auth()->user()->hasPermissionTo('customer_type_edit')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255', Rule::unique('customer_types', 'name')->ignore($customer_type->id)
            ],
            'status' => 'required',
        ]);

        try {
            $customer_type->update($validatedData);

            return redirect()->route('customer-types.index')->with('success', 'Customer type updated successfully');
        } catch (\Exception $e) {

            return redirect()->route('customer-types.edit',['customer_type' => $customer_type->id])->withInput()->with('error',$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerType $customer_type)
    {
        try {
            if (!auth()->user()->hasPermissionTo('customer_type_delete')) {
                abort(403, 'Unauthorized');
            }

            // Check if this type is used in any client or supplier
            $clientCount = CrmClient::where('customer_type_id', $customer_type->id)->count();
            $supplierCount = Supplier::where('customer_type_id', $customer_type->id)->count();

            if ($clientCount > 0 || $supplierCount > 0) {
                return response()->json(['success' => false, 'message' => 'This customer type is being used. You cannot delete it.'], Response::HTTP_OK);
            }

            // Delete the type record
            $customer_type->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Customer type deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' =>$e->getMessage()], Response::HTTP_OK);

        }
    }
}
