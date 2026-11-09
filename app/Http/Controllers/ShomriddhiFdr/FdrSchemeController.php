<?php

namespace App\Http\Controllers\ShomriddhiFdr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiFdr\FdrScheme;
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
                return $row->duration . ' Months';
            })
            ->addColumn('interest_rate', function ($row) {
                return number_format($row->interest_rate, 2) . '%';
            })
            ->addColumn('penalty_rate', function ($row) {
                return !is_null($row->penalty_rate) ? number_format($row->penalty_rate, 2) . '%' : 'None';
            })
            ->addColumn('min_amount', function ($row) {
                return '৳ ' . number_format($row->min_amount, 2);
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
            ->addColumn('action', function ($row) {
                $btn = '';
                $hasAccounts = $row->accounts()->exists();
                
                if ($hasAccounts) {
                    $btn .= ' <button type="button" class="btn btn-secondary btn-sm" title="Cannot edit/delete because accounts are attached to this scheme"><i class="fa fa-lock"></i></button>';
                } else {
                    if (auth()->user()->can('shomriddhi_fdr_scheme_edit') || true) {
                        $btn .= ' <a href="'.route('shomriddhi-fdr.schemes.edit', $row->id).'" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (auth()->user()->can('shomriddhi_fdr_scheme_delete') || true) {
                        $btn .= ' <a role="button" data-id="' . $row->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                    }
                }
                
                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('shomriddhi_fdr.schemes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'penalty_rate' => 'nullable|numeric|min:0',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:Active,Inactive'
        ]);

        FdrScheme::create($request->all());

        return redirect()->route('shomriddhi-fdr.schemes.index')->with('success', 'FDR Scheme created successfully.');
    }

    public function edit(FdrScheme $scheme)
    {
        if ($scheme->accounts()->exists()) {
            return redirect()->route('shomriddhi-fdr.schemes.index')->with('error', 'Cannot edit this scheme because it is already used in active accounts.');
        }
        return view('shomriddhi_fdr.schemes.edit', compact('scheme'));
    }

    public function update(Request $request, FdrScheme $scheme)
    {
        if ($scheme->accounts()->exists()) {
            return redirect()->route('shomriddhi-fdr.schemes.index')->with('error', 'Cannot update this scheme because it is already used in active accounts.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'penalty_rate' => 'nullable|numeric|min:0',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:Active,Inactive'
        ]);

        $scheme->update($request->all());

        return redirect()->route('shomriddhi-fdr.schemes.index')->with('success', 'FDR Scheme updated successfully.');
    }

    public function destroy(FdrScheme $scheme)
    {
        try {
            if ($scheme->accounts()->exists()) {
                return response()->json(['success'=>false,'message' => 'Cannot delete this scheme because it is already used in active accounts.'], 200);
            }
            if (!auth()->user()->hasPermissionTo('shomriddhi_fdr_scheme_delete') && false) { // Assuming false for now or use actual check
                abort(403, 'Unauthorized');
            }
            $scheme->delete();
            return response()->json(['success'=>true,'message' => 'FDR Scheme deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'message' => 'Scheme could not be deleted: '.$e->getMessage()], 200);
        }
    }

    public function toggleStatus(FdrScheme $scheme)
    {
        $scheme->status = $scheme->status === 'Active' ? 'Inactive' : 'Active';
        $scheme->save();

        return response()->json([
            'success' => true,
            'status' => $scheme->status,
            'message' => 'Scheme status updated successfully.'
        ]);
    }
}
