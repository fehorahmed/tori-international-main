<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;

use App\Models\Hrm\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ShiftController extends Controller
{
    public function index()
    {
        try {
            return view('hrm.shifts.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function dataTable()
    {
        $query = Shift::query();
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Shift $shift) {

                $btn = '';
                if (auth()->user()->hasPermissionTo('shift_edit')) {
                    $btn .= '<a href="' . route('shifts.edit', ['shift' => $shift->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('shift_delete')) {
                    $btn .= ' <a role="button" data-id="' . $shift->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })
            ->editColumn('start_time', function(Shift $shift) {
                return Carbon::parse($shift->start_time)->format('h:i A');
            })
            ->editColumn('end_time', function(Shift $shift) {
                return Carbon::parse($shift->end_time)->format('h:i A');
            })
            ->rawColumns(['action'])
            ->toJson();
    }
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('shift_create')) {
            abort(403, 'Unauthorized');
        }
        return view('hrm.shifts.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('shift_create')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('shifts')
                    ->where('start_time',Carbon::parse($request->start_time)->format('H:i'))
                    ->where('end_time',Carbon::parse($request->end_time)->format('H:i'))
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);
        try {
            $validatedData['create_user_id'] = auth()->id();
            $validatedData['start_time'] = Carbon::parse($request->start_time)->format('H:i');
            $validatedData['end_time'] = Carbon::parse($request->end_time)->format('H:i');
            Shift::create($validatedData);
            return redirect()->route('shifts.index')->with('success', 'Shift created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create shift: ' . $e->getMessage()]);
        }
    }

    public function edit(Shift $shift)
    {
        if (!auth()->user()->hasPermissionTo('shift_edit')) {
            abort(403, 'Unauthorized');
        }
        return view('hrm.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        if (!auth()->user()->hasPermissionTo('shift_edit')) {
            abort(403, 'Unauthorized');
        }
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('shifts')
                    ->where('start_time',Carbon::parse($request->start_time)->format('H:i'))
                    ->where('end_time',Carbon::parse($request->end_time)->format('H:i'))
                ->ignore($shift)
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);
        try {
            $validatedData['alter_user_id'] = auth()->id();
            $validatedData['start_time'] = Carbon::parse($request->start_time)->format('H:i');
            $validatedData['end_time'] = Carbon::parse($request->end_time)->format('H:i');
            $shift->update($validatedData);
            return redirect()->route('shifts.index')->with('success', 'Shift updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update shift: ' . $e->getMessage()]);
        }
    }

    public function destroy(Shift $shift)
    {
        try {
            if (!auth()->user()->hasPermissionTo('shift_delete')) {
                abort(403, 'Unauthorized');
            }
            $shift->delete();
            return response()->json(['success'=>true,'message'=>'Shift deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message'=>'Failed to delete shift: ' . $e->getMessage()]);
        }
    }
}
