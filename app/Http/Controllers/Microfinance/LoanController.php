<?php

namespace App\Http\Controllers\Microfinance;

use App\Http\Controllers\Controller;
use App\Models\Microfinance\MfMember;
use App\Models\Microfinance\MfLoan;
use App\Models\Microfinance\MfLoanProduct;
use App\Models\Microfinance\MfSavingsAccount;
use App\Services\Microfinance\LoanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\UploadTrait;

class LoanController extends Controller
{
    use UploadTrait;

    protected LoanService $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function printEmptyForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan'), 403, 'Unauthorized');
        return view('microfinance.loan.print_empty_form');
    }

    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan'), 403, 'Unauthorized');
        $pageTitle = 'Loan Applications & Management';
        return view('microfinance.loan.index', compact('pageTitle'));
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan'), 403, 'Unauthorized');
        $scId  = auth()->user()->activeConcernId();
        $query = MfLoan::with('member', 'product', 'somiti')
            ->where('sister_concern_id', $scId);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (MfLoan $loan) {
                $btn = '<a href="' . route('microfinance.loan.show', $loan->id) . '" class="btn btn-sm" title="আবেদন দেখুন (View Profile)" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50% !important; background-color: #28a745; color: white; border: none; margin: 0 2px; transition: all 0.2s; box-shadow: 0 4px 6px rgba(40, 167, 69, 0.15);"><i class="fa fa-eye"></i></a>';
                if ($loan->status == 1) {
                    $btn .= ' <a href="' . route('microfinance.loan.edit', $loan->id) . '" class="btn btn-sm" title="আবেদন সংশোধন (Edit Application)" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50% !important; background-color: #007bff; color: white; border: none; margin: 0 2px; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0, 123, 255, 0.15);"><i class="fa fa-edit"></i></a>';
                    $btn .= ' <button class="btn btn-sm btn-reject" data-id="' . $loan->id . '" title="আবেদন বাতিল (Reject Application)" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50% !important; background-color: #dc3545; color: white; border: none; margin: 0 2px; transition: all 0.2s; box-shadow: 0 4px 6px rgba(220, 53, 69, 0.15);"><i class="fa fa-times"></i></button>';
                }
                return $btn;
            })
            ->addColumn('member_info', function (MfLoan $loan) {
                return $loan->member->name . '<br><small class="text-muted">' . $loan->member->member_no . '</small>';
            })
            ->editColumn('status', function (MfLoan $loan) {
                $badges = [1 => 'badge-warning', 2 => 'badge-info', 3 => 'badge-success', 4 => 'badge-dark', 5 => 'badge-danger'];
                $labels = [1 => 'Pending', 2 => 'Approved', 3 => 'Active', 4 => 'Closed', 5 => 'Rejected'];
                return '<span class="badge ' . ($badges[$loan->status] ?? 'badge-default') . '">' . ($labels[$loan->status] ?? 'Unknown') . '</span>';
            })
            ->rawColumns(['action', 'status', 'member_info'])
            ->toJson();
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_create'), 403, 'Unauthorized');
        $pageTitle = 'New Loan Application';
        $scId      = auth()->user()->activeConcernId();
        $members   = MfMember::where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $products  = MfLoanProduct::where('status', 1)->orderBy('name')->get();
        return view('microfinance.loan.create', compact('pageTitle', 'members', 'products'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_create'), 403, 'Unauthorized');
        $request->validate([
            'member_id'              => 'required|exists:mf_members,id',
            'mf_loan_product_id'     => 'required|exists:mf_loan_products,id',
            'applied_amount'         => 'required|numeric|min:1',
            'total_installments'     => 'required|integer|min:1',
            'grace_period_days'      => 'nullable|integer|min:0',
            'application_date'       => 'required|string',
            'processing_fee'         => 'nullable|numeric|min:0',
            'insurance_amount'       => 'nullable|numeric|min:0',
            'loan_purpose'           => 'nullable|string|max:500',
            'guarantor_name'         => 'nullable|string|max:255',
            'guarantor_phone'        => 'nullable|string|max:20',
            'guarantor_nid'          => 'nullable|string|max:50',
            'guarantor_photo'        => 'nullable|image|max:5120',
            'collateral_description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $member  = MfMember::findOrFail($request->member_id);
            $product = MfLoanProduct::findOrFail($request->mf_loan_product_id);

            // Enforce MFI Professional rules: Member must have an active General Savings account before taking any loan
            $hasActiveGS = \App\Models\Microfinance\MfSavingsAccount::where('member_id', $member->id)
                ->where('status', 1) // Active
                ->whereHas('product', function($q) {
                    $q->where('product_type', 1); // 1 = General Savings
                })
                ->exists();

            if (!$hasActiveGS) {
                return redirect()->back()->with('error', 'ঋণ আবেদনের পূর্বে সদস্যের একটি সাধারণ সঞ্চয় হিসাব সচল থাকা বাধ্যতামূলক।')->withInput();
            }

            $count  = MfLoan::where('member_id', $member->id)->count() + 1;
            $loanNo = $product->code . '-LN-' . $member->id . '-' . $count;

            $data = $request->except(['guarantor_photo', 'installment_start_date', '_token']);
            $data['sister_concern_id'] = auth()->user()->activeConcernId();
            $data['somiti_id']         = $member->somiti_id;
            $data['loan_no']           = $loanNo;
            $data['status']            = 1; // Pending
            $data['created_by']        = auth()->id();

            // Format dates
            $data['application_date'] = Carbon::createFromFormat('d-m-Y', $request->application_date)->format('Y-m-d');

            if ($request->hasFile('guarantor_photo')) {
                $data['guarantor_photo'] = $this->uploadImage($request->file('guarantor_photo'), 'microfinance/loan/guarantors');
            }

            MfLoan::create($data);

            DB::commit();
            return redirect()->route('microfinance.loan.index')->with('success', 'Loan application submitted. Loan No: ' . $loanNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(MfLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan'), 403, 'Unauthorized');
        // Ownership check
        abort_if($loan->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $pageTitle = 'Loan Profile: ' . $loan->loan_no;
        $loan->load('member', 'product', 'somiti', 'installments');
        return view('microfinance.loan.show', compact('pageTitle', 'loan'));
    }

    public function edit(MfLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_edit'), 403, 'Unauthorized');
        abort_if($loan->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        abort_if($loan->status != 1, 403, 'Only pending loans can be edited.');

        $pageTitle = 'Edit Loan Application';
        $scId      = auth()->user()->activeConcernId();
        $members   = MfMember::where('sister_concern_id', $scId)->where('status', 1)->orderBy('name')->get();
        $products  = MfLoanProduct::where('status', 1)->orderBy('name')->get();
        return view('microfinance.loan.edit', compact('pageTitle', 'loan', 'members', 'products'));
    }

    public function update(Request $request, MfLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_edit'), 403, 'Unauthorized');
        abort_if($loan->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        abort_if($loan->status != 1, 403);

        $request->validate([
            'member_id'              => 'required|exists:mf_members,id',
            'mf_loan_product_id'     => 'required|exists:mf_loan_products,id',
            'applied_amount'         => 'required|numeric|min:1',
            'total_installments'     => 'required|integer|min:1',
            'grace_period_days'      => 'nullable|integer|min:0',
            'application_date'       => 'required|string',
            'processing_fee'         => 'nullable|numeric|min:0',
            'insurance_amount'       => 'nullable|numeric|min:0',
            'guarantor_name'         => 'nullable|string|max:255',
            'guarantor_phone'        => 'nullable|string|max:20',
            'guarantor_nid'          => 'nullable|string|max:50',
            'guarantor_photo'        => 'nullable|image|max:5120',
            'collateral_description' => 'nullable|string|max:1000',
        ]);

        $member = MfMember::findOrFail($request->member_id);

        // Enforce MFI Professional rules: Member must have an active General Savings account before taking any loan
        $hasActiveGS = MfSavingsAccount::where('member_id', $member->id)
            ->where('status', 1) // Active
            ->whereHas('product', function($q) {
                $q->where('product_type', 1); // 1 = General Savings
            })
            ->exists();

        if (!$hasActiveGS) {
            return redirect()->back()->with('error', 'ঋণ আবেদনের পূর্বে সদস্যের একটি সাধারণ সঞ্চয় হিসাব সচল থাকা বাধ্যতামূলক।')->withInput();
        }

        $data = $request->except(['_token', '_method', 'guarantor_photo']);
        $data['application_date'] = Carbon::createFromFormat('d-m-Y', $request->application_date)->format('Y-m-d');

        if ($request->hasFile('guarantor_photo')) {
            $data['guarantor_photo'] = $this->uploadImage($request->file('guarantor_photo'), 'microfinance/loan/guarantors');
        }

        $loan->update($data);
        return redirect()->route('microfinance.loan.show', $loan->id)->with('success', 'Loan updated successfully.');
    }

    public function approve(Request $request, MfLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_approve'), 403, 'Unauthorized');
        abort_if($loan->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $request->validate([
            'approved_amount' => 'required|numeric|min:1',
        ]);

        try {
            $this->loanService->approve($loan, $request->only(['approved_amount']));
            return back()->with('success', 'Loan application approved. Waiting for disbursement.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function disburse(Request $request, MfLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_disburse'), 403, 'Unauthorized');
        abort_if($loan->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $request->validate([
            'disbursement_date'      => 'required|date',
            'first_installment_date' => 'required|date|after_or_equal:disbursement_date',
        ]);

        try {
            $this->loanService->disburse($loan, $request->only([
                'disbursement_date', 'first_installment_date'
            ]));
            return back()->with('success', 'Loan disbursed successfully. Repayment schedule created.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, MfLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_reject'), 403, 'Unauthorized');
        abort_if($loan->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        try {
            $this->loanService->reject($loan);
            return response()->json(['success' => true, 'message' => 'Loan rejected.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function close(MfLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan_close'), 403, 'Unauthorized');
        abort_if($loan->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        try {
            $this->loanService->close($loan);
            return back()->with('success', 'Loan formally closed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function repaymentSchedule(MfLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_loan'), 403, 'Unauthorized');
        abort_if($loan->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $loan->load('member', 'product', 'installments');
        $pageTitle = 'Repayment Schedule: ' . $loan->loan_no;
        return view('microfinance.loan.repayment_schedule', compact('pageTitle', 'loan'));
    }
}
