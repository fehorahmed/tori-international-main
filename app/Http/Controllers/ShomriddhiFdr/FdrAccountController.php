<?php

namespace App\Http\Controllers\ShomriddhiFdr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrScheme;
use App\Models\Supplier;
use App\Models\ShomriddhiFdr\FdrTransaction;
use App\Enumeration\SupplierType;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\Division;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Account\AccountHead;
use App\Services\ShomriddhiFdr\FdrAccountService;
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
                    return '<a href="#">' . e($row->customer->name) . '</a><br><small class="text-muted">' . e($row->customer->phone) . '</small>';
                }
                return '-';
            })
            ->addColumn('scheme', function ($row) {
                return e($row->scheme->name) . '<br><small class="badge badge-info">' . e($row->scheme->duration) . ' Months</small>';
            })
            ->addColumn('principal_amount', function ($row) {
                return '<span class="font-weight-bold text-success">৳ ' . number_format($row->principal_amount, 2) . '</span>';
            })
            ->addColumn('maturity_date', function ($row) {
                return \Carbon\Carbon::parse($row->maturity_date)->format('d-M-Y');
            })
            ->addColumn('payable_amount', function ($row) {
                return '<span class="font-weight-bold text-primary">৳ ' . number_format($row->payable_amount_at_maturity, 2) . '</span>';
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 'Pending') return '<span class="badge badge-warning">Pending Approval</span>';
                if ($row->status == 'Active') return '<span class="badge badge-success">Active</span>';
                if ($row->status == 'Matured') return '<span class="badge badge-primary">Matured</span>';
                return '<span class="badge badge-secondary">Closed</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '';
                
                // View Action
                if (auth()->user()->can('shomriddhi_fdr_account_view') || true) {
                    $btn .= ' <a href="'.route('shomriddhi-fdr.accounts.show', $row->id).'" class="btn btn-primary btn-sm btn-view" title="View Details"><i class="fa fa-eye"></i></a>';
                }
                

                // Encash Action
                if ($row->status == 'Active' && (auth()->user()->can('shomriddhi_fdr_account_encash') || true)) {
                    $btn .= ' <a role="button" data-id="' . $row->id . '" data-amount="' . $row->principal_amount . '" class="btn btn-warning btn-sm btn-encash"><i class="fa fa-hand-holding-usd"></i></a>';
                }
                if ($row->status === 'Pending') {
                    // Edit Action
                    if (auth()->user()->can('shomriddhi_fdr_account_edit') || true) {
                        $btn .= ' <a href="'.route('shomriddhi-fdr.accounts.edit', $row->id).'" class="btn btn-info bg-gradient-info btn-sm btn-edit" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    // Delete Action
                    if (auth()->user()->can('shomriddhi_fdr_account_delete') || true) {
                        $btn .= ' <a role="button" data-id="' . $row->id . '" class="btn btn-danger btn-sm btn-delete" title="Delete"><i class="fa fa-trash"></i></a>';
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
        $customers = Supplier::select('id', 'name', 'mobile_no', 'member_no')
            ->where('type', SupplierType::$CUSTOMER)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereHas('shomriddhiAccount')
            ->orderBy('name', 'asc')
            ->get();
        $schemes = FdrScheme::where('status', 1)->get();
        $divisions = Division::orderBy('name')->get();

        return view('shomriddhi_fdr.accounts.create', compact('customers', 'schemes', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:suppliers,id',
            'scheme_id' => 'required|exists:fdr_schemes,id',
            'principal_amount' => 'required|numeric|min:1',
            'interest_payout_frequency' => 'required|in:Monthly,Quarterly,Half-Yearly,Yearly,At Maturity',
            'opening_date' => 'required|date_format:d-m-Y',
            'nominees' => 'nullable|array',
            'nominees.*.id' => 'required_with:nominees|exists:nominees,id',
            'nominees.*.share_percentage' => 'required_with:nominees|numeric|min:1|max:100',
        ]);

        // Check if the customer has an active Shomriddhi Advance (Savings) account
        $hasSavings = ShomriddhiAccount::where('customer_id', $request->customer_id)
            ->where('status', 'Active')
            ->exists();

        if (!$hasSavings) {
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
                    ->withErrors(['nominees' => 'Total nominee share must be exactly 100%. Currently it is ' . $totalPercentage . '%.']);
            }
        }

        $scheme = FdrScheme::findOrFail($request->scheme_id);
        
        // Validate min/max amount
        if ($request->principal_amount < $scheme->min_amount) {
            return back()->with('error', 'Amount is less than the scheme minimum of ' . $scheme->min_amount)->withInput();
        }
        if ($scheme->max_amount && $request->principal_amount > $scheme->max_amount) {
            return back()->with('error', 'Amount is greater than the scheme maximum of ' . $scheme->max_amount)->withInput();
        }

        // Calculate maturity date and payable amount
        $openingDate = Carbon::createFromFormat('d-m-Y', $request->opening_date);
        $maturityDate = $openingDate->copy()->addMonths($scheme->duration);
        
        $interestAmount = ($request->principal_amount * $scheme->interest_rate / 100);
        $payableAmount = $request->principal_amount + $interestAmount; // Simple calculation for now

        // Calculate Next Interest Date
        $nextInterestDate = null;
        if ($request->interest_payout_frequency === 'Monthly') {
            $nextInterestDate = $openingDate->copy()->addMonth();
        } elseif ($request->interest_payout_frequency === 'Quarterly') {
            $nextInterestDate = $openingDate->copy()->addMonths(3);
        } elseif ($request->interest_payout_frequency === 'Half-Yearly') {
            $nextInterestDate = $openingDate->copy()->addMonths(6);
        } elseif ($request->interest_payout_frequency === 'Yearly') {
            $nextInterestDate = $openingDate->copy()->addYear();
        } else {
            $nextInterestDate = $maturityDate;
        }

        // Generate FDR Number based on Savings Account
        $shomriddhiAccount = ShomriddhiAccount::where('customer_id', $request->customer_id)->first();
        $savingsAcNo = $shomriddhiAccount ? $shomriddhiAccount->account_number : '000000000';
        $customerFdrCount = FdrAccount::where('customer_id', $request->customer_id)->count();
        $fdrNo = $savingsAcNo . '-F' . str_pad($customerFdrCount + 1, 2, '0', STR_PAD_LEFT);

        $account = FdrAccount::create([
            'fdr_no' => $fdrNo,
            'customer_id' => $request->customer_id,
            'scheme_id' => $request->scheme_id,
            'origin_sister_concern_id' => auth()->user()->activeConcernId(),
            'principal_amount' => $request->principal_amount,
            'interest_payout_frequency' => $request->interest_payout_frequency,
            'next_interest_date' => $nextInterestDate,
            'opening_date' => $openingDate->format('Y-m-d'),
            'maturity_date' => $maturityDate,
            'payable_amount_at_maturity' => $payableAmount,
            'auto_renew' => $request->has('auto_renew') ? 1 : 0,
            'status' => 'Pending',
            'notes' => $request->notes,
            'created_by' => auth()->id()
        ]);

        if ($request->has('nominees')) {
            foreach ($request->nominees as $nominee) {
                DB::table('fdr_nominees')->insert([
                    'fdr_account_id' => $account->id,
                    'nominee_id' => $nominee['id'],
                    'share_percentage' => $nominee['share_percentage'] ?? 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('shomriddhi-fdr.accounts.index')->with('success', 'FDR Account created successfully and is pending approval.');
    }

    public function show($id)
    {
        $account = FdrAccount::with(['customer', 'scheme', 'nominees', 'transactions'])->findOrFail($id);
        
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')
              ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })->where('payment_mode', '>', 0)->where('status', 1)->get();

        return view('shomriddhi_fdr.accounts.show', compact('account', 'paymentModes'));
    }

    public function edit($id)
    {
        $account = FdrAccount::findOrFail($id);
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
        $account = FdrAccount::findOrFail($id);
        if ($account->status !== 'Pending') {
            return redirect()->route('shomriddhi-fdr.accounts.index')->with('error', 'Only pending accounts can be edited.');
        }

        $request->validate([
            'customer_id' => 'required|exists:suppliers,id',
            'scheme_id' => 'required|exists:fdr_schemes,id',
            'principal_amount' => 'required|numeric|min:0',
            'interest_payout_frequency' => 'required|in:Monthly,Quarterly,Half-Yearly,Yearly,At Maturity',
            'opening_date' => 'required|date_format:d-m-Y',
            'auto_renew' => 'boolean'
        ]);

        $scheme = FdrScheme::findOrFail($request->scheme_id);
        
        if ($request->principal_amount < $scheme->min_amount) {
            return back()->with('error', 'Amount is less than the scheme minimum of ' . $scheme->min_amount)->withInput();
        }
        if ($scheme->max_amount && $request->principal_amount > $scheme->max_amount) {
            return back()->with('error', 'Amount is greater than the scheme maximum of ' . $scheme->max_amount)->withInput();
        }

        $openingDate = Carbon::createFromFormat('d-m-Y', $request->opening_date);
        $maturityDate = $openingDate->copy()->addMonths($scheme->duration);
        
        $interestAmount = ($request->principal_amount * $scheme->interest_rate / 100);
        $payableAmount = $request->principal_amount + $interestAmount;

        $nextInterestDate = null;
        if ($request->interest_payout_frequency === 'Monthly') {
            $nextInterestDate = $openingDate->copy()->addMonth();
        } elseif ($request->interest_payout_frequency === 'Quarterly') {
            $nextInterestDate = $openingDate->copy()->addMonths(3);
        } elseif ($request->interest_payout_frequency === 'Half-Yearly') {
            $nextInterestDate = $openingDate->copy()->addMonths(6);
        } elseif ($request->interest_payout_frequency === 'Yearly') {
            $nextInterestDate = $openingDate->copy()->addYear();
        } else {
            $nextInterestDate = $maturityDate;
        }

        $account->update([
            'customer_id' => $request->customer_id,
            'scheme_id' => $request->scheme_id,
            'principal_amount' => $request->principal_amount,
            'interest_payout_frequency' => $request->interest_payout_frequency,
            'next_interest_date' => $nextInterestDate,
            'opening_date' => $openingDate->format('Y-m-d'),
            'maturity_date' => $maturityDate,
            'payable_amount_at_maturity' => $payableAmount,
            'auto_renew' => $request->has('auto_renew') ? 1 : 0,
            'notes' => $request->notes
        ]);

        return redirect()->route('shomriddhi-fdr.accounts.index')->with('success', 'FDR Account updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $account = FdrAccount::findOrFail($id);
            if ($account->status !== 'Pending') {
                return response()->json(['success' => false, 'message' => 'Only pending accounts can be deleted.'], 200);
            }
            $account->delete();
            return response()->json(['success' => true, 'message' => 'FDR Account deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete account: ' . $e->getMessage()], 200);
        }
    }

    public function approve(Request $request, $id)
    {
        $account = FdrAccount::findOrFail($id);
        if ($account->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Account is already approved or closed.']);
        }

        $request->validate(['payment_mode' => 'required']);

        $service = new FdrAccountService();
        $result = $service->approve($account, (int) $request->payment_mode);

        return response()->json($result);
    }

    public function encash(Request $request, $id)
    {
        $account = FdrAccount::findOrFail($id);
        if ($account->status !== 'Active') {
            return response()->json(['success' => false, 'message' => 'Only active accounts can be encashed.']);
        }

        $request->validate([
            'payment_mode' => 'required',
            'interest_amount' => 'required|numeric|min:0',
            'penalty_amount' => 'required|numeric|min:0',
        ]);

        $service = new FdrAccountService();
        $result = $service->encash($account, $request->all());

        return response()->json($result);
    }

    public function encashData($id)
    {
        $account = FdrAccount::findOrFail($id);
        if ($account->status !== 'Active') {
            return response()->json(['success' => false, 'message' => 'Only active accounts can be encashed.']);
        }

        $openingDate = \Carbon\Carbon::parse($account->opening_date);
        $today = \Carbon\Carbon::parse(auth()->user()->business_date ?? today());
        $daysPassed = $openingDate->diffInDays($today);

        // Assume duration in months
        $durationDays = $account->scheme->duration * 30; 
        $isPremature = $daysPassed < $durationDays;

        $interestAmount = 0;
        $penaltyAmount = 0;

        if ($isPremature) {
            // Calculate penalty amount if any
            $penaltyRate = $account->scheme->penalty_rate ?? 0;
            $penaltyAmount = ($account->principal_amount * $penaltyRate / 100);
            
            // Calculate partial interest if applicable (simplified logic)
            // For premature, usually no interest is given, but it depends on bank logic. We'll set it to 0.
        } else {
            // Matured: full interest
            $interestAmount = $account->payable_amount_at_maturity - $account->principal_amount;
        }

        $netPayable = $account->principal_amount + $interestAmount - $penaltyAmount;

        return response()->json([
            'success' => true,
            'interest_amount' => round($interestAmount, 2),
            'penalty_amount' => round($penaltyAmount, 2),
            'net_payable' => round($netPayable, 2),
            'days_passed' => $daysPassed,
            'is_premature' => $isPremature
        ]);
    }
}
