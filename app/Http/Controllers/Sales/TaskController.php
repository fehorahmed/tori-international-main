<?php

namespace App\Http\Controllers\Sales;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Sales\TaskAssignment;
use App\Models\Sales\TaskItem;
use App\Models\Sales\TaskType;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Get the child role based on current user or requested role
     */
    private function getTargetContext($role)
    {
        switch ($role) {
            case 'area_manager':
                return [
                    'child_type' => RoleType::$AREA_MANAGER,
                    'child_label' => 'Area_Manager',
                    'parent_role' => null, // Admin is parent
                    'permission' => 'area_manager_tasks'
                ];
            case 'branch_manager':
                return [
                    'child_type' => RoleType::$BRANCH_MANAGER,
                    'child_label' => 'Branch_Manager',
                    'parent_role' => 'Area_Manager',
                    'permission' => 'branch_manager_tasks'
                ];
            case 'sr':
                return [
                    'child_type' => RoleType::$SR,
                    'child_label' => 'SR',
                    'parent_role' => 'Branch_Manager',
                    'permission' => 'sr_tasks'
                ];
            default:
                abort(404);
        }
    }

    public function index(Request $request, $role)
    {
        $context = $this->getTargetContext($role);
        $date = $request->date ? Carbon::parse('01-' . str_replace('/', '-', $request->date))->startOfMonth()->format('Y-m-d') : '';
        
        return view('sales.tasks.index', compact('date', 'role', 'context'));
    }

    public function dataTable(Request $request, $role)
    {
        $context = $this->getTargetContext($role);
        $user = auth()->user();

        $query = TaskAssignment::with(['employee', 'items.type'])
            ->withCount('items')
            ->where('target_role', $context['child_label']);

        // Filtering based on who is logged in
        if ($user->role == 'Area_Manager') {
            // Area Manager can only see their branch managers' tasks
            // Logic to filter by area's branches would go here
        } elseif ($user->role == 'Branch_Manager') {
            $query->where('sister_concern_id', $user->sister_concern_id);
        }elseif ($user->role == 'SR') {
            $query->where('employee_id', $user->employee_id);
        }

        if ($request->date != '') {
            $date = Carbon::parse('01-' . str_replace('/', '-', $request->date))->startOfMonth()->format('Y-m-d');
            $query->where('date', $date);
        }

        $query->orderBy('date', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('F, Y');
            })
            ->addColumn('user_name', function ($row) {
                return $row->employee->name ?? 'N/A';
            })
            ->addColumn('task_count', function ($row) {
                return '<span class="badge badge-info">' . $row->items_count . ' Tasks</span>';
            })
            ->addColumn('tasks_summary', function ($row) {
                $output = '<ul class="list-unstyled mb-0" style="font-size: 0.85rem;">';
                foreach($row->items as $item) {
                    $val = $item->type && $item->type->unit == 'Amount' 
                        ? '৳' . number_format($item->target_value, 2) 
                        : number_format($item->target_value, 0) . ' ' . ($item->type->unit ?? '');
                    
                    $output .= '<li><span class="text-muted">' . ($item->type->name ?? 'Task') . ':</span> <strong>' . $val . '</strong></li>';
                }
                $output .= '</ul>';
                return $output;
            })
            ->addColumn('action', function ($row) use ($role) {
                $showUrl = route('tasks.show', ['role' => $role, 'id' => $row->id]);
                $editUrl = route('tasks.distribute', ['role' => $role, 'month' => Carbon::parse($row->date)->format('Y-m')]);
                
                $btn = '<a href="' . $showUrl . '" class="btn btn-primary btn-sm mr-1" title="Details"><i class="fa fa-eye"></i></a>';
                $btn .= '<a href="' . $editUrl . '" class="btn btn-info btn-sm mr-1" title="Distribute Targets"><i class="fa fa-share-alt"></i></a>';
                
                if ($role == 'sr') {
                    $manageUrl = route('tasks.manage', ['role' => $role, 'id' => $row->id]); 
                    $btn .= '<a href="' . $manageUrl . '" class="btn btn-success btn-sm" title="Manage All Tasks"><i class="fa fa-tasks"></i></a>';
                }
                
                return $btn;
            })
            ->rawColumns(['task_count', 'tasks_summary', 'action'])
            ->make(true);
    }

    public function distribute(Request $request, $role)
    {
        $context = $this->getTargetContext($role);
        $user = auth()->user();
        $month = $request->month ? $request->month . '-01' : now()->startOfMonth()->format('Y-m-d');
        $formattedMonth = Carbon::parse($month)->format('Y-m');

        $areaManagers = collect();
        $branchManagers = collect();
        $selectedAreaManagerId = null;
        $selectedBranchManagerId = null;
        
        $parentEmployeeId = null;

        if ($role == 'area_manager') {
            // No parent
        } elseif ($role == 'branch_manager') {
            // Dropdown logic for Area Managers
            if ($user->role == 'Area_Manager') {
                $areaManagers = Employee::where('id', $user->employee_id)->get();
                $selectedAreaManagerId = $user->employee_id;
            } else {
                // Admin, Sub-admin or any other role with permission
                $areaManagers = Employee::areaManagers()->where('status', 1)->get();
                $selectedAreaManagerId = $request->area_manager_id;
            }
            $parentEmployeeId = $selectedAreaManagerId;
        } elseif ($role == 'sr') {
            // Dropdown logic for Branch Managers
            if ($user->role == 'Branch_Manager') {
                $branchManagers = Employee::where('id', $user->employee_id)->get();
                $selectedBranchManagerId = $user->employee_id;
            } else if ($user->role == 'Area_Manager') {
                // Get branch managers under this Area Manager
                $branchIds = DB::table('sister_concerns')->where('area_manager_id', $user->employee_id)->pluck('id');
                $branchManagers = Employee::branchManagers()->whereIn('sister_concern_id', $branchIds)->where('status', 1)->get();
                $selectedBranchManagerId = $request->branch_manager_id;
            } else {
                // Admin, Sub-admin or any other role with permission
                $branchManagers = Employee::branchManagers()->where('status', 1)->get();
                $selectedBranchManagerId = $request->branch_manager_id;
            }
            $parentEmployeeId = $selectedBranchManagerId;
        }

        // Limit calculation
        $parentTargetAmount = 0;
        if ($role == 'area_manager') {
            $parentTargetAmount = -1; // Flexible
        } else {
            if ($context['parent_role'] && $parentEmployeeId) {
                $parentAssignment = TaskAssignment::where('employee_id', $parentEmployeeId)
                    ->where('date', $month)
                    ->where('target_role', $context['parent_role'])
                    ->first();
                
                if ($parentAssignment) {
                    $parentTargetAmount = TaskItem::where('task_assignment_id', $parentAssignment->id)
                        ->where('task_type_id', 1)
                        ->value('target_value') ?? 0;
                }
            }
        }

        // Get children
        $query = Employee::where('status', 1);
        if ($role == 'area_manager') {
            $query->areaManagers();
        } elseif ($role == 'branch_manager') {
            $query->branchManagers();
        } elseif ($role == 'sr') {
            $query->srs();
        }
        
        if ($role == 'area_manager') {
            // fetch all active area managers automatically
        } elseif ($role == 'branch_manager') {
            if ($parentEmployeeId) {
                $branchIds = DB::table('sister_concerns')->where('area_manager_id', $parentEmployeeId)->pluck('id');
                $query->whereIn('sister_concern_id', $branchIds);
            } else {
                $query->whereRaw('1 = 0'); // Empty until selected
            }
        } elseif ($role == 'sr') {
            if ($parentEmployeeId) {
                // Find the branch manager's sister concern
                $bm = Employee::find($parentEmployeeId);
                if ($bm) {
                    $query->where('sister_concern_id', $bm->sister_concern_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereRaw('1 = 0'); // Empty until selected
            }
        }

        $children = $query->get();
        
        // 3. Load existing assignments
        foreach ($children as $child) {
            $assignment = TaskAssignment::where('employee_id', $child->id)
                ->where('date', $month)
                ->where('target_role', $context['child_label'])
                ->first();
            
            if ($assignment) {
                $child->target_value = TaskItem::where('task_assignment_id', $assignment->id)
                    ->where('task_type_id', 1)
                    ->value('target_value') ?? 0;
            } else {
                $child->target_value = 0;
            }
        }

        return view('sales.tasks.distribute', compact('parentTargetAmount', 'children', 'formattedMonth', 'role', 'context', 'areaManagers', 'branchManagers', 'selectedAreaManagerId', 'selectedBranchManagerId'));
    }

    public function storeDistribute(Request $request, $role)
    {
        $context = $this->getTargetContext($role);
        $user = auth()->user();
        
        $request->validate([
            'month' => 'required',
            'targets' => 'nullable|array',
            'targets.*' => 'nullable|numeric|min:0',
        ]);

        $month = $request->month . '-01';

        $parentEmployeeId = null;
        if ($role == 'branch_manager') {
            $parentEmployeeId = $request->area_manager_id;
        } elseif ($role == 'sr') {
            $parentEmployeeId = $request->branch_manager_id;
        }

        // Validation against parent target
        if ($role != 'area_manager' && $context['parent_role'] && $parentEmployeeId) {
            $parentAssignment = TaskAssignment::where('employee_id', $parentEmployeeId)
                ->where('date', $month)
                ->where('target_role', $context['parent_role'])
                ->first();
            
            $parentLimit = 0;
            if ($parentAssignment) {
                $parentLimit = TaskItem::where('task_assignment_id', $parentAssignment->id)
                    ->where('task_type_id', 1)
                    ->value('target_value') ?? 0;
            }

            $totalDistributed = $request->targets ? collect($request->targets)->sum() : 0;
            if (round($totalDistributed, 2) > round($parentLimit, 2)) {
                return back()->with('error', 'Total distribution (৳'.number_format($totalDistributed, 2).') cannot exceed your assigned limit (৳'.number_format($parentLimit, 2).').');
            }
        }

        DB::beginTransaction();
        try {
            if ($request->targets) {
                foreach ($request->targets as $targetUserId => $amount) {
                    $child = Employee::find($targetUserId);
                    
                    $assignment = TaskAssignment::updateOrCreate(
                        [
                            'employee_id' => $targetUserId,
                            'date' => $month,
                            'target_role' => $context['child_label'],
                        ],
                        [
                            'sister_concern_id' => $child->sister_concern_id ?? null,
                            'created_by' => $user->id,
                        ]
                    );

                    TaskItem::updateOrCreate(
                        [
                            'task_assignment_id' => $assignment->id,
                            'task_type_id' => 1, // Sales Target
                        ],
                        [
                            'target_value' => $amount,
                        ]
                    );
                }
            }

            DB::commit();
            return redirect()->route('tasks.index', $role)->with('success', $context['child_label'] . ' targets updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($role, $id)
    {
        $context = $this->getTargetContext($role);
        $task = TaskAssignment::with('employee', 'items.type')->findOrFail($id);
        
        return view('sales.tasks.show', compact('task', 'role', 'context'));
    }

    public function manageTasks($role, $id)
    {
        $context = $this->getTargetContext($role);
        $assignment = TaskAssignment::with('employee', 'items')->findOrFail($id);
        $taskTypes = TaskType::all();
        
        // Map existing items to types for easier display
        $existingItems = $assignment->items->pluck('target_value', 'task_type_id')->toArray();

        return view('sales.tasks.manage', compact('assignment', 'taskTypes', 'existingItems', 'role', 'context'));
    }

    public function updateTasks(Request $request, $role, $id)
    {
        $context = $this->getTargetContext($role);
        $assignment = TaskAssignment::findOrFail($id);
        
        $request->validate([
            'targets' => 'required|array',
            'targets.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->targets as $typeId => $value) {
                // Security check: Never update Sales Target (ID 1) from this page
                if ($typeId == 1) continue;

                TaskItem::updateOrCreate(
                    [
                        'task_assignment_id' => $assignment->id,
                        'task_type_id' => $typeId,
                    ],
                    [
                        'target_value' => $value,
                    ]
                );
            }

            DB::commit();
            return redirect()->route('tasks.index', $role)->with('success', 'Tasks updated successfully for ' . ($assignment->employee->name ?? 'User'));
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
