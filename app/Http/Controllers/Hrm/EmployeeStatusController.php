<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployeeStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmployeeStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hrm.employee_status.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('employee_status_create')) {
            abort(403, 'Unauthorized');
        }
        $maxSort = EmployeeStatus::max('sort') + 1;
        return view('hrm.employee_status.create',compact('maxSort'));
    }
    public function dataTable()
    {
        $query = EmployeeStatus::query();
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(EmployeeStatus $employee_status) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('employee_status_edit')) {
                    $btn .= '<a href="' . route('employee-statuses.edit', ['employee_status' => $employee_status->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('employee_status_delete')) {
                    $btn .= ' <a role="button" data-id="' . $employee_status->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })
            ->editColumn('is_salary_apply', function(EmployeeStatus $employee_status) {

                if ($employee_status->is_salary_apply == 1){
                    return '<span class="badge badge-success">Yes</span>';
                }else{
                    return '<span class="badge badge-danger">No</span>';
                }

            })

            ->rawColumns(['action','is_salary_apply'])
            ->toJson();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('employee_status_create')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('employee_statuses')
            ],
            'is_salary_apply' => 'required',
            'sort' => 'required|integer',
        ]);

        try {
            EmployeeStatus::create($validatedData);

            return redirect()->route('employee-statuses.index')->with('success', 'Employee status created successfully');
        } catch (\Exception $e) {
            return redirect()->route('employee-statuses.create')
                ->withInput()
                ->with('error',$e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeStatus $employee_status)
    {
        if (!auth()->user()->hasPermissionTo('employee_status_edit')) {
            abort(403, 'Unauthorized');
        }
        return view('hrm.employee_status.edit', compact('employee_status'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeStatus $employee_status)
    {
        if (!auth()->user()->hasPermissionTo('employee_status_edit')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('employee_statuses')
                    ->ignore($employee_status)
            ],
            'is_salary_apply' => 'required',
            'sort' => 'required|integer', // Ensure 'status' is boolean
        ]);

        try {
            $employee_status->update($validatedData);

            return redirect()->route('employee-statuses.index')->with('success', 'Employee status updated successfully');
        } catch (\Exception $e) {

            return redirect()->route('employee-statuses.edit')->withInput()->with('error',$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeStatus $employee_status)
    {
        try {
            if (!auth()->user()->hasPermissionTo('employee_status_delete')) {
                abort(403, 'Unauthorized');
            }
            $employee = Employee::where('employee_status_id',$employee_status->id)->first();
            if ($employee) {
                // If a related Supplier exists, return an error message
                return response()->json(['success' => false, 'message' => 'It has employee logs. Cannot delete.'], Response::HTTP_OK);
            }
            // Delete the Project record
            $employee->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Employee status deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' =>$e->getMessage()], Response::HTTP_OK);

        }
    }
}
