<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;
use App\Enumeration\SupplierType;

class BranchSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate([
            'sister_concern_id' => 'required|exists:sister_concerns,id'
        ]);

        $user = auth()->user();
        $targetId = $request->sister_concern_id;

        if ($user->role == 'Admin') {
            // Admin can switch to any branch
            session(['active_sister_concern_id' => $targetId]);
        } elseif ($user->role == 'Area_Manager') {
            // Area Manager can only switch to branches assigned to them
            $isAssigned = SisterConcern::where('id', $targetId)
                ->where('area_manager_id', $user->employee_id)
                ->exists();

            if ($isAssigned) {
                session(['active_sister_concern_id' => $targetId]);
            } else {
                return back()->with('error', 'You do not have access to this branch.');
            }
        } else {
            return back()->with('error', 'Unauthorized action.');
        }

        return back()->with('success', 'Branch switched successfully.');
    }
}
