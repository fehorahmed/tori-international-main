<?php

namespace App\Http\Controllers\TraineeManagement;

use App\Http\Controllers\Controller;
use App\Models\TraineeManagement\TraineeVisitType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TraineeVisitTypeController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits'), 403, 'Unauthorized');
        return view('trainee_management.visit_type.index');
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits'), 403, 'Unauthorized');
        $query = TraineeVisitType::where('sister_concern_id', auth()->user()->activeConcernId());

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (TraineeVisitType $type) {
                return '<a href="' . route('trainee.visit_type.edit', $type->id) . '" class="btn btn-info btn-sm"><i class="fas fa-edit"></i> Edit</a>';
            })
            ->editColumn('status', function (TraineeVisitType $type) {
                return $type->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits_create'), 403, 'Unauthorized');
        return view('trainee_management.visit_type.create');
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits_create'), 403, 'Unauthorized');
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        TraineeVisitType::create([
            'sister_concern_id' => auth()->user()->activeConcernId(),
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return redirect()->route('trainee.visit_type.index')->with('success', 'Visit Type created successfully');
    }

    public function edit(TraineeVisitType $traineeVisitType)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits_edit'), 403, 'Unauthorized');
        if ($traineeVisitType->sister_concern_id != auth()->user()->activeConcernId()) {
            abort(403);
        }
        return view('trainee_management.visit_type.edit', compact('traineeVisitType'));
    }

    public function update(Request $request, TraineeVisitType $traineeVisitType)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits_edit'), 403, 'Unauthorized');
        if ($traineeVisitType->sister_concern_id != auth()->user()->activeConcernId()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $traineeVisitType->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return redirect()->route('trainee.visit_type.index')->with('success', 'Visit Type updated successfully');
    }
}
