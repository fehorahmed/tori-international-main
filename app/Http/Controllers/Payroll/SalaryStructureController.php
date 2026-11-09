<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\SalaryStructure;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SalaryStructureController extends Controller
{
    public function index()
    {
        return view('payroll.salary_structure.index');
    }

    public function dataTable()
    {
        $query = SalaryStructure::orderBy('id', 'desc');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('salary-structures.edit', $row->id);
                $statusIcon = $row->status == 1 ? 'fa-lock-open' : 'fa-lock';
                $statusClass = $row->status == 1 ? 'btn-success' : 'btn-danger';
                $statusUrl = route('salary-structures.status', $row->id);

                $btn = '';
                $btn .= ' <a href="' . $editUrl . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                $btn .= ' <button type="button" class="btn btn-sm ' . $statusClass . ' btn-change-status" data-url="' . $statusUrl . '" title="Toggle Status"><i class="fa ' . $statusIcon . '"></i></button>';

                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create()
    {
        return view('payroll.salary_structure.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'basic_percent' => 'required|numeric|min:0|max:100',
            'house_rent_percent' => 'required|numeric|min:0|max:100',
            'medical_percent' => 'required|numeric|min:0|max:100',
            'ta_da_fixed' => 'nullable|numeric|min:0',
            'pf_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->user()->id;

        SalaryStructure::create($data);

        return redirect()->route('salary-structures.index')->with('success', 'Salary Structure created successfully!');
    }

    public function edit(SalaryStructure $salaryStructure)
    {
        return view('payroll.salary_structure.edit', compact('salaryStructure'));
    }

    public function update(Request $request, SalaryStructure $salaryStructure)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'basic_percent' => 'required|numeric|min:0|max:100',
            'house_rent_percent' => 'required|numeric|min:0|max:100',
            'medical_percent' => 'required|numeric|min:0|max:100',
            'ta_da_fixed' => 'nullable|numeric|min:0',
            'pf_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $salaryStructure->update($request->all());

        return redirect()->route('salary-structures.index')->with('success', 'Salary Structure updated successfully!');
    }

    public function status(SalaryStructure $salary_structure)
    {
        $salary_structure->status = $salary_structure->status == 1 ? 0 : 1;
        $salary_structure->save();

        return response()->json(['success' => true, 'message' => 'Status changed successfully']);
    }
}
