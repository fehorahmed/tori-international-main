<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getDesignation(Request $request)
    {
        $designations = Designation::where('department_id',$request->id)->get();
        return response()->json($designations);

    }
}
