<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployeeMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmployeeMovementController extends Controller
{
    public function index()
    {
        try {
            return view('hrm.employee_movements.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch employee movements. Please try again.');
        }
    }
    public function dataTable()
    {
        $query = EmployeeMovement::with('employee')->where('sister_concern_id',auth()->user()->activeConcernId());
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(EmployeeMovement $employee_movement) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('employee_movement_edit')) {
                    $btn .= '<a href="' . route('employee-movements.edit', ['employee_movement' => $employee_movement->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('employee_movement_delete')) {
                    $btn .= ' <a role="button" data-id="' . $employee_movement->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })
            ->addColumn('employee_name', function(EmployeeMovement $employee_movement) {
                return $employee_movement->employee->name ?? '';
            })

            ->addColumn('total_days', function(EmployeeMovement $employee_movement) {
                $startDate = Carbon::parse($employee_movement->start_date);
                $endDate = Carbon::parse($employee_movement->end_date);
                return $startDate->diffInDays($endDate) + 1; // Including the start date
            })
            ->editColumn('start_date', function(EmployeeMovement $employee_movement) {
                return Carbon::parse($employee_movement->start_date)->format('d-m-Y');
            })
            ->editColumn('end_date', function(EmployeeMovement $employee_movement) {
                return Carbon::parse($employee_movement->end_date)->format('d-m-Y');
            })
            ->editColumn('status', function(EmployeeMovement $employee_movement) {
                if ($employee_movement->status == 'pending'){
                    return '<span class="badge badge-warning">Pending</span>';
                }elseif ($employee_movement->status == 'approved'){
                    return '<span class="badge badge-success">Approved</span>';
                }else{
                    return '<span class="badge badge-danger">Rejected</span>';
                }
            })
            ->rawColumns(['action','status'])
            ->toJson();
    }
    public function create()
    {
        try {
            if (!auth()->user()->hasPermissionTo('employee_movement_create')) {
                abort(403, 'Unauthorized');
            }

            $employees = Employee::where('sister_concern_id',auth()->user()->activeConcernId())->get();
            return view('hrm.employee_movements.create', compact('employees'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load create movement form. Please try again.');
        }
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('employee_movement_create')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'employee' => 'required|exists:employees,id',
            'from_location' => 'required|max:255',
            'to_location' => 'required|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        try {
            $validatedData['sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['employee_id'] = $validatedData['employee'];
            $validatedData['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $validatedData['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            $validatedData['create_user_id'] = auth()->id();
            unset($validatedData['employee']);
            EmployeeMovement::create($validatedData);
            return redirect()->route('employee-movements.index')->with('success', 'Employee movement recorded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to record employee movement. Please try again.');
        }
    }

    public function edit(EmployeeMovement $employee_movement)
    {
        try {
            if (!auth()->user()->hasPermissionTo('employee_movement_edit')) {
                abort(403, 'Unauthorized');
            }else{
                if($employee_movement->sister_concern_id != auth()->user()->activeConcernId()){
                    abort(403, 'Unauthorized');
                }
            }
            $employees = Employee::where('sister_concern_id',auth()->user()->activeConcernId())->get();
            return view('hrm.employee_movements.edit', compact('employee_movement', 'employees'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load edit movement form. Please try again.');
        }
    }

    public function update(Request $request, EmployeeMovement $employee_movement)
    {
        if (!auth()->user()->hasPermissionTo('employee_movement_edit')) {
            abort(403, 'Unauthorized');
        }else{
            if($employee_movement->sister_concern_id != auth()->user()->activeConcernId()){
                abort(403, 'Unauthorized');
            }
        }
        $validatedData = $request->validate([
            'employee' => 'required|exists:employees,id',
            'from_location' => 'required|max:255',
            'to_location' => 'required|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        try {
            $validatedData['sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['employee_id'] = $validatedData['employee'];
            $validatedData['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $validatedData['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            $validatedData['alter_user_id'] = auth()->id();
            unset($validatedData['employee']);
            $employee_movement->update($validatedData);
            return redirect()->route('employee-movements.index')->with('success', 'Employee movement updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update employee movement. Please try again.');
        }
    }

    public function destroy(EmployeeMovement $employee_movement)
    {
        try {
            if (!auth()->user()->hasPermissionTo('employee_movement_delete')) {
                abort(403, 'Unauthorized');
            }
            if ($employee_movement->status == 'approved'){
                throw new \Exception('Already approved!');
            }
            $employee_movement->delete();
            return response()->json(['success'=>true,'message'=>'Employee movement deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'Failed to delete employee movement: ' . $e->getMessage()]);
        }
    }
}
