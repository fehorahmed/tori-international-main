<?php

namespace App\Http\Controllers\Microfinance;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Microfinance\MfFieldOfficerAssignment;
use App\Models\Microfinance\MfSomiti;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FieldOfficerAssignmentController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_field_officer_assignment'), 403, 'Unauthorized');
        $pageTitle = 'Field Officer Bulk Transfer';
        $sisterConcernId = auth()->user()->activeConcernId();

        // Fetch all active field officers
        $fieldOfficers = Employee::where('sister_concern_id', $sisterConcernId)
            ->fieldOfficers()
            ->orderBy('name')
            ->get();

        return view('microfinance.field_officer_assignment.index', compact('pageTitle', 'fieldOfficers'));
    }

    public function getSomitis(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_field_officer_assignment'), 403, 'Unauthorized');
        $officerId = $request->get('field_officer_id');
        $sisterConcernId = auth()->user()->activeConcernId();

        if (!$officerId) {
            return response()->json([]);
        }

        $somitis = MfSomiti::withCount('members')
            ->where('sister_concern_id', $sisterConcernId)
            ->where('field_officer_id', $officerId)
            ->get(['id', 'name', 'somiti_code', 'meeting_day']);

        return response()->json($somitis);
    }

    public function transfer(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_field_officer_assignment'), 403, 'Unauthorized');
        $request->validate([
            'transfer_from_id' => 'required|exists:employees,id',
            'transfer_to_id' => 'required|exists:employees,id|different:transfer_from_id',
            'somiti_ids' => 'required|array|min:1',
            'somiti_ids.*' => 'exists:mf_somitis,id',
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

        // Verify that all somiti_ids belong to the active sister concern and are currently assigned to the source officer
        $somitiCount = MfSomiti::whereIn('id', $request->somiti_ids)
            ->where('sister_concern_id', $sisterConcernId)
            ->where('field_officer_id', $request->transfer_from_id)
            ->count();

        if ($somitiCount !== count(array_unique($request->somiti_ids))) {
            abort(403);
        }

        $effectiveDate = Carbon::createFromFormat('d-m-Y', $request->effective_date)->format('Y-m-d');
        $changeReason = $request->change_reason ?? 'Bulk reassignment';

        try {
            DB::beginTransaction();

            foreach ($request->somiti_ids as $somitiId) {
                // 1. Close current active assignment for this Somiti
                MfFieldOfficerAssignment::where('somiti_id', $somitiId)
                    ->where('status', 1)
                    ->update([
                        'to_date' => Carbon::parse($effectiveDate)->subDay()->format('Y-m-d'),
                        'status' => 0,
                        'change_reason' => $changeReason . ' (Transferred out)',
                    ]);

                // 2. Open new active assignment for the target Field Officer
                MfFieldOfficerAssignment::create([
                    'sister_concern_id' => $sisterConcernId,
                    'somiti_id' => $somitiId,
                    'field_officer_id' => $request->transfer_to_id,
                    'assigned_by' => auth()->id(),
                    'from_date' => $effectiveDate,
                    'status' => 1,
                    'change_reason' => $changeReason,
                ]);

                // 3. Update the main somiti field_officer_id column
                MfSomiti::where('id', $somitiId)->update([
                    'field_officer_id' => $request->transfer_to_id,
                ]);
            }

            DB::commit();

            return redirect()->route('microfinance.field-officer-assignment.index')
                ->with('success', count($request->somiti_ids) . ' Somiti(s) transferred successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
}
