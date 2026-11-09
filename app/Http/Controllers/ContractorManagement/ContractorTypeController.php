<?php

namespace App\Http\Controllers\ContractorManagement;

use App\Http\Controllers\Controller;
use App\Models\ContractorManagement\ContractorType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContractorTypeController extends Controller
{
    public function index()
    {
        return view('contractor_management.types.index');
    }

    public function datatable()
    {
        $types = ContractorType::withCount('contractors')->orderBy('id', 'asc');

        return DataTables::eloquent($types)
            ->addIndexColumn()
            ->addColumn('status', function ($type) {
                if ($type->status == 1) {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function ($type) {
                $btn = '<a href="' . route('contractor_management.types.edit', $type->id) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                
                if ($type->contractors_count > 0) {
                    $btn .= ' <button type="button" class="btn btn-warning btn-sm" title="Cannot delete, contractors exist"><i class="fa fa-lock"></i></button>';
                } else {
                    $btn .= ' <a role="button" data-id="' . $type->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                
                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('contractor_management.types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:contractor_types,name',
            'status' => 'required|in:1,0',
        ]);

        ContractorType::create($request->all());

        return redirect()->route('contractor_management.types.index')->with('success', 'Contractor Type created successfully.');
    }

    public function edit(ContractorType $type)
    {
        return view('contractor_management.types.edit', compact('type'));
    }

    public function update(Request $request, ContractorType $type)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:contractor_types,name,' . $type->id,
            'status' => 'required|in:1,0',
        ]);

        $type->update($request->all());

        return redirect()->route('contractor_management.types.index')->with('success', 'Contractor Type updated successfully.');
    }

    public function destroy(ContractorType $type)
    {
        try {
            // Add constraint checking if contractors exist under this type before deleting
            if ($type->contractors()->count() > 0) {
                 return response()->json(['success' => false, 'message' => 'Cannot delete! Contractors exist under this type.']);
            }

            $type->delete();

            return response()->json(['success' => true, 'message' => 'Contractor Type deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting contractor type: ' . $e->getMessage()]);
        }
    }
}
