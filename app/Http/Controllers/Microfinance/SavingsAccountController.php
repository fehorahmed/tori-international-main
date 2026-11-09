<?php

namespace App\Http\Controllers\Microfinance;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Microfinance\MfMember;
use App\Models\Microfinance\MfSavingsAccount;
use App\Models\Microfinance\MfSavingsProduct;
use App\Models\Microfinance\MfSavingsTransaction;
use App\Models\Microfinance\MfNominee;
use App\Models\Microfinance\MfSomiti;
use App\Services\Microfinance\SavingsAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

use App\Http\Requests\Microfinance\StoreSavingsAccountRequest;

class SavingsAccountController extends Controller
{
    protected $savingsService;

    public function __construct(SavingsAccountService $savingsService)
    {
        $this->savingsService = $savingsService;
    }

    public function printEmptyDpsForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account'), 403, 'Unauthorized');
        return view('microfinance.savings_account.print_empty_dps_form');
    }

    public function printEmptyFdrForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account'), 403, 'Unauthorized');
        return view('microfinance.savings_account.print_empty_fdr_form');
    }

    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account'), 403, 'Unauthorized');
        $pageTitle = 'Microfinance Savings Accounts';
        $somitis = MfSomiti::where('sister_concern_id', auth()->user()->activeConcernId())->get();
        return view('microfinance.savings_account.index', compact('pageTitle', 'somitis'));
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account'), 403, 'Unauthorized');
        $query = MfSavingsAccount::with('member', 'product')
            ->where('sister_concern_id', auth()->user()->activeConcernId());

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('type', function (MfSavingsAccount $account) {
                $type = $account->product->product_type;
                return $type == 1 ? 'GS' : ($type == 2 ? 'DPS' : 'FDR');
            })
            ->editColumn('status', function (MfSavingsAccount $account) {
                if ($account->status == 0) return '<span class="badge badge-warning">Pending</span>';
                if ($account->status == 1) return '<span class="badge badge-success">Active</span>';
                return '<span class="badge badge-danger">Closed</span>';
            })
            ->addColumn('action', function (MfSavingsAccount $account) {
                $btn = '<a href="' . route('microfinance.savings-account.show', $account->id) . '" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>';
                if ($account->status == 0) {
                    $productType = $account->product->product_type;
                    $admissionFee = $productType == 1 ? floatval($account->member->admission_fee) : 0;
                    $formFee = $productType == 1 ? floatval($account->member->form_fee) : 0;
                    $passbookFee = $productType == 1 ? floatval($account->member->passbook_fee) : 0;

                    $btn .= ' <button class="btn btn-success btn-sm btn-approve" ' .
                            'data-id="' . $account->id . '" ' .
                            'data-product-type="' . $productType . '" ' .
                            'data-opening-balance="' . floatval($account->opening_balance) . '" ' .
                            'data-opening-fee="' . floatval($account->product->opening_fee) . '" ' .
                            'data-admission-fee="' . $admissionFee . '" ' .
                            'data-form-fee="' . $formFee . '" ' .
                            'data-passbook-fee="' . $passbookFee . '">' .
                            '<i class="fa fa-check"></i> Approve</button>';
                }
                return $btn;
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_create'), 403, 'Unauthorized');
        $pageTitle = 'Open Savings Account';
        $sisterConcernId = auth()->user()->activeConcernId();
        
        $members = MfMember::where('sister_concern_id', $sisterConcernId)->get();
        $products = MfSavingsProduct::where('status', 1)->get();
        $divisions = Division::all();
        
        return view('microfinance.savings_account.create', compact('pageTitle', 'members', 'products', 'divisions'));
    }

    public function getProductInfo($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account'), 403, 'Unauthorized');
        $product = MfSavingsProduct::findOrFail($id);
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'type_label' => $product->product_type == 1 ? 'GS' : ($product->product_type == 2 ? 'DPS' : 'FDR'),
            'interest_rate' => $product->interest_rate,
            'installment_amount' => $product->installment_amount,
            'opening_fee' => $product->opening_fee,
            'duration' => $product->duration_months,
            'frequency' => $product->installment_frequency == 1 ? 'Daily' : ($product->installment_frequency == 2 ? 'Weekly' : 'Monthly'),
            'product_type' => $product->product_type,
            'min_deposit' => $product->min_deposit,
        ]);
    }

    public function getMemberInfo($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account'), 403, 'Unauthorized');
        $member = MfMember::with('somiti', 'nominees')->findOrFail($id);
        return response()->json([
            'id' => $member->id,
            'name' => $member->name,
            'member_no' => $member->member_no,
            'mobile' => $member->mobile_no,
            'somiti_name' => $member->somiti?->name ?? 'N/A',
            'photo' => $member->photo ? asset($member->photo) : asset('img/no-image.webp'),
            'nominees' => $member->nominees
        ]);
    }

    public function store(StoreSavingsAccountRequest $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_create'), 403, 'Unauthorized');
        try {
            $account = $this->savingsService->openAccount($request->validated());
            return redirect()->route('microfinance.savings-account.index')->with('success', 'Account opened successfully. Account No: ' . $account->account_no);
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(MfSavingsAccount $savingsAccount)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account'), 403, 'Unauthorized');
        abort_if($savingsAccount->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $pageTitle = 'Account Ledger: ' . $savingsAccount->account_no;
        $savingsAccount->load('member', 'product', 'transactions', 'nominees');

        // Fetch active FDR products for manual renewal options
        $fdrProducts = MfSavingsProduct::where('product_type', 3) // FDR
            ->where('status', 1) // Active
            ->get();

        return view('microfinance.savings_account.show', compact('pageTitle', 'savingsAccount', 'fdrProducts'));
    }

    public function approve(Request $request, MfSavingsAccount $savingsAccount)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_approve'), 403, 'Unauthorized');
        abort_if($savingsAccount->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        try {
            $this->savingsService->approveAccount($savingsAccount, $request->all());
            return response()->json(['success' => true, 'message' => 'Account approved and initial transactions recorded!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function edit(MfSavingsAccount $savingsAccount)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_edit'), 403, 'Unauthorized');
        abort_if($savingsAccount->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        if ($savingsAccount->status != 0) {
            return redirect()->route('microfinance.savings-account.show', $savingsAccount->id)
                             ->with('error', 'Only pending accounts can be edited.');
        }

        $pageTitle = 'Edit Savings Account: ' . $savingsAccount->account_no;
        $sisterConcernId = auth()->user()->activeConcernId();
        
        $members = MfMember::where('sister_concern_id', $sisterConcernId)->get();
        $products = MfSavingsProduct::where('status', 1)->get();
        $divisions = Division::all();

        return view('microfinance.savings_account.edit', compact('pageTitle', 'savingsAccount', 'members', 'products', 'divisions'));
    }

    public function update(StoreSavingsAccountRequest $request, MfSavingsAccount $savingsAccount)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_edit'), 403, 'Unauthorized');
        abort_if($savingsAccount->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        try {
            $this->savingsService->updateAccount($savingsAccount, $request->validated());
            return redirect()->route('microfinance.savings-account.show', $savingsAccount->id)
                             ->with('success', 'Account details updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function withdraw(Request $request, MfSavingsAccount $savingsAccount)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_withdraw'), 403, 'Unauthorized');
        abort_if($savingsAccount->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date'   => 'required|date',
            'notes'  => 'nullable|string|max:500',
        ]);

        try {
            $this->savingsService->withdraw($savingsAccount, $request->only(['amount', 'date', 'notes']));
            return response()->json(['success' => true, 'message' => 'Withdrawal of ৳' . number_format($request->amount, 2) . ' recorded successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function postInterest(MfSavingsAccount $savingsAccount)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_interest'), 403, 'Unauthorized');
        abort_if($savingsAccount->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        try {
            $txn = $this->savingsService->postInterest($savingsAccount);
            return response()->json(['success' => true, 'message' => 'Interest of ৳' . number_format($txn->amount, 2) . ' posted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function closeAccount(Request $request, MfSavingsAccount $savingsAccount)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_close'), 403, 'Unauthorized');
        abort_if($savingsAccount->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $request->validate(['close_reason' => 'nullable|string|max:500']);

        try {
            $this->savingsService->closeAccount($savingsAccount, $request->close_reason ?? '');
            return back()->with('success', 'Account closed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeNominee(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_edit'), 403, 'Unauthorized');
        $request->validate([
            'mf_member_id' => 'required|exists:mf_members,id',
            'name' => 'required|string|max:255',
            'relation' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'share_percentage' => 'nullable|numeric|min:0|max:100',
            'dob' => 'nullable|date',
            'nid_no' => 'nullable|string|max:50',
            'division_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'upazila_id' => 'nullable|integer',
            'union_id' => 'nullable|integer',
            'pourashava_id' => 'nullable|integer',
            'village' => 'nullable|string',
            'house_road' => 'nullable|string',
        ]);

        $dob = $request->dob ? date('Y-m-d', strtotime($request->dob)) : null;

        $nominee = MfNominee::create([
            'mf_member_id' => $request->mf_member_id,
            'name' => $request->name,
            'relation' => $request->relation,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'share_percentage' => $request->share_percentage ?? 100,
            'dob' => $dob,
            'nid_no' => $request->nid_no,
            'division_id' => $request->division_id,
            'district_id' => $request->district_id,
            'upazila_id' => $request->upazila_id,
            'union_id' => $request->union_id,
            'pourashava_id' => $request->pourashava_id,
            'village' => $request->village,
            'house_road' => $request->house_road,
        ]);

        return response()->json([
            'success' => true,
            'nominee' => $nominee
        ]);
    }

    public function renew(Request $request, MfSavingsAccount $savingsAccount)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_savings_account_renew'), 403, 'Unauthorized');
        abort_if($savingsAccount->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $validated = $request->validate([
            'renewal_date' => 'required|date_format:d-m-Y',
            'renewal_mode' => 'required|in:principal_only,compound',
            'mf_savings_product_id' => 'nullable|exists:mf_savings_products,id',
        ]);

        try {
            $this->savingsService->renewAccount($savingsAccount, $validated);
            return response()->json([
                'success' => true,
                'message' => 'FDR Account renewed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 422);
        }
    }
}
