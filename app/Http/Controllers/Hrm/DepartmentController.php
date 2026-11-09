<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;

use App\Models\Hrm\Department;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    public function index()
    {
        try {
            return view('hrm.departments.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    public function dataTable()
    {
        $query = Department::query();
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Department $department) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('department_edit')) {
                    $btn .= '<a href="' . route('departments.edit', ['department' => $department->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('department_delete')) {
                    $btn .= ' <a role="button" data-id="' . $department->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })

            ->rawColumns(['action'])
            ->toJson();
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('department_create')) {
            abort(403, 'Unauthorized');
        }
        return view('hrm.departments.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('department_create')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('departments')
            ],
        ]);

        try {
            $validatedData['create_user_id'] = auth()->id();
            Department::create($validatedData);

            return redirect()->route('departments.index')->with('success', 'Department created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create departments: ' . $e->getMessage()]);
        }
    }

    public function edit(Department $department)
    {
        if (!auth()->user()->hasPermissionTo('department_edit')) {
            abort(403, 'Unauthorized');
        }
        return view('hrm.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        if (!auth()->user()->hasPermissionTo('department_edit')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('departments')
                    ->ignore($department)
            ],
        ]);

        try {
            $validatedData['alter_user_id'] = auth()->id();
            $department->update($validatedData);
            return redirect()->route('departments.index')->with('success', 'Department updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update departments: ' . $e->getMessage()]);
        }
    }

    public function destroy(Department $department)
    {
        try {
            if (!auth()->user()->hasPermissionTo('department_delete')) {
                abort(403, 'Unauthorized');
            }

           $employee = Employee::where('department_id',$department->id)->first();
            if ($employee){
                throw new \Exception("This department cannot delete, Its have employee logs");
            }
            $department->delete();
            return response()->json(['success'=>true,'message'=>'Department deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
