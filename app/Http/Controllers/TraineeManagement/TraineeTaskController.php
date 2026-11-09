<?php

namespace App\Http\Controllers\TraineeManagement;

use App\Http\Controllers\Controller;

use App\Enumeration\RoleType;
use App\Models\Sales\TaskAssignment;
use App\Models\Sales\TaskItem;
use App\Models\Sales\TaskType;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class TraineeTaskController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_tasks'), 403, 'Unauthorized');
        $businessDate = auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now();
        $dateStr = $request->date ? str_replace('/', '-', $request->date) : null;
        if ($dateStr && strlen($dateStr) == 7 && $dateStr[2] == '-') $dateStr = '01-' . $dateStr;
        $date = $dateStr ? Carbon::parse($dateStr)->startOfMonth()->format('Y-m-d') : '';
        return view('trainee_management.tasks.index', compact('date'));
    }

    public function dataTable(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_tasks'), 403, 'Unauthorized');
        $user = auth()->user();
        $query = TaskAssignment::with(['employee', 'items.type'])
            ->withCount('items')
            ->where('target_role', 'Trainee');

        // Branch-wise filtering
        if ($user->role != 'Admin') {
            $query->where('sister_concern_id', $user->sister_concern_id);
        }

        if ($request->date != '') {
            $dateStr = str_replace('/', '-', $request->date);
            if (strlen($dateStr) == 7 && $dateStr[2] == '-') $dateStr = '01-' . $dateStr;
            $date = Carbon::parse($dateStr)->startOfMonth()->format('Y-m-d');
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
            ->addColumn('action', function ($row) {
                $showUrl = route('trainee.tasks.show', ['id' => $row->id]);
                $editUrl = route('trainee.tasks.distribute', ['month' => Carbon::parse($row->date)->format('m-Y')]);
                
                $btn = '<a href="' . $showUrl . '" class="btn btn-primary btn-sm mr-1" title="Details"><i class="fa fa-eye"></i></a>';
                $btn .= '<a href="' . $editUrl . '" class="btn btn-info btn-sm" title="Edit Month Targets"><i class="fa fa-edit"></i></a>';
                
                return $btn;
            })
            ->rawColumns(['task_count', 'tasks_summary', 'action'])
            ->make(true);
    }

    public function distribute(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_tasks_create'), 403, 'Unauthorized');
        $user = auth()->user();
        $businessDate = $user->activeSisterConcern->business_date ?? $user->sisterConcern->business_date ?? now();
        $monthStr = $request->month ? str_replace('/', '-', $request->month) : null;
        if ($monthStr && strlen($monthStr) == 7 && $monthStr[2] == '-') $monthStr = '01-' . $monthStr;
        $month = $monthStr ? Carbon::parse($monthStr)->startOfMonth()->format('Y-m-d') : Carbon::parse($businessDate)->startOfMonth()->format('Y-m-d');
        $formattedMonth = Carbon::parse($month)->format('Y-m');

        // Get all active trainees in this branch
        $query = Employee::trainees()->where('status', 1);
        
        if ($user->role != 'Admin') {
            $query->where('sister_concern_id', $user->sister_concern_id);
        }
        
        $trainees = $query->get();
        
        // Load existing assignments for Visit Count (ID 2)
        foreach ($trainees as $trainee) {
            $assignment = TaskAssignment::where('employee_id', $trainee->id)
                ->where('date', $month)
                ->where('target_role', 'Trainee')
                ->first();
            
            if ($assignment) {
                $trainee->target_value = TaskItem::where('task_assignment_id', $assignment->id)
                    ->where('task_type_id', 2) // Visit Count Target
                    ->value('target_value') ?? 0;
            } else {
                $trainee->target_value = 0;
            }
        }

        return view('trainee_management.tasks.distribute', compact('trainees', 'formattedMonth'));
    }

    public function storeDistribute(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_tasks_create'), 403, 'Unauthorized');
        $user = auth()->user();
        
        $request->validate([
            'month' => 'required',
            'targets' => 'required|array',
            'targets.*' => 'nullable|numeric|min:0',
        ]);

        $monthStr = str_replace('/', '-', $request->month);
        if (strlen($monthStr) == 7 && $monthStr[2] == '-') $monthStr = '01-' . $monthStr;
        $month = Carbon::parse($monthStr)->startOfMonth()->format('Y-m-d');

        DB::beginTransaction();
        try {
            foreach ($request->targets as $targetUserId => $amount) {
                $trainee = Employee::find($targetUserId);
                if (!$trainee) continue;

                $assignment = TaskAssignment::updateOrCreate(
                    [
                        'employee_id' => $targetUserId,
                        'date' => $month,
                        'target_role' => 'Trainee',
                    ],
                    [
                        'sister_concern_id' => $trainee->sister_concern_id ?? null,
                        'created_by' => $user->id,
                    ]
                );

                TaskItem::updateOrCreate(
                    [
                        'task_assignment_id' => $assignment->id,
                        'task_type_id' => 2, // Visit Count Target
                    ],
                    [
                        'target_value' => $amount ?? 0,
                    ]
                );
            }

            DB::commit();
            return redirect()->route('trainee.tasks.index')->with('success', 'Trainee visit targets updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_tasks'), 403, 'Unauthorized');
        $task = TaskAssignment::with('employee', 'items.type')->findOrFail($id);
        if ($task->target_role != 'Trainee') abort(403);
        
        return view('trainee_management.tasks.show', compact('task'));
    }

    public function manageTasks($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_tasks_edit'), 403, 'Unauthorized');
        $assignment = TaskAssignment::with('employee', 'items')->findOrFail($id);
        if ($assignment->target_role != 'Trainee') abort(403);
        
        $taskTypes = TaskType::all();
        $existingItems = $assignment->items->pluck('target_value', 'task_type_id')->toArray();

        return view('trainee_management.tasks.manage', compact('assignment', 'taskTypes', 'existingItems'));
    }

    public function updateTasks(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_tasks_edit'), 403, 'Unauthorized');
        $assignment = TaskAssignment::findOrFail($id);
        if ($assignment->target_role != 'Trainee') abort(403);
        
        $request->validate([
            'targets' => 'required|array',
            'targets.*' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->targets as $typeId => $value) {
                TaskItem::updateOrCreate(
                    [
                        'task_assignment_id' => $assignment->id,
                        'task_type_id' => $typeId,
                    ],
                    [
                        'target_value' => $value ?? 0,
                    ]
                );
            }

            DB::commit();
            return redirect()->route('trainee.tasks.index')->with('success', 'Tasks updated successfully for ' . ($assignment->employee->name ?? 'User'));
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function calculateActualValue($traineeId, $slug, $month)
    {
        $monthDate = Carbon::parse($month);
        $start = $monthDate->copy()->startOfMonth()->format('Y-m-d');
        $end = $monthDate->copy()->endOfMonth()->format('Y-m-d');

        switch ($slug) {
            case 'visit_count':
                return DB::table('trainee_visits')
                    ->where('trainee_id', $traineeId)
                    ->whereBetween('date', [$start, $end])
                    ->count();

            // Add other task slugs here if needed in the future
            default:
                return 0;
        }
    }
}
