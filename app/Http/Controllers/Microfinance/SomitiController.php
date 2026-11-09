<?php

namespace App\Http\Controllers\Microfinance;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Microfinance\MfSomiti;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class SomitiController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_somiti'), 403, 'Unauthorized');
        if ($request->ajax()) {
            $data = MfSomiti::withCount('members')->where('sister_concern_id', auth()->user()->activeConcernId())->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('field_officer', function($row) {
                    return $row->fieldOfficer ? $row->fieldOfficer->name : 'N/A';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('microfinance.somiti.show', $row->id).'" class="btn btn-info btn-sm" title="View Details"><i class="fa fa-eye"></i></a>';
                    
                    if ($row->members_count > 0) {
                        $btn .= ' <button class="btn btn-secondary btn-sm" disabled title="Locked: Members exist"><i class="fa fa-lock"></i></button>';
                    } else {
                        $btn .= ' <a href="'.route('microfinance.somiti.edit', $row->id).'" class="edit btn btn-primary btn-sm" title="Edit"><i class="fa fa-edit"></i></a>';
                        $btn .= ' <button type="button" class="delete btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'" title="Delete"><i class="fa fa-trash"></i></button>';
                    }
                    return $btn;
                })
                ->editColumn('status', function($row) {
                    return $row->status ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
                })
                ->rawColumns(['action', 'status', 'field_officer'])
                ->make(true);
        }
        $pageTitle = 'Somiti List';
        return view('microfinance.somiti.index', compact('pageTitle'));
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_somiti_create'), 403, 'Unauthorized');
        $pageTitle = 'Add New Somiti';
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $fieldOfficers = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->fieldOfficers()
            ->orderBy('name')
            ->get();
        
        // Generate Next Somiti Code
        $nextCode = $this->generateSomitiCode();

        return view('microfinance.somiti.create', compact('pageTitle', 'days', 'fieldOfficers', 'nextCode'));
    }

    private function generateSomitiCode()
    {
        $sister_concern_id = auth()->user()->activeConcernId();
        $prefix = 'S';
        $lastSomiti = MfSomiti::where('sister_concern_id', $sister_concern_id)
            ->where('somiti_code', 'LIKE', $prefix . '-%')
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastSomiti) {
            $lastCode = $lastSomiti->somiti_code;
            $lastNumber = (int) str_replace($prefix . '-', '', $lastCode);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_somiti_create'), 403, 'Unauthorized');
        $request->validate([
            'name' => 'required|string|max:255',
            'somiti_type' => 'required|integer|in:1,2,3',
            'max_member_limit' => 'required|integer|min:1',
            'field_officer_id' => 'required|exists:employees,id',
            'meeting_day' => 'required|string',
            'opening_date' => 'required|string',
            'collection_start_date' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();
            $data['sister_concern_id'] = auth()->user()->activeConcernId();
            $data['created_by'] = auth()->id();
            $data['somiti_code'] = $this->generateSomitiCode();

            // Format Dates
            if ($request->opening_date) {
                $data['opening_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $request->opening_date)->format('Y-m-d');
            }
            if ($request->collection_start_date) {
                $data['collection_start_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $request->collection_start_date)->format('Y-m-d');
            }

            $somiti = MfSomiti::create($data);

            // Auto-create initial active field officer assignment
            \App\Models\Microfinance\MfFieldOfficerAssignment::create([
                'sister_concern_id' => $somiti->sister_concern_id,
                'somiti_id' => $somiti->id,
                'field_officer_id' => $somiti->field_officer_id,
                'assigned_by' => auth()->id(),
                'from_date' => $somiti->opening_date ?? date('Y-m-d'),
                'status' => 1,
                'change_reason' => 'Initial Somiti Assignment',
            ]);

            DB::commit();

            return redirect()->route('microfinance.somiti.index')->with('success', 'Somiti created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create Somiti: ' . $e->getMessage());
        }
    }

    public function show(MfSomiti $somiti)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_somiti'), 403, 'Unauthorized');
        abort_if($somiti->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        $pageTitle = 'Somiti Details: ' . $somiti->name;
        $somiti->load([
            'fieldOfficer',
            'members.accounts.product',
            'fieldOfficerAssignments.fieldOfficer',
            'fieldOfficerAssignments.assignedBy'
        ]);
        return view('microfinance.somiti.show', compact('pageTitle', 'somiti'));
    }

    public function edit(MfSomiti $somiti)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_somiti_edit'), 403, 'Unauthorized');
        abort_if($somiti->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        if ($somiti->members()->count() > 0) {
            return redirect()->route('microfinance.somiti.index')->with('error', 'Action denied: Members already exist in this somiti.');
        }
        $pageTitle = 'Edit Somiti: ' . $somiti->name;
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $fieldOfficers = \App\Models\Hrm\Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->fieldOfficers()
            ->orderBy('name')
            ->get();
        return view('microfinance.somiti.edit', compact('pageTitle', 'somiti', 'days', 'fieldOfficers'));
    }

    public function update(Request $request, MfSomiti $somiti)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_somiti_edit'), 403, 'Unauthorized');
        abort_if($somiti->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        if ($somiti->members()->count() > 0) {
            return redirect()->route('microfinance.somiti.index')->with('error', 'Update denied: Members already exist in this somiti.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'somiti_code' => 'required|string|unique:mf_somitis,somiti_code,' . $somiti->id,
            'somiti_type' => 'required|integer|in:1,2,3',
            'max_member_limit' => 'required|integer|min:1',
            'field_officer_id' => 'required|exists:employees,id',
            'meeting_day' => 'required|string',
            'opening_date' => 'required|string',
            'collection_start_date' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            // Format Dates
            if ($request->opening_date) {
                $data['opening_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $request->opening_date)->format('Y-m-d');
            }
            if ($request->collection_start_date) {
                $data['collection_start_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $request->collection_start_date)->format('Y-m-d');
            }

            $oldFieldOfficerId = $somiti->field_officer_id;
            $somiti->update($data);

            // If the officer changed, close the old active assignment and create a new one
            if ($oldFieldOfficerId != $somiti->field_officer_id) {
                $today = date('Y-m-d');
                
                // Close active assignment
                \App\Models\Microfinance\MfFieldOfficerAssignment::where('somiti_id', $somiti->id)
                    ->where('status', 1)
                    ->update([
                        'to_date' => $today,
                        'status' => 0,
                        'change_reason' => 'Changed via Somiti edit form',
                    ]);

                // Create new active assignment
                \App\Models\Microfinance\MfFieldOfficerAssignment::create([
                    'sister_concern_id' => $somiti->sister_concern_id,
                    'somiti_id' => $somiti->id,
                    'field_officer_id' => $somiti->field_officer_id,
                    'assigned_by' => auth()->id(),
                    'from_date' => $today,
                    'status' => 1,
                    'change_reason' => 'Changed via Somiti edit form',
                ]);
            }

            DB::commit();

            return redirect()->route('microfinance.somiti.index')->with('success', 'Somiti updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update Somiti: ' . $e->getMessage());
        }
    }

    public function destroy(MfSomiti $somiti)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_somiti_delete'), 403, 'Unauthorized');
        abort_if($somiti->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        if ($somiti->members()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete: Members already exist in this somiti.']);
        }
        try {
            $somiti->delete();
            return response()->json(['success' => true, 'message' => 'Somiti deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
