<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;

use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DesignationController extends Controller
{
    public function index()
    {
        try {
            return view('hrm.designations.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function dataTable()
    {
        $query = Designation::with('department');
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Designation $designation) {

                $btn = '';
                if ($designation->reserved_id != null) {
                    $btn .= '<button class="btn btn-secondary btn-sm" disabled title="System Reserved"><i class="fa fa-lock"></i></button>';
                } else {
                    if (auth()->user()->hasPermissionTo('designation_edit')) {
                        $btn .= '<a href="' . route('designations.edit', ['designation' => $designation->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (auth()->user()->hasPermissionTo('designation_delete')) {
                        $btn .= ' <a role="button" data-id="' . $designation->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                    }
                }
                return $btn;

            })
            ->addColumn('department_name', function(Designation $designation) {

                return $designation->department->name ?? '';

            })

            ->rawColumns(['action'])
            ->toJson();
    }
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('designation_create')) {
            abort(403, 'Unauthorized');
        }
        $departments = Department::get();
        return view('hrm.designations.create',compact('departments'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('designation_create')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('designations')
                ->where('department_id',$request->department)
            ],
            'department'=>'required'
        ]);
        try {
            $validatedData['department_id'] = $validatedData['department'];
            unset($validatedData['department']);
            $validatedData['create_user_id'] = auth()->id();
            Designation::create($validatedData);

            return redirect()->route('designations.index')->with('success', 'Designation created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create designation: ' . $e->getMessage()]);
        }
    }

    public function edit(Designation $designation)
    {
        if (!auth()->user()->hasPermissionTo('designation_edit')) {
            abort(403, 'Unauthorized');
        }
        abort_if($designation->reserved_id != null, 403, 'System reserved designation cannot be edited.');
        $departments = Department::get();
        return view('hrm.designations.edit', compact('designation','departments'));
    }

    public function update(Request $request, Designation $designation)
    {
        if (!auth()->user()->hasPermissionTo('designation_edit')) {
            abort(403, 'Unauthorized');
        }
        abort_if($designation->reserved_id != null, 403, 'System reserved designation cannot be updated.');
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('designations')
                    ->where('department_id',$request->department)
                    ->ignore($designation)

            ],
            'department'=>'required'
        ]);
        try {
            $validatedData['alter_user_id'] = auth()->id();
            $validatedData['department_id'] = $validatedData['department'];
            unset($validatedData['department']);
            $designation->update($validatedData);
            return redirect()->route('designations.index')->with('success', 'Designation updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update designation: ' . $e->getMessage()]);
        }
    }

    public function destroy(Designation $designation)
    {
        try {
            if (!auth()->user()->hasPermissionTo('designation_delete')) {
                abort(403, 'Unauthorized');
            }
            if ($designation->reserved_id != null) {
                throw new \Exception("System reserved designation cannot be deleted");
            }
            $employee = Employee::where('designation_id',$designation->id)->first();
            if ($employee){
                throw new \Exception("This designation cannot delete, Its have employee logs");
            }
            $designation->delete();
            return response()->json(['success'=>true,'message'=>'Designation deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
