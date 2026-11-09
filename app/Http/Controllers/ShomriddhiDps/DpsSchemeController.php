<?php

namespace App\Http\Controllers\ShomriddhiDps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiDps\DpsScheme;
use Yajra\DataTables\Facades\DataTables;

class DpsSchemeController extends Controller
{
    public function index()
    {
        return view('shomriddhi_dps.schemes.index');
    }

    public function dataTable()
    {
        $schemes = DpsScheme::withCount('accounts')->orderBy('id', 'desc');

        return DataTables::of($schemes)
            ->addIndexColumn()
            ->addColumn('duration_months', fn($row) => $row->duration_months . ' Months')
            ->addColumn('installment_amount', fn($row) => '৳ ' . number_format($row->installment_amount, 2) . ' (' . $row->installment_type . ')')
            ->addColumn('interest_rate', fn($row) => number_format($row->interest_rate, 2) . '% p.a.')
            ->addColumn('penalty', function($row) {
                return $row->penalty_type === 'Percentage' ? number_format($row->penalty_amount, 2) . '%' : '৳' . number_format($row->penalty_amount, 2);
            })
            ->addColumn('premature_penalty', function($row) {
                return $row->premature_penalty_type === 'Percentage' ? number_format($row->premature_penalty_amount, 2) . '%' : '৳' . number_format($row->premature_penalty_amount, 2);
            })
            ->addColumn('status', function ($row) {
                $checked = $row->status === 'Active' ? 'checked' : '';
                return '<div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input status-toggle"
                           id="status_' . $row->id . '" data-id="' . $row->id . '" ' . $checked . '>
                    <label class="custom-control-label" for="status_' . $row->id . '"></label>
                </div>';
            })
            ->addColumn('action', function ($row) {
                if ($row->accounts_count > 0) {
                    return '<i class="fa fa-lock text-muted" title="Locked - Accounts linked" style="font-size: 1.2rem;"></i>';
                }
                $btn = '';
                if (auth()->user()->can('shomriddhi_dps_scheme_edit')) {
                    $btn .= '<a href="' . route('shomriddhi-dps.schemes.edit', $row->id) . '" class="btn btn-info btn-sm"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->can('shomriddhi_dps_scheme_delete')) {
                    $btn .= ' <a role="button" data-id="' . $row->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('shomriddhi_dps.schemes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                      => 'required|string|max:255|unique:dps_schemes,name',
            'duration_months'           => 'required|integer|min:1',
            'installment_type'          => 'required|in:Daily,Weekly,Monthly',
            'installment_amount'        => 'required|numeric|min:1',
            'interest_rate'             => 'required|numeric|min:0|max:100',
            'penalty_type'              => 'required|in:Fixed,Percentage',
            'penalty_amount'            => 'nullable|numeric|min:0',
            'premature_penalty_type'    => 'required|in:Fixed,Percentage',
            'premature_penalty_amount'  => 'nullable|numeric|min:0',
            'status'                    => 'required|in:Active,Inactive',
        ]);

        DpsScheme::create([
            'name'                      => $request->name,
            'duration_months'           => $request->duration_months,
            'installment_type'          => $request->installment_type,
            'installment_amount'        => $request->installment_amount,
            'interest_rate'             => $request->interest_rate,
            'penalty_type'              => $request->penalty_type,
            'penalty_amount'            => $request->penalty_amount ?? 0,
            'premature_penalty_type'    => $request->premature_penalty_type,
            'premature_penalty_amount'  => $request->premature_penalty_amount ?? 0,
            'status'                    => $request->status,
            'created_by'                => auth()->id(),
        ]);

        return redirect()->route('shomriddhi-dps.schemes.index')->with('success', 'DPS Scheme created successfully.');
    }

    public function edit(DpsScheme $scheme)
    {
        if ($scheme->accounts()->exists()) {
            return redirect()->route('shomriddhi-dps.schemes.index')->with('error', 'Cannot edit this scheme because it has linked accounts.');
        }
        return view('shomriddhi_dps.schemes.edit', compact('scheme'));
    }

    public function update(Request $request, DpsScheme $scheme)
    {
        if ($scheme->accounts()->exists()) {
            return redirect()->route('shomriddhi-dps.schemes.index')->with('error', 'Cannot update this scheme because it has linked accounts.');
        }

        $request->validate([
            'name'                      => 'required|string|max:255|unique:dps_schemes,name,' . $scheme->id,
            'duration_months'           => 'required|integer|min:1',
            'installment_type'          => 'required|in:Daily,Weekly,Monthly',
            'installment_amount'        => 'required|numeric|min:1',
            'interest_rate'             => 'required|numeric|min:0|max:100',
            'penalty_type'              => 'required|in:Fixed,Percentage',
            'penalty_amount'            => 'nullable|numeric|min:0',
            'premature_penalty_type'    => 'required|in:Fixed,Percentage',
            'premature_penalty_amount'  => 'nullable|numeric|min:0',
            'status'                    => 'required|in:Active,Inactive',
        ]);

        $scheme->update([
            'name'                      => $request->name,
            'duration_months'           => $request->duration_months,
            'installment_type'          => $request->installment_type,
            'installment_amount'        => $request->installment_amount,
            'interest_rate'             => $request->interest_rate,
            'penalty_type'              => $request->penalty_type,
            'penalty_amount'            => $request->penalty_amount ?? 0,
            'premature_penalty_type'    => $request->premature_penalty_type,
            'premature_penalty_amount'  => $request->premature_penalty_amount ?? 0,
            'status'                    => $request->status,
        ]);

        return redirect()->route('shomriddhi-dps.schemes.index')->with('success', 'DPS Scheme updated successfully.');
    }

    public function destroy(DpsScheme $scheme)
    {
        try {
            if ($scheme->accounts()->exists()) {
                return response()->json(['success' => false, 'message' => 'Scheme has linked accounts. Cannot delete.'], 200);
            }
            $scheme->delete();
            return response()->json(['success' => true, 'message' => 'DPS Scheme deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 200);
        }
    }

    public function toggleStatus(DpsScheme $scheme)
    {
        $scheme->status = $scheme->status === 'Active' ? 'Inactive' : 'Active';
        $scheme->save();
        return response()->json(['success' => true, 'status' => $scheme->status, 'message' => 'Status updated.']);
    }
}
