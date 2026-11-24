<?php

namespace App\Http\Controllers\ShomriddhiFdr;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiFdr\FdrScheme;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FdrSchemeController extends Controller
{
    public function index()
    {
        return view('shomriddhi_fdr.schemes.index');
    }

    public function dataTable()
    {
        $schemes = FdrScheme::orderBy('id', 'desc');

        return DataTables::of($schemes)
            ->addIndexColumn()
            ->addColumn('duration', function ($row) {
                return $row->duration.' Months';
            })
            ->addColumn('interest_rate', function ($row) {
                return number_format($row->interest_rate, 2).'%';
            })
            ->addColumn('penalty_rate', function ($row) {
                return ! is_null($row->penalty_rate) ? number_format($row->penalty_rate, 2).'%' : 'None';
            })
            ->addColumn('min_amount', function ($row) {
                return '৳ '.number_format($row->min_amount, 2);
            })
            ->addColumn('status', function ($row) {
                $checked = $row->status == 'Active' ? 'checked' : '';

                return '
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input status-toggle" 
                           id="status_'.$row->id.'" data-id="'.$row->id.'" '.$checked.'>
                    <label class="custom-control-label" for="status_'.$row->id.'"></label>
                </div>';
            })
            ->addColumn('visible_on_app', fn ($row) => $row->visible_on_app ? 'Visible' : 'Hidden')
            ->addColumn('action', function ($row) {
                $btn = '';
                $hasAccounts = $row->accounts()->exists();

                if ($hasAccounts) {
                    $btn .= ' <button type="button" class="btn btn-secondary btn-sm" title="Cannot edit/delete because accounts are attached to this scheme"><i class="fa fa-lock"></i></button>';
                } else {
                    if (auth()->user()->can('shomriddhi_fdr_scheme_edit')) {
                        $btn .= ' <a href="'.route('shomriddhi-fdr.schemes.edit', $row->id).'" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (auth()->user()->can('shomriddhi_fdr_scheme_delete')) {
                        $btn .= ' <a role="button" data-id="'.$row->id.'" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                    }
                }

                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_scheme_create'), 403);

        return view('shomriddhi_fdr.schemes.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_scheme_create'), 403);
        $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1|max:600',
            'interest_rate' => 'required|numeric|decimal:0,2|min:0|max:100',
            'penalty_rate' => 'nullable|numeric|decimal:0,2|min:0|max:100',
            'min_amount' => 'required|numeric|decimal:0,2|min:0|max:9999999999.99',
            'max_amount' => 'nullable|numeric|decimal:0,2|gte:min_amount|max:9999999999.99',
            'visible_on_app' => 'sometimes|boolean',
            'status' => 'required|in:Active,Inactive',
        ]);

        FdrScheme::create($request->only(['name', 'duration', 'interest_rate', 'penalty_rate', 'min_amount', 'max_amount', 'status']) + ['visible_on_app' => $request->boolean('visible_on_app')]);

        return redirect()->route('shomriddhi-fdr.schemes.index')->with('success', 'FDR Scheme created successfully.');
    }

    public function edit(FdrScheme $scheme)
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_scheme_edit'), 403);
        if ($scheme->accounts()->exists()) {
            return redirect()->route('shomriddhi-fdr.schemes.index')->with('error', 'Cannot edit this scheme because it is already used in active accounts.');
        }

        return view('shomriddhi_fdr.schemes.edit', compact('scheme'));
    }

    public function update(Request $request, FdrScheme $scheme)
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_scheme_edit'), 403);
        if ($scheme->accounts()->exists()) {
            return redirect()->route('shomriddhi-fdr.schemes.index')->with('error', 'Cannot update this scheme because it is already used in active accounts.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1|max:600',
            'interest_rate' => 'required|numeric|decimal:0,2|min:0|max:100',
            'penalty_rate' => 'nullable|numeric|decimal:0,2|min:0|max:100',
            'min_amount' => 'required|numeric|decimal:0,2|min:0|max:9999999999.99',
            'max_amount' => 'nullable|numeric|decimal:0,2|gte:min_amount|max:9999999999.99',
            'visible_on_app' => 'sometimes|boolean',
            'status' => 'required|in:Active,Inactive',
        ]);

        $scheme->update($request->only(['name', 'duration', 'interest_rate', 'penalty_rate', 'min_amount', 'max_amount', 'status']) + ['visible_on_app' => $request->boolean('visible_on_app')]);

        return redirect()->route('shomriddhi-fdr.schemes.index')->with('success', 'FDR Scheme updated successfully.');
    }

    public function destroy(FdrScheme $scheme)
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_scheme_delete'), 403);
        try {
            if ($scheme->accounts()->exists()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete this scheme because it is already used in active accounts.'], 200);
            }
            if (! auth()->user()->hasPermissionTo('shomriddhi_fdr_scheme_delete')) { // Assuming false for now or use actual check
                abort(403, 'Unauthorized');
            }
            $scheme->delete();

            return response()->json(['success' => true, 'message' => 'FDR Scheme deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Scheme could not be deleted: '.$e->getMessage()], 200);
        }
    }

    public function toggleVisibility(FdrScheme $scheme)
    {
        $scheme->update(['visible_on_app' => ! $scheme->visible_on_app]);

        return response()->json(['success' => true, 'visible_on_app' => $scheme->visible_on_app]);
    }

    public function toggleStatus(FdrScheme $scheme)
    {
        $scheme->status = $scheme->status === 'Active' ? 'Inactive' : 'Active';
        $scheme->save();

        return response()->json([
            'success' => true,
            'status' => $scheme->status,
            'message' => 'Scheme status updated successfully.',
        ]);
    }
}
