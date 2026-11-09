<?php

namespace App\Http\Controllers\Microfinance;

use App\Http\Controllers\Controller;
use App\Models\Microfinance\MfSavingsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SavingsProductController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_product'), 403, 'Unauthorized');
        $pageTitle = 'Savings & Investment Products';
        return view('microfinance.savings_product.index', compact('pageTitle'));
    }

    public function dataTable(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_product'), 403, 'Unauthorized');
        $query = MfSavingsProduct::withCount(['accounts' => function ($q) {
            $q->where('sister_concern_id', auth()->user()->activeConcernId());
        }]);

        if ($request->product_type) {
            $query->where('product_type', $request->product_type);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (MfSavingsProduct $product) {
                $btn = '<a href="' . route('microfinance.savings-product.show', $product->id) . '" class="btn btn-info btn-sm" title="View Details"><i class="fa fa-eye"></i></a>';
                
                if ($product->product_type == 1) {
                    $btn .= ' <button class="btn btn-secondary btn-sm" disabled title="Cannot edit General Savings scheme"><i class="fa fa-lock"></i></button>';
                } elseif ($product->accounts_count > 0) {
                    $btn .= ' <button class="btn btn-secondary btn-sm" disabled title="Cannot edit: Accounts exist"><i class="fa fa-lock"></i></button>';
                } else {
                    $btn .= ' <a href="' . route('microfinance.savings-product.edit', $product->id) . '" class="btn btn-primary btn-sm" title="Edit"><i class="fa fa-edit"></i></a>';
                }
                
                return $btn;
            })
            ->editColumn('product_type', function (MfSavingsProduct $product) {
                switch($product->product_type) {
                    case 1: return '<span class="badge badge-info">General Savings (GS)</span>';
                    case 2: return '<span class="badge badge-primary">DPS</span>';
                    case 3: return '<span class="badge badge-warning">FDR</span>';
                    default: return 'Unknown';
                }
            })
            ->editColumn('status', function (MfSavingsProduct $product) {
                return $product->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->rawColumns(['action', 'status', 'product_type'])
            ->toJson();
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_product_create'), 403, 'Unauthorized');
        $pageTitle = 'Add New Product';
        return view('microfinance.savings_product.create', compact('pageTitle'));
    }

    public function getNextProductCode(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_product'), 403, 'Unauthorized');
        $nextCode = $this->generateProductCode($request->type);
        return response()->json(['code' => $nextCode]);
    }

    private function generateProductCode($type)
    {
        $prefix = '';
        if ($type == 1) $prefix = 'GS';
        elseif ($type == 2) $prefix = 'DPS';
        elseif ($type == 3) $prefix = 'FDR';

        $lastProduct = MfSavingsProduct::where('product_type', $type)
            ->where('code', 'LIKE', $prefix . '-%')
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastProduct) {
            $lastCode = $lastProduct->code;
            $lastNumber = (int) str_replace($prefix . '-', '', $lastCode);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_product_create'), 403, 'Unauthorized');
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:mf_savings_products,code',
            'product_type' => 'required|integer|in:1,2,3',
            'installment_frequency' => 'nullable|required_if:product_type,2|integer|in:1,2,3',
            'interest_rate' => 'required|numeric|min:0',
            'duration_months' => 'nullable|required_if:product_type,2,3|integer|min:1',
            'installment_amount' => 'nullable|required_if:product_type,2|numeric|min:0',
            'opening_fee' => 'required|numeric|min:0',
            'closing_fee' => 'required|numeric|min:0',
            'installment_fine_type' => 'required|in:flat,percentage',
            'installment_fine_amount' => 'required|numeric|min:0',
            'early_closing_fine_type' => 'required|in:flat,percentage',
            'early_closing_fine_amount' => 'required|numeric|min:0',
            'interest_posting_type' => 'nullable|required_if:product_type,3|in:1,2,3,4',
        ]);

        $data = $request->all();
        
        // Re-generate code on server side to ensure accuracy and uniqueness
        $data['code'] = $this->generateProductCode($request->product_type);

        MfSavingsProduct::create($data);

        return redirect()->route('microfinance.savings-product.index')->with('success', 'Product created successfully');
    }

    public function show(MfSavingsProduct $savingsProduct)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_product'), 403, 'Unauthorized');
        $pageTitle = 'Product Details: ' . $savingsProduct->name;
        $savingsProduct->loadCount('accounts');
        return view('microfinance.savings_product.show', compact('pageTitle', 'savingsProduct'));
    }

    public function edit(MfSavingsProduct $savingsProduct)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_product_edit'), 403, 'Unauthorized');
        if ($savingsProduct->product_type == 1) {
            return redirect()->route('microfinance.savings-product.index')->with('error', 'Action denied: General Savings scheme cannot be edited.');
        }
        if ($savingsProduct->accounts()->count() > 0) {
            return redirect()->route('microfinance.savings-product.index')->with('error', 'Action denied: Accounts already exist under this scheme.');
        }
        $pageTitle = 'Edit Product: ' . $savingsProduct->name;
        return view('microfinance.savings_product.edit', compact('pageTitle', 'savingsProduct'));
    }

    public function update(Request $request, MfSavingsProduct $savingsProduct)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_product_edit'), 403, 'Unauthorized');
        if ($savingsProduct->product_type == 1) {
            return redirect()->route('microfinance.savings-product.index')->with('error', 'Update denied: General Savings scheme cannot be edited.');
        }
        if ($savingsProduct->accounts()->count() > 0) {
            return redirect()->route('microfinance.savings-product.index')->with('error', 'Update denied: Accounts already exist under this scheme.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:mf_savings_products,code,' . $savingsProduct->id,
            'product_type' => 'required|integer|in:1,2,3',
            'installment_frequency' => 'nullable|required_if:product_type,2|integer|in:1,2,3',
            'interest_rate' => 'required|numeric|min:0',
            'duration_months' => 'nullable|required_if:product_type,2,3|integer|min:1',
            'installment_amount' => 'nullable|required_if:product_type,2|numeric|min:0',
            'opening_fee' => 'required|numeric|min:0',
            'closing_fee' => 'required|numeric|min:0',
            'installment_fine_type' => 'required|in:flat,percentage',
            'installment_fine_amount' => 'required|numeric|min:0',
            'early_closing_fine_type' => 'required|in:flat,percentage',
            'early_closing_fine_amount' => 'required|numeric|min:0',
            'interest_posting_type' => 'nullable|required_if:product_type,3|in:1,2,3,4',
        ]);

        $savingsProduct->update($request->all());

        return redirect()->route('microfinance.savings-product.index')->with('success', 'Product updated successfully');
    }
}
