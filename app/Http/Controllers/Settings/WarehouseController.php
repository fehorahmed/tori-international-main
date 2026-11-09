<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Inventory;
use App\Models\Showroom;
use App\Models\Settings\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {

        $permission = 'warehouse';
        $permissionCreate = 'warehouse_create';
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized');
        }
        try {
            $typeName = 'Warehouse';

            return view('settings.warehouses.index',compact('typeName','permissionCreate'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    public function dataTable()
    {
        $query = Warehouse::where('sister_concern_id',auth()->user()->activeConcernId());
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Warehouse $warehouse) {
                $btn = '';
                $permissionEdit = 'warehouse_edit';
                $permissionDelete = 'warehouse_delete1';

                if(auth()->user()->can($permissionEdit)) {
                    $btn .= '<a href="' . route('warehouses.edit', ['warehouse' => $warehouse->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if(auth()->user()->can($permissionDelete)) {
                    $btn .= ' <a role="button" data-id="' . $warehouse->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })
            ->addColumn('status', function(Warehouse $warehouse) {
                if ($warehouse->status == 1)
                 return '<span class="badge badge-success">Active</span>';
                else
                    return '<span class="badge badge-danger">Inactive</span>';
              })
            ->rawColumns(['action','status'])
            ->toJson();
    }
    public function create(){
        if (!auth()->user()->hasPermissionTo('warehouse_create')) {
            abort(403, 'Unauthorized');
        }
        try {
            $typeName = 'Warehouse';

            return view('settings.warehouses.create',compact('typeName'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function store(Request $request){
        if (!auth()->user()->hasPermissionTo('warehouse_create')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('warehouses')
            ],
            'address' =>['nullable','max:255'],
            'status' =>['required'],
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $warehouse = new Warehouse();
            $warehouse->name = $request->name;
            $warehouse->address = $request->address;
            $warehouse->status = $request->status;
            $warehouse->save();

            DB::commit();

            return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'An error occurred while creating the warehouse : '.$e->getMessage())->withInput();
        }
    }
    public function edit(Warehouse $warehouse){
        if (!auth()->user()->hasPermissionTo('warehouse_edit')) {
            abort(403, 'Unauthorized');
        }
        try {
            $typeName = 'Warehouse';

            return view('settings.warehouses.edit',compact('typeName','warehouse'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function update(Warehouse $warehouse,Request $request){
        if (!auth()->user()->hasPermissionTo('warehouse_edit')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('warehouses')
                    ->ignore($warehouse)
            ],
            'address' =>['nullable','max:255'],
            'status' =>['required'],
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            $warehouse->name = $request->name;
            $warehouse->address = $request->address;
            $warehouse->status = $request->status;
            $warehouse->save();
            DB::commit();

            return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', 'An error occurred while creating the warehouse : '.$e->getMessage())->withInput();
        }
    }
}
