<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FieldOfficerController extends Controller
{
    public function index()
    {
        // Maybe using microfinance permission or shomriddhi_advance_settings
        // Assuming if they can access shomriddhi advance they can see this list
        abort_if(!auth()->user()->can('shomriddhi_advance'), 403, 'Unauthorized');
        return view('shomriddhi_advance.field_officer.index');
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->can('shomriddhi_advance'), 403, 'Unauthorized');
        $query = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->fieldOfficers()
            ->with('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion');
        
        if (auth()->user()->role == RoleType::$FIELD_OFFICER) {
            $query->where('id', auth()->user()->employee_id);
        }    

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Employee $fo) {
                // If they need to view/edit, they can be redirected to employees edit
                $btn = '';
                if (auth()->user()->hasPermissionTo('mf_field_officer_edit') || auth()->user()->can('employee_edit')) {
                    $btn .= '<a href="' . route('employees.edit', ['employee' => $fo->id]) . '" class="btn btn-info btn-sm mb-1" title="Edit Employee"><i class="fa fa-edit"></i></a> ';
                }
                return $btn;
            })
            ->addColumn('image', function (Employee $fo) {
                $url = asset('img/avatar.png');
                if ($fo->photo) {
                    $url = asset($fo->photo);
                }
                return '<img src="' . $url . '" width="50px" height="50px" class="img-circle" style="border: 1px solid #ddd;">';
            })
            ->addColumn('address', function (Employee $fo) {
                return '<button type="button" class="btn btn-info btn-xs btn-view-address" data-id="' . $fo->id . '"><i class="fa fa-map-marker-alt"></i> View Address</button>';
            })
            ->rawColumns(['image', 'action', 'address'])
            ->toJson();
    }
}
