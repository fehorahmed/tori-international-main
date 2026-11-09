<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiAdvance\ShomriddhiGroup;
use App\Models\ShomriddhiAdvance\ShomriddhiFieldOfficerAssignment;
use App\Models\Hrm\Employee;
use App\Models\Division;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class GroupController extends Controller
{
    public function index()
    {
        $divisions = Division::orderBy('name')->get();
        return view('shomriddhi_advance.groups.index', compact('divisions'));
    }

    public function dataTable(Request $request)
    {
        $query = ShomriddhiGroup::with('fieldOfficer');

        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }
        if ($request->upazila_id) {
            $query->where('upazila_id', $request->upazila_id);
        }
        if ($request->union_id) {
            if (strpos($request->union_id, 'union_') !== false) {
                $query->where('union_id', str_replace('union_', '', $request->union_id));
            } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                $query->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
            }
        }
        if ($request->village_id) {
            $query->where('village_id', $request->village_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('field_officer', function (ShomriddhiGroup $group) {
                return $group->fieldOfficer ? $group->fieldOfficer->id_no . ' - ' . $group->fieldOfficer->name : '<span class="text-danger">Unassigned</span>';
            })
            ->addColumn('action', function (ShomriddhiGroup $group) {
                $btn = '<a href="' . route('shomriddhi-advance.groups.show', $group->id) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-eye"></i></a>';
                $btn .= ' <a href="' . route('shomriddhi-advance.groups.edit', $group->id) . '" class="btn btn-info btn-sm"><i class="fa fa-edit"></i></a>';
                
                if ($group->members()->count() > 0) {
                    $btn .= ' <button type="button" class="btn btn-secondary btn-sm" disabled title="Locked: Group has members"><i class="fa fa-lock"></i></button>';
                } else {
                    $btn .= ' <a role="button" data-id="' . $group->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                
                return dropdownMenuContainer($btn);
            })
            ->addColumn('status', function (ShomriddhiGroup $group) {
                return $group->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->rawColumns(['action', 'status', 'field_officer'])
            ->toJson();
    }

    public function create()
    {
        $divisions = Division::orderBy('name')->get();
        $fieldOfficers = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->fieldOfficers()
            ->orderBy('name')
            ->get();
        return view('shomriddhi_advance.groups.create', compact('divisions', 'fieldOfficers'));
    }

    public function show($id)
    {
        $group = ShomriddhiGroup::with(['members', 'division', 'district', 'upazila', 'union', 'pourashava', 'village'])->findOrFail($id);
        return view('shomriddhi_advance.groups.show', compact('group'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|max:255',
            'name_bn' => 'nullable|max:255',
            'opening_date' => 'nullable|date',
            'meeting_time' => 'nullable|string|max:255',
            'collection_day' => 'required|integer|between:0,6',
        ]);

        $group = new ShomriddhiGroup();
        $group->name_en = $request->name_en;
        $group->name_bn = $request->name_bn;
        $group->opening_date = $request->opening_date ? \Carbon\Carbon::parse($request->opening_date)->format('Y-m-d') : null;
        $group->meeting_time = $request->meeting_time;
        $group->collection_day = $request->collection_day;
        $group->division_id = $request->division;
        $group->district_id = $request->district;
        $group->upazila_id = $request->upazila;
        
        if ($request->union) {
            if (strpos($request->union, 'union_') !== false) {
                $group->union_id = str_replace('union_', '', $request->union);
            } elseif (strpos($request->union, 'pourashava_') !== false) {
                $group->pourashava_id = str_replace('pourashava_', '', $request->union);
            }
        }
        $group->village_id = $request->village_id;
        $group->status = $request->status ?? 1;

        // Auto generate code 001, 002, 003...
        $lastGroup = ShomriddhiGroup::orderBy('id', 'desc')->first();
        if ($lastGroup) {
            $nextCode = intval($lastGroup->code) + 1;
        } else {
            $nextCode = 1;
        }
        $group->code = str_pad($nextCode, 3, '0', STR_PAD_LEFT);
        $group->field_officer_id = $request->field_officer_id;
        
        $group->save();

        if ($group->field_officer_id) {
            ShomriddhiFieldOfficerAssignment::create([
                'sister_concern_id' => auth()->user()->activeConcernId(),
                'shomriddhi_group_id' => $group->id,
                'field_officer_id' => $group->field_officer_id,
                'from_date' => Carbon::now()->format('Y-m-d'),
                'status' => 1,
                'assigned_by' => auth()->id()
            ]);
        }

        return redirect()->route('shomriddhi-advance.groups.index')->with('success', 'Group created successfully.');
    }

    public function edit(ShomriddhiGroup $group)
    {
        $divisions = Division::orderBy('name')->get();
        $fieldOfficers = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->fieldOfficers()
            ->orderBy('name')
            ->get();
        return view('shomriddhi_advance.groups.edit', compact('group', 'divisions', 'fieldOfficers'));
    }

    public function update(Request $request, ShomriddhiGroup $group)
    {
        $request->validate([
            'name_en' => 'required|max:255',
            'name_bn' => 'nullable|max:255',
            'opening_date' => 'nullable|date',
            'meeting_time' => 'nullable|string|max:255',
            'collection_day' => 'required|integer|between:0,6',
        ]);

        $group->name_en = $request->name_en;
        $group->name_bn = $request->name_bn;
        $group->opening_date = $request->opening_date ? \Carbon\Carbon::parse($request->opening_date)->format('Y-m-d') : null;
        $group->meeting_time = $request->meeting_time;
        $group->collection_day = $request->collection_day;
        $group->division_id = $request->division;
        $group->district_id = $request->district;
        $group->upazila_id = $request->upazila;
        
        if ($request->union) {
            if (strpos($request->union, 'union_') !== false) {
                $group->union_id = str_replace('union_', '', $request->union);
                $group->pourashava_id = null;
            } elseif (strpos($request->union, 'pourashava_') !== false) {
                $group->pourashava_id = str_replace('pourashava_', '', $request->union);
                $group->union_id = null;
            }
        } else {
            $group->union_id = null;
            $group->pourashava_id = null;
        }
        $oldFieldOfficerId = $group->field_officer_id;
        
        $group->village_id = $request->village_id;
        $group->field_officer_id = $request->field_officer_id;
        $group->status = $request->status ?? 1;
        $group->save();

        if ($oldFieldOfficerId != $group->field_officer_id) {
            // Close previous assignment
            if ($oldFieldOfficerId) {
                ShomriddhiFieldOfficerAssignment::where('shomriddhi_group_id', $group->id)
                    ->where('status', 1)
                    ->update([
                        'to_date' => Carbon::now()->subDay()->format('Y-m-d'),
                        'status' => 0
                    ]);
            }

            // Create new assignment
            if ($group->field_officer_id) {
                ShomriddhiFieldOfficerAssignment::create([
                    'sister_concern_id' => auth()->user()->activeConcernId(),
                    'shomriddhi_group_id' => $group->id,
                    'field_officer_id' => $group->field_officer_id,
                    'from_date' => Carbon::now()->format('Y-m-d'),
                    'status' => 1,
                    'assigned_by' => auth()->id()
                ]);
            }
        }

        return redirect()->route('shomriddhi-advance.groups.index')->with('success', 'Group updated successfully.');
    }

    public function destroy(ShomriddhiGroup $group)
    {
        try {
            if ($group->members()->exists()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete! This group has active members.'], 200);
            }
            
            $group->delete();
            return response()->json(['success' => true, 'message' => 'Group deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 200);
        }
    }
}
