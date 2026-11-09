<?php

namespace App\Http\Controllers\ManagerManagement;
use App\Http\Controllers\Controller;

use App\Enumeration\RoleType;
use App\Models\ManagerManagement\ManagerAssignment;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ManagerAssignmentController extends Controller
{
    public function index()
    {
        $type = request()->routeIs('area-manager-assignments.*') ? 'Area_Manager' : 'Branch_Manager';
        $title = $type == 'Area_Manager' ? 'Area Manager Assignments' : 'Branch Manager Assignments';
        
        return view('manager_management.manager_assignment.index', compact('type', 'title'));
    }

    public function dataTable(Request $request)
    {
        $query = ManagerAssignment::with(['manager', 'sisterConcern']);

        if ($request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->manager_type != '') {
            $query->where('manager_type', $request->manager_type);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('manager_name', function (ManagerAssignment $assignment) {
                return $assignment->manager->name ?? 'N/A';
            })
            ->addColumn('branch_name', function (ManagerAssignment $assignment) {
                return $assignment->sisterConcern->name ?? 'N/A';
            })
            ->addColumn('status', function (ManagerAssignment $assignment) {
                if ($assignment->status == 1)
                    return '<span class="badge badge-success">Active</span>';
                else
                    return '<span class="badge badge-danger">Transferred/Inactive</span>';
            })
            ->addColumn('action', function (ManagerAssignment $assignment) {
                if ($assignment->status == 1) {
                    $managerName = $assignment->manager->name ?? 'N/A';
                    $branchName = $assignment->sisterConcern->name ?? 'N/A';
                    return '<button class="btn btn-danger btn-sm btn-release" data-id="'.$assignment->id.'" data-name="'.$managerName.'" data-branch="'.$branchName.'">Release</button>';
                }
                return '';
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'Branch_Manager');
        
        // Only show branches that DON'T have an active manager of this type
        // AND exclude Head Office (ID: 1)
        $occupiedBranchIds = ManagerAssignment::where('manager_type', $type)
            ->where('status', 1)
            ->pluck('sister_concern_id');

        $branches = SisterConcern::where('status', 1)
            ->whereNotIn('id', $occupiedBranchIds)
            ->get();

        $areaManagers = Employee::areaManagers()->where('status', 1)->get();
        $branchManagers = Employee::branchManagers()->where('status', 1)->get();

        if ($request->ajax()) {
            return response()->json(['branches' => $branches]);
        }

        return view('manager_management.manager_assignment.create', compact('branches', 'areaManagers', 'branchManagers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'manager_type' => 'required|in:Area_Manager,Branch_Manager',
            'manager_id' => 'required',
            'sister_concern_id' => 'required',
            'assigned_date' => 'required|date',
        ]);

        // Prevent assignment to Head Office (ID: 1)
        // if ($request->sister_concern_id == 1) {
        //     return redirect()->back()->with('error', 'Head Office management is handled separately. You cannot assign managers here.')->withInput();
        // }

        // Check if branch is already occupied (Double check for safety)
        $isOccupied = ManagerAssignment::where('sister_concern_id', $request->sister_concern_id)
            ->where('manager_type', $request->manager_type)
            ->where('status', 1)
            ->exists();

        if ($isOccupied) {
            return redirect()->back()->with('error', 'This branch already has an active manager of this type. Please release them first.')->withInput();
        }

        // Special check for Branch Manager: Cannot be in two branches at once
        if ($request->manager_type == RoleType::$BRANCH_MANAGER) {
            $currentAssignment = ManagerAssignment::where('manager_id', $request->manager_id)
                ->where('manager_type', RoleType::$BRANCH_MANAGER)
                ->where('status', 1)
                ->first();

            if ($currentAssignment) {
                return redirect()->back()->with('error', 'This manager is already active in ' . $currentAssignment->sisterConcern->name . '. Please release them from there first.')->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $assignedDate = Carbon::parse($request->assigned_date)->format('Y-m-d');

            // Create New Assignment
            ManagerAssignment::create([
                'manager_id' => $request->manager_id,
                'sister_concern_id' => $request->sister_concern_id,
                'manager_type' => $request->manager_type,
                'assigned_date' => $assignedDate,
                'status' => 1
            ]);

            // Update the SisterConcern (Branch) table for current status
            $branch = SisterConcern::find($request->sister_concern_id);
            if ($request->manager_type == RoleType::$AREA_MANAGER) {
                $branch->area_manager_id = $request->manager_id;
            } else {
                $branch->branch_manager_id = $request->manager_id;
            }
            $branch->save();

            DB::commit();
            $routeName = $request->manager_type == 'Area_Manager' ? 'area-manager-assignments.index' : 'branch-manager-assignments.index';
            return redirect()->route($routeName)->with('success', 'Manager assigned successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function release(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:manager_assignments,id',
            'unassigned_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $assignment = ManagerAssignment::findOrFail($request->id);
            $unassignedDate = Carbon::parse($request->unassigned_date)->format('Y-m-d');

            $assignment->update([
                'status' => 0,
                'unassigned_date' => $unassignedDate
            ]);

            // Clear from SisterConcern table
            $branch = SisterConcern::find($assignment->sister_concern_id);
            if ($assignment->manager_type == RoleType::$AREA_MANAGER) {
                if ($branch->area_manager_id == $assignment->manager_id) {
                    $branch->area_manager_id = null;
                }
            } else {
                if ($branch->branch_manager_id == $assignment->manager_id) {
                    $branch->branch_manager_id = null;
                }
            }
            $branch->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Manager released successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
