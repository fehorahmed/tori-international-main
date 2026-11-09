<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;

use App\Models\Hrm\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class HolidayController extends Controller
{
    public function index()
    {
        try {
            return view('hrm.holidays.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function dataTable()
    {
        $query = Holiday::query();
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Holiday $holiday) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('holiday_edit')) {
                    $btn .= '<a href="' . route('holidays.edit', ['holiday' => $holiday->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('holiday_delete')) {
                    $btn .= ' <a role="button" data-id="' . $holiday->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })
            ->editColumn('start_date', function(Holiday $holiday) {
                return Carbon::parse($holiday->start_date)->format('d-m-Y');
            })
            ->editColumn('end_date', function(Holiday $holiday) {
                return Carbon::parse($holiday->end_date)->format('d-m-Y');
            })
            ->rawColumns(['action'])
            ->toJson();
    }
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('holiday_create')) {
            abort(403, 'Unauthorized');
        }
        return view('hrm.holidays.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('holiday_create')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'title' =>[
                'required','max:255'
            ],
            'start_date'=>'required|date',
            'end_date'=>'required|date',
            'description'=>'nullable|max:255',
        ]);
        try {
            $validatedData['create_user_id'] = auth()->id();
            $validatedData['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $validatedData['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            Holiday::create($validatedData);
            return redirect()->route('holidays.index')->with('success', 'Holiday created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create holiday: ' . $e->getMessage()]);
        }
    }

    public function edit(Holiday $holiday)
    {
        if (!auth()->user()->hasPermissionTo('holiday_edit')) {
            abort(403, 'Unauthorized');
        }
        return view('hrm.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        if (!auth()->user()->hasPermissionTo('holiday_edit')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'title' =>[
                'required','max:255',
                Rule::unique('holidays')
            ],
            'start_date'=>'required|date',
            'end_date'=>'required|date',
            'description'=>'nullable|max:255',
        ]);
        try {
            $validatedData['alter_user_id'] = auth()->id();
            $validatedData['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
            $validatedData['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
            $holiday->update($validatedData);
            return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update holiday: ' . $e->getMessage()]);
        }
    }

    public function destroy(Holiday $holiday)
    {
        try {
            if (!auth()->user()->hasPermissionTo('holiday_delete')) {
                abort(403, 'Unauthorized');
            }

            $holiday->delete();
            return response()->json(['success'=>true,'message'=>'Holiday deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'Failed to delete holiday: ' . $e->getMessage()]);
        }
    }
}
