<?php

namespace App\Http\Controllers\ShomriddhiFdr;

use App\Enumeration\SupplierType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Division;
use App\Models\Nominee;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrScheme;
use App\Models\Supplier;
use App\Services\ShomriddhiFdr\FdrAccountService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class FdrAccountController extends Controller
{
    public function index()
    {
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')
                ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })->where('payment_mode', '>', 0)->where('status', 1)->get();

        return view('shomriddhi_fdr.accounts.index', compact('paymentModes'));
    }

    public function dataTable()
    {
        $query = FdrAccount::with(['customer', 'scheme'])
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('customer', function ($row) {
                if ($row->customer) {
                    return '<a href="#">'.e($row->customer->name).'</a><br><small class="text-muted">'.e($row->customer->mobile_no).'</small>';
                }

                return '-';
            })
            ->addColumn('scheme', function ($row) {
                return e($row->scheme->name).'<br><small class="badge badge-info">'.e($row->scheme->duration).' Months</small>';
            })
            ->addColumn('principal_amount', function ($row) {
                return '<span class="font-weight-bold text-success">৳ '.number_format($row->principal_amount, 2).'</span>';
            })
            ->addColumn('maturity_date', function ($row) {
                return Carbon::parse($row->maturity_date)->format('d-M-Y');
            })
            ->addColumn('payable_amount', function ($row) {
                return '<span class="font-weight-bold text-primary">৳ '.number_format($row->payable_amount_at_maturity, 2).'</span>';
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 'Pending') {
                    return '<span class="badge badge-warning">Pending Approval</span>';
                }
                if ($row->status == 'Active') {
                    return '<span class="badge badge-success">Active</span>';
                }
                if ($row->status == 'Matured') {
                    return '<span class="badge badge-primary">Matured</span>';
                }

                return '<span class="badge badge-secondary">Closed</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '';

                // View Action
                if (auth()->user()->can('shomriddhi_fdr_accounts')) {
                    $btn .= ' <a href="'.route('shomriddhi-fdr.accounts.show', $row->id).'" class="btn btn-primary btn-sm btn-view" title="View Details"><i class="fa fa-eye"></i></a>';
                }

                // Encash Action
                if (in_array($row->status, ['Active', 'Matured']) && (auth()->user()->can('shomriddhi_fdr_account_approve'))) {
                    $btn .= ' <a role="button" data-id="'.$row->id.'" data-amount="'.$row->principal_amount.'" class="btn btn-warning btn-sm btn-encash"><i class="fa fa-hand-holding-usd"></i></a>';
                }
                if ($row->status === 'Pending') {
                    // Edit Action
                    if (auth()->user()->can('shomriddhi_fdr_account_create')) {
                        $btn .= ' <a href="'.route('shomriddhi-fdr.accounts.edit', $row->id).'" class="btn btn-info bg-gradient-info btn-sm btn-edit" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    // Delete Action
                    if (auth()->user()->can('shomriddhi_fdr_account_create')) {
                        $btn .= ' <a role="button" data-id="'.$row->id.'" class="btn btn-danger btn-sm btn-delete" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                } else {
                    $btn .= ' <button class="btn btn-secondary btn-sm" disabled title="Locked"><i class="fa fa-lock"></i></button>';
                }

                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['customer', 'scheme', 'principal_amount', 'payable_amount', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_account_create'), 403);
        $customers = Supplier::select('id', 'name', 'mobile_no', 'member_no')
            ->where('type', SupplierType::$CUSTOMER)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereHas('shomriddhiAccount')
            ->orderBy('name', 'asc')
            ->get();
        $schemes = FdrScheme::where('status', 'Active')->get();
        $divisions = Division::orderBy('name')->get();

        return view('shomriddhi_fdr.accounts.create', compact('customers', 'schemes', 'divisions'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_account_create'), 403);
        $request->validate([
            'customer_id' => 'required|exists:suppliers,id',
            'scheme_id' => 'required|exists:fdr_schemes,id',
            'principal_amount' => 'required|numeric|decimal:0,2|min:0.01|max:9999999999.99',
            'interest_payout_frequency' => 'required|in:Monthly,Quarterly,Half-Yearly,Yearly,At Maturity',
            'opening_date' => 'required|date_format:d-m-Y',
            'nominees' => 'nullable|array',
            'nominees.*.id' => 'required_with:nominees|distinct|exists:nominees,id',
            'nominees.*.share_percentage' => 'required_with:nominees|numeric|min:1|max:100',
        ]);

        Supplier::whereKey($request->customer_id)->where('type', 'Customer')->where('status', 1)->where('origin_sister_concern_id', auth()->user()->activeConcernId())->firstOrFail();
        // Check if the customer has an active Shomriddhi Advance (Savings) account
        $hasSavings = ShomriddhiAccount::where('customer_id', $request->customer_id)
            ->where('status', 'Active')
            ->exists();

        if (! $hasSavings) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['customer_id' => 'This customer does not have an Active Shomriddhi Advance (Savings) account. FDR cannot be opened.']);
        }

        // Validate Nominee Total Percentage if provided
        if ($request->has('nominees') && count($request->nominees) > 0) {
            $totalPercentage = collect($request->nominees)->sum('share_percentage');
            if ($totalPercentage != 100) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['nominees' => 'Total nominee share must be exactly 100%. Currently it is '.$totalPercentage.'%.']);
            }
        }

        Supplier::whereKey($request->customer_id)->where('type', 'Customer')->where('status', 1)->where('origin_sister_concern_id', auth()->user()->activeConcernId())->firstOrFail();
        $scheme = FdrScheme::whereKey($request->scheme_id)->where('status', 'Active')->firstOrFail();

        // Validate min/max amount
        if ($request->principal_amount < $scheme->min_amount) {
            return back()->with('error', 'Amount is less than the scheme minimum of '.$scheme->min_amount)->withInput();
        }
        if ($scheme->max_amount && $request->principal_amount > $scheme->max_amount) {
            return back()->with('error', 'Amount is greater than the scheme maximum of '.$scheme->max_amount)->withInput();
        }

        // Calculate maturity date and payable amount
        $openingDate = Carbon::createFromFormat('d-m-Y', $request->opening_date);
        $maturityDate = $openingDate->copy()->addMonthsNoOverflow($scheme->duration);

        $interestAmount = app(FdrAccountService::class)->termInterest($request->principal_amount, $scheme->interest_rate, $scheme->duration) / 100;
        $payableAmount = $request->principal_amount + $interestAmount; // Simple calculation for now

        // Calculate Next Interest Date
        $nextInterestDate = null;
        if ($request->interest_payout_frequency === 'Monthly') {
            $nextInterestDate = $openingDate->copy()->addMonthsNoOverflow(1);
        } elseif ($request->interest_payout_frequency === 'Quarterly') {
            $nextInterestDate = $openingDate->copy()->addMonthsNoOverflow(3);
        } elseif ($request->interest_payout_frequency === 'Half-Yearly') {
            $nextInterestDate = $openingDate->copy()->addMonthsNoOverflow(6);
        } elseif ($request->interest_payout_frequency === 'Yearly') {
            $nextInterestDate = $openingDate->copy()->addMonthsNoOverflow(12);
        } else {
            $nextInterestDate = $maturityDate;
        }

        $nextInterestDate = $nextInterestDate->min($maturityDate);

        DB::transaction(function () use ($request, $scheme, $openingDate, $maturityDate, $payableAmount, $nextInterestDate) {
            SisterConcern::whereKey(auth()->user()->activeConcernId())->lockForUpdate()->firstOrFail();
            // Generate FDR Number based on Savings Account
            $shomriddhiAccount = ShomriddhiAccount::where('customer_id', $request->customer_id)->first();
            $savingsAcNo = $shomriddhiAccount ? $shomriddhiAccount->account_number : '000000000';
            $customerFdrCount = FdrAccount::withTrashed()->where('customer_id', $request->customer_id)->count();
            $fdrNo = $savingsAcNo.'-F'.str_pad($customerFdrCount + 1, 2, '0', STR_PAD_LEFT);

            $account = FdrAccount::create([
                'fdr_no' => $fdrNo,
                'customer_id' => $request->customer_id,
                'scheme_id' => $request->scheme_id,
                'origin_sister_concern_id' => auth()->user()->activeConcernId(),
                'principal_amount' => $request->principal_amount,
                'interest_rate' => $scheme->interest_rate, 'duration_months' => $scheme->duration, 'penalty_rate' => $scheme->penalty_rate ?? 0,
                'interest_payout_frequency' => $request->interest_payout_frequency,
                'next_interest_date' => $nextInterestDate,
                'opening_date' => $openingDate->format('Y-m-d'),
                'maturity_date' => $maturityDate,
                'payable_amount_at_maturity' => $payableAmount,
                'auto_renew' => $request->has('auto_renew') ? 1 : 0,
                'status' => 'Pending',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            if ($request->has('nominees')) {
                foreach ($request->nominees as $nominee) {
                    Nominee::whereKey($nominee['id'])->where('customer_id', $account->customer_id)->firstOrFail();
                    DB::table('fdr_nominees')->insert([
                        'fdr_account_id' => $account->id,
                        'nominee_id' => $nominee['id'],
                        'share_percentage' => $nominee['share_percentage'] ?? 100,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

        }, 3);

        return redirect()->route('shomriddhi-fdr.accounts.index')->with('success', 'FDR Account created successfully and is pending approval.');
    }

    public function show($id)
    {
        $account = FdrAccount::with(['customer', 'scheme', 'nominees', 'transactions'])->findOrFail($id);
        app(FdrAccountService::class)->adminBranch($account);

        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')
                ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })->where('payment_mode', '>', 0)->where('status', 1)->get();

        return view('shomriddhi_fdr.accounts.show', compact('account', 'paymentModes'));
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_account_create'), 403);
        $account = FdrAccount::findOrFail($id);
        app(FdrAccountService::class)->adminBranch($account);
        if ($account->status !== 'Pending') {
            return redirect()->route('shomriddhi-fdr.accounts.index')->with('error', 'Only pending accounts can be edited.');
        }

        $customers = Supplier::select('id', 'name', 'mobile_no', 'member_no')
            ->where('type', SupplierType::$CUSTOMER)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereHas('shomriddhiAccount')
            ->orderBy('name', 'asc')
            ->get();
        $schemes = FdrScheme::where('status', 'Active')->orWhere('id', $account->scheme_id)->get();

        return view('shomriddhi_fdr.accounts.edit', compact('account', 'customers', 'schemes'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_account_create'), 403);
        $account = FdrAccount::findOrFail($id);
        app(FdrAccountService::class)->adminBranch($account);
        if ($account->status !== 'Pending') {
            return redirect()->route('shomriddhi-fdr.accounts.index')->with('error', 'Only pending accounts can be edited.');
        }

        $request->validate([
            'customer_id' => 'required|exists:suppliers,id',
            'scheme_id' => 'required|exists:fdr_schemes,id',
            'principal_amount' => 'required|numeric|decimal:0,2|min:0.01|max:9999999999.99',
            'interest_payout_frequency' => 'required|in:Monthly,Quarterly,Half-Yearly,Yearly,At Maturity',
            'opening_date' => 'required|date_format:d-m-Y',
            'auto_renew' => 'boolean',
        ]);

        Supplier::whereKey($request->customer_id)->where('type', 'Customer')->where('status', 1)->where('origin_sister_concern_id', auth()->user()->activeConcernId())->firstOrFail();
        $scheme = FdrScheme::whereKey($request->scheme_id)->where('status', 'Active')->firstOrFail();

        if ($request->principal_amount < $scheme->min_amount) {
            return back()->with('error', 'Amount is less than the scheme minimum of '.$scheme->min_amount)->withInput();
        }
        if ($scheme->max_amount && $request->principal_amount > $scheme->max_amount) {
            return back()->with('error', 'Amount is greater than the scheme maximum of '.$scheme->max_amount)->withInput();
        }

        $openingDate = Carbon::createFromFormat('d-m-Y', $request->opening_date);
        $maturityDate = $openingDate->copy()->addMonthsNoOverflow($scheme->duration);

        $interestAmount = app(FdrAccountService::class)->termInterest($request->principal_amount, $scheme->interest_rate, $scheme->duration) / 100;
        $payableAmount = $request->principal_amount + $interestAmount;

        $nextInterestDate = null;
        if ($request->interest_payout_frequency === 'Monthly') {
            $nextInterestDate = $openingDate->copy()->addMonthsNoOverflow(1);
        } elseif ($request->interest_payout_frequency === 'Quarterly') {
            $nextInterestDate = $openingDate->copy()->addMonthsNoOverflow(3);
        } elseif ($request->interest_payout_frequency === 'Half-Yearly') {
            $nextInterestDate = $openingDate->copy()->addMonthsNoOverflow(6);
        } elseif ($request->interest_payout_frequency === 'Yearly') {
            $nextInterestDate = $openingDate->copy()->addMonthsNoOverflow(12);
        } else {
            $nextInterestDate = $maturityDate;
        }

        $nextInterestDate = $nextInterestDate->min($maturityDate);
        DB::transaction(function () use ($id, $request, $scheme, $openingDate, $maturityDate, $payableAmount, $nextInterestDate) {
            $account = FdrAccount::whereKey($id)->lockForUpdate()->firstOrFail();
            app(FdrAccountService::class)->adminBranch($account);
            if ($account->status !== 'Pending') {
                throw ValidationException::withMessages(['fdr' => 'Only pending accounts can be edited.']);
            }
            if ((int) $account->customer_id !== (int) $request->customer_id) {
                throw ValidationException::withMessages(['customer_id' => 'The member cannot be changed after an FDR account is created.']);
            }
            $account->update([
                'customer_id' => $request->customer_id,
                'scheme_id' => $request->scheme_id,
                'principal_amount' => $request->principal_amount,
                'interest_rate' => $scheme->interest_rate, 'duration_months' => $scheme->duration, 'penalty_rate' => $scheme->penalty_rate ?? 0,
                'interest_payout_frequency' => $request->interest_payout_frequency,
                'next_interest_date' => $nextInterestDate,
                'opening_date' => $openingDate->format('Y-m-d'),
                'maturity_date' => $maturityDate,
                'payable_amount_at_maturity' => $payableAmount,
                'auto_renew' => $request->has('auto_renew') ? 1 : 0,
                'notes' => $request->notes,
            ]);
        }, 3);

        return redirect()->route('shomriddhi-fdr.accounts.index')->with('success', 'FDR Account updated successfully.');
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->can('shomriddhi_fdr_account_create'), 403);
        try {
            return DB::transaction(function () use ($id) {
                $account = FdrAccount::whereKey($id)->lockForUpdate()->firstOrFail();
                app(FdrAccountService::class)->adminBranch($account);
                if ($account->status !== 'Pending') {
                    return response()->json(['success' => false, 'message' => 'Only pending accounts can be deleted.']);
                }
                $account->delete();

                return response()->json(['success' => true, 'message' => 'FDR Account deleted successfully.']);
            }, 3);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete account: '.$e->getMessage()], 200);
        }
    }

    public function approve(Request $request, $id)
    {
        $account = FdrAccount::findOrFail($id);
        app(FdrAccountService::class)->adminBranch($account);
        if ($account->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Account is already approved or closed.']);
        }

        $request->validate(['payment_source' => 'required|in:cash_bank,savings', 'payment_mode' => 'required_if:payment_source,cash_bank|nullable|integer']);

        $service = new FdrAccountService;
        $result = $service->approve($account, (int) $request->payment_mode, $request->payment_source);

        return response()->json($result);
    }

    public function encash(Request $request, $id)
    {
        $account = FdrAccount::findOrFail($id);
        app(FdrAccountService::class)->adminBranch($account);
        if (! in_array($account->status, ['Active', 'Matured'])) {
            return response()->json(['success' => false, 'message' => 'Only active accounts can be encashed.']);
        }

        $request->validate([
            'payment_source' => 'required|in:cash_bank,savings',
            'payment_mode' => 'required_if:payment_source,cash_bank|nullable|integer',

        ]);

        $service = new FdrAccountService;
        $result = $service->encash($account, $request->all());

        return response()->json($result);
    }

    public function encashData($id)
    {
        $account = FdrAccount::findOrFail($id);
        app(FdrAccountService::class)->adminBranch($account);
        if (! in_array($account->status, ['Active', 'Matured'])) {
            return response()->json(['success' => false, 'message' => 'Only active accounts can be encashed.']);
        }

        $date = SisterConcern::findOrFail($account->origin_sister_concern_id)->business_date;
        $data = app(FdrAccountService::class)->settlement($account, $date);

        return response()->json(['success' => true, 'interest_amount' => $data['interest_adjustment'], 'penalty_amount' => $data['penalty_amount'], 'net_payable' => $data['net_payable'], 'is_premature' => $data['is_premature'], 'paid_interest' => $data['paid_interest']]);
    }
}
