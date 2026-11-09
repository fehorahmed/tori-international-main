<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;

use App\Models\Hrm\Employee;
use App\Models\Hrm\Leave;
use App\Models\Hrm\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LeaveController extends Controller
{
    public function index()
    {
        try {
            return view('hrm.leave.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function dataTable()
    {
        $query = Leave::with('employee','leaveType')->where('sister_concern_id',auth()->user()->activeConcernId());
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Leave $leave) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('leave_edit')) {
                    $btn .= '<a href="' . route('leave.edit', ['leave' => $leave->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('leave_delete')) {
                    $btn .= ' <a role="button" data-id="' . $leave->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })
            ->addColumn('employee_name', function(Leave $leave) {
                return $leave->employee->name ?? '';
            })
            ->addColumn('leave_type_name', function(Leave $leave) {
                return $leave->leaveType->name ?? '';
            })
            ->addColumn('total_days', function(Leave $leave) {
                $startDate = Carbon::parse($leave->start_date);
                $endDate = Carbon::parse($leave->end_date);
                return $startDate->diffInDays($endDate) + 1; // Including the start date
            })
            ->editColumn('start_date', function(Leave $leave) {
                return Carbon::parse($leave->start_date)->format('d-m-Y');
            })
            ->editColumn('end_date', function(Leave $leave) {
                return Carbon::parse($leave->end_date)->format('d-m-Y');
            })
            ->editColumn('status', function(Leave $leave) {
                if ($leave->status == 'pending'){
                    return '<span class="badge badge-warning">Pending</span>';
                }elseif ($leave->status == 'approved'){
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
        if (!auth()->user()->hasPermissionTo('leave_create')) {
            abort(403, 'Unauthorized');
        }
        $leaveTypes = LeaveType::get();
        $employees = Employee::where('sister_concern_id',auth()->user()->activeConcernId())->get();
        return view('hrm.leave.create',compact('leaveTypes','employees'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('leave_create')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'employee' => 'required|exists:employees,id',
            'leave_type' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|string|in:pending,approved,rejected',
        ]);
        try {
            $validatedData['sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['employee_id'] = $validatedData['employee'];
            $validatedData['leave_type_id'] = $validatedData['leave_type'];
            $validatedData['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $validatedData['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            $validatedData['create_user_id'] = auth()->id();
            unset($validatedData['employee']);
            unset($validatedData['leave_type']);
            Leave::create($validatedData);
            return redirect()->route('leave.index')->with('success', 'Leave application submitted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to submit leave application: ' . $e->getMessage()]);
        }
    }

    public function edit(Leave $leave)
    {
        if (!auth()->user()->hasPermissionTo('leave_edit')) {
            abort(403, 'Unauthorized');
        }else{
            if($leave->sister_concern_id != auth()->user()->activeConcernId()){
                abort(403, 'Unauthorized');
            }
        }
        $leaveTypes = LeaveType::get();
        $employees = Employee::where('sister_concern_id',auth()->user()->activeConcernId())->get();
        return view('hrm.leave.edit', compact('leave','employees','leaveTypes'));
    }

    public function update(Request $request, Leave $leave)
    {
        if (!auth()->user()->hasPermissionTo('leave_edit')) {
            abort(403, 'Unauthorized');
        }else{
            if($leave->sister_concern_id != auth()->user()->activeConcernId()){
                abort(403, 'Unauthorized');
            }
        }
        $validatedData = $request->validate([
            'employee' => 'required|exists:employees,id',
            'leave_type' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|string|in:pending,approved,rejected',
        ]);
        try {
            $validatedData['sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['employee_id'] = $validatedData['employee'];
            $validatedData['leave_type_id'] = $validatedData['leave_type'];
            $validatedData['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $validatedData['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            $validatedData['alter_user_id'] = auth()->id();
            unset($validatedData['employee']);
            unset($validatedData['leave_type']);
            $leave->update($validatedData);
            return redirect()->route('leave.index')->with('success', 'Leave application updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update leave application: ' . $e->getMessage()]);
        }
    }

    public function destroy(Leave $leave)
    {
        try {
            if (!auth()->user()->hasPermissionTo('leave_delete')) {
                abort(403, 'Unauthorized');
            }
            if ($leave->status == 'approved'){
                throw new \Exception('Already approved!');
            }
            $leave->delete();
            return response()->json(['success'=>true,'message'=>'Leave deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'Failed to delete leave: ' . $e->getMessage()]);
        }
    }
}
