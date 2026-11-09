<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\ShomriddhiAdvance\ShomriddhiFieldOfficerAssignment;
use App\Models\ShomriddhiAdvance\ShomriddhiGroup;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FieldOfficerAssignmentController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->can('shomriddhi_advance'), 403, 'Unauthorized');
        $pageTitle = 'Field Officer Bulk Transfer';
        $sisterConcernId = auth()->user()->activeConcernId();

        // Fetch all active field officers
        $fieldOfficers = Employee::where('sister_concern_id', $sisterConcernId)
            ->fieldOfficers()
            ->orderBy('name')
            ->get();

        return view('shomriddhi_advance.field_officer_assignment.index', compact('pageTitle', 'fieldOfficers'));
    }

    public function getGroups(Request $request)
    {
        abort_if(!auth()->user()->can('shomriddhi_advance'), 403, 'Unauthorized');
        $officerId = $request->get('field_officer_id');

        if (!$officerId) {
            return response()->json([]);
        }

        $groups = ShomriddhiGroup::withCount('members')
            ->where('field_officer_id', $officerId)
            ->get(['id', 'name_en', 'code']);

        return response()->json($groups);
    }

    public function transfer(Request $request)
    {
        abort_if(!auth()->user()->can('shomriddhi_advance'), 403, 'Unauthorized');
        $request->validate([
            'transfer_from_id' => 'required|exists:employees,id',
            'transfer_to_id' => 'required|exists:employees,id|different:transfer_from_id',
            'group_ids' => 'required|array|min:1',
            'group_ids.*' => 'exists:shomriddhi_groups,id',
            'effective_date' => 'required|string',
            'change_reason' => 'nullable|string|max:500',
        ]);

        $sisterConcernId = auth()->user()->activeConcernId();

        // Verify both field officers belong to the active sister concern
        $fromOfficer = Employee::where('id', $request->transfer_from_id)
            ->where('sister_concern_id', $sisterConcernId)
            ->fieldOfficers()
            ->first();

        $toOfficer = Employee::where('id', $request->transfer_to_id)
            ->where('sister_concern_id', $sisterConcernId)
            ->fieldOfficers()
            ->first();

        if (!$fromOfficer || !$toOfficer) {
            abort(403);
        }

        // Verify that all group_ids are currently assigned to the source officer
        $groupCount = ShomriddhiGroup::whereIn('id', $request->group_ids)
            ->where('field_officer_id', $request->transfer_from_id)
            ->count();

        if ($groupCount !== count(array_unique($request->group_ids))) {
            abort(403);
        }

        $effectiveDate = Carbon::createFromFormat('d-m-Y', $request->effective_date)->format('Y-m-d');

        try {
            DB::beginTransaction();

            foreach ($request->group_ids as $groupId) {
                // 1. Close current active assignment for this Group
                ShomriddhiFieldOfficerAssignment::where('shomriddhi_group_id', $groupId)
                    ->where('status', 1)
                    ->update([
                        'to_date' => Carbon::parse($effectiveDate)->subDay()->format('Y-m-d'),
                        'status' => 0,
                    ]);

                // 2. Open new active assignment for the target Field Officer
                ShomriddhiFieldOfficerAssignment::create([
                    'sister_concern_id' => $sisterConcernId,
                    'shomriddhi_group_id' => $groupId,
                    'field_officer_id' => $request->transfer_to_id,
                    'assigned_by' => auth()->id(),
                    'from_date' => $effectiveDate,
                    'status' => 1,
                ]);

                // 3. Update the main group field_officer_id column
                ShomriddhiGroup::where('id', $groupId)->update([
                    'field_officer_id' => $request->transfer_to_id,
                ]);
            }

            DB::commit();

            return redirect()->route('shomriddhi-advance.field-officer-assignment.index')
                ->with('success', count($request->group_ids) . ' Group(s) transferred successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
}
