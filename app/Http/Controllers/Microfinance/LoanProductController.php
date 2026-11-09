<?php

namespace App\Http\Controllers\Microfinance;

use App\Http\Controllers\Controller;
use App\Models\Microfinance\MfLoanProduct;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LoanProductController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_product'), 403, 'Unauthorized');
        $pageTitle = 'Loan Products (Schemes)';
        return view('microfinance.loan_product.index', compact('pageTitle'));
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_product'), 403, 'Unauthorized');
        $query = MfLoanProduct::withCount(['loans' => function ($q) {
            $q->where('sister_concern_id', auth()->user()->activeConcernId());
        }]);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (MfLoanProduct $product) {
                $btn = '<a href="' . route('microfinance.loan-product.show', $product->id) . '" class="btn btn-info btn-sm" title="View Details"><i class="fa fa-eye"></i></a>';
                
                if ($product->loans_count > 0) {
                    $btn .= ' <button class="btn btn-secondary btn-sm" disabled title="Cannot edit: Loans exist"><i class="fa fa-lock"></i></button>';
                } else {
                    $btn .= ' <a href="' . route('microfinance.loan-product.edit', $product->id) . '" class="btn btn-primary btn-sm" title="Edit"><i class="fa fa-edit"></i></a>';
                }
                
                return $btn;
            })
            ->editColumn('installment_frequency', function (MfLoanProduct $product) {
                switch($product->installment_frequency) {
                    case 1: return '<span class="badge badge-info">Daily</span>';
                    case 2: return '<span class="badge badge-primary">Weekly</span>';
                    case 3: return '<span class="badge badge-warning">Monthly</span>';
                    case 4: return '<span class="badge badge-danger">Quarterly</span>';
                    case 5: return '<span class="badge badge-secondary">Half-Yearly</span>';
                    case 6: return '<span class="badge badge-dark">One-Time / At Maturity</span>';
                    default: return 'N/A';
                }
            })
            ->editColumn('status', function (MfLoanProduct $product) {
                return $product->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('late_penalty', function (MfLoanProduct $product) {
                if ($product->late_penalty_rate <= 0) {
                    return '<span class="text-muted">None</span>';
                }
                $suffix = $product->late_penalty_type === 'flat' ? ' ৳' : '%';
                return number_format($product->late_penalty_rate, 2) . $suffix;
            })
            ->rawColumns(['action', 'status', 'installment_frequency', 'late_penalty'])
            ->toJson();
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_product_create'), 403, 'Unauthorized');
        $pageTitle = 'Add New Loan Product';
        return view('microfinance.loan_product.create', compact('pageTitle'));
    }

    public function getNextProductCode(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_product'), 403, 'Unauthorized');
        $nextCode = $this->generateProductCode($request->frequency);
        return response()->json(['code' => $nextCode]);
    }

    private function generateProductCode($frequency)
    {
        $prefix = '';
        if ($frequency == 1) $prefix = 'LPD';
        elseif ($frequency == 2) $prefix = 'LPW';
        elseif ($frequency == 3) $prefix = 'LPM';
        elseif ($frequency == 4) $prefix = 'LPQ';
        elseif ($frequency == 5) $prefix = 'LPH';
        elseif ($frequency == 6) $prefix = 'LPT';
        else $prefix = 'LP';

        $lastProduct = MfLoanProduct::where('installment_frequency', $frequency)
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
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_product_create'), 403, 'Unauthorized');
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:mf_loan_products,code',
            'interest_rate' => 'required|numeric|min:0',
            'interest_type' => 'required|integer|in:1,2',
            'installment_frequency' => 'required|integer|in:1,2,3,4,5,6',
            'insurance_fee_type' => 'required|in:flat,percentage',
            'insurance_fee_percentage' => 'required|numeric|min:0',
            'late_penalty_type' => 'required|in:flat,percentage',
            'late_penalty_rate' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        
        // Re-generate code on server side to ensure accuracy and uniqueness
        $data['code'] = $this->generateProductCode($request->installment_frequency);

        MfLoanProduct::create($data);

        return redirect()->route('microfinance.loan-product.index')->with('success', 'Loan product created successfully');
    }

    public function show(MfLoanProduct $loanProduct)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_product'), 403, 'Unauthorized');
        $pageTitle = 'Loan Product Scheme Details: ' . $loanProduct->name;
        $loanProduct->loadCount('loans');
        return view('microfinance.loan_product.show', compact('pageTitle', 'loanProduct'));
    }

    public function edit(MfLoanProduct $loanProduct)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_product_edit'), 403, 'Unauthorized');
        if ($loanProduct->loans()->count() > 0) {
            return redirect()->route('microfinance.loan-product.index')->with('error', 'Action denied: Loans already exist under this scheme.');
        }
        $pageTitle = 'Edit Loan Product: ' . $loanProduct->name;
        return view('microfinance.loan_product.edit', compact('pageTitle', 'loanProduct'));
    }

    public function update(Request $request, MfLoanProduct $loanProduct)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_product_edit'), 403, 'Unauthorized');
        if ($loanProduct->loans()->count() > 0) {
            return redirect()->route('microfinance.loan-product.index')->with('error', 'Update denied: Loans already exist under this scheme.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:mf_loan_products,code,' . $loanProduct->id,
            'interest_rate' => 'required|numeric|min:0',
            'interest_type' => 'required|integer|in:1,2',
            'installment_frequency' => 'required|integer|in:1,2,3,4,5,6',
            'insurance_fee_type' => 'required|in:flat,percentage',
            'insurance_fee_percentage' => 'required|numeric|min:0',
            'late_penalty_type' => 'required|in:flat,percentage',
            'late_penalty_rate' => 'required|numeric|min:0',
        ]);

        $loanProduct->update($request->all());

        return redirect()->route('microfinance.loan-product.index')->with('success', 'Loan product updated successfully');
    }
}
