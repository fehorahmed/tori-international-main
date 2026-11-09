<?php

namespace App\Http\Controllers\ShomriddhiDps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiDps\DpsAccount;
use App\Models\ShomriddhiDps\DpsScheme;
use App\Models\Supplier;
use App\Enumeration\SupplierType;
use App\Models\Account\AccountHead;
use App\Services\ShomriddhiDps\DpsAccountService;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\Nominee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DpsAccountController extends Controller
{
    public function __construct(private DpsAccountService $service) {}

    public function index()
    {
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')
              ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })->where('payment_mode', '>', 0)->where('status', 1)->get();

        return view('shomriddhi_dps.accounts.index', compact('paymentModes'));
    }

    public function dataTable()
    {
        $query = DpsAccount::with(['customer', 'scheme'])
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('dps_no', function ($row) {
                return '<a href="' . route('shomriddhi-dps.accounts.show', $row->id) . '" class="font-weight-bold text-primary">' . e($row->dps_no) . '</a>';
            })
            ->addColumn('customer', function ($row) {
                return '<a href="#">' . e($row->customer->name ?? '-') . '</a>'
                    . '<br><small class="text-muted">' . e($row->customer->phone ?? '') . '</small>';
            })
            ->addColumn('scheme', function ($row) {
                return e($row->scheme->name ?? '-')
                    . '<br><small class="badge badge-info">' . e($row->scheme->duration_months) . ' Months</small>';
            })
            ->addColumn('installment_amount', fn($row) => '<span class="font-weight-bold text-success">৳ ' . number_format($row->installment_amount, 2) . '</span>')
            ->addColumn('progress', function ($row) {
                $pct = $row->completion_percentage;
                return '<div class="progress" style="height:14px;min-width:90px;">
                    <div class="progress-bar bg-success" style="width:' . $pct . '%"></div>
                </div>
                <small>' . $row->paid_installments . '/' . $row->total_installments . ' paid</small>';
            })
            ->addColumn('maturity_date', fn($row) => Carbon::parse($row->maturity_date)->format('d-M-Y'))
            ->addColumn('next_installment_date', function($row) {
                if ($row->status === 'Active' && $row->next_installment_date) {
                    $date = Carbon::parse($row->next_installment_date);
                    $color = $date->isPast() ? 'text-danger font-weight-bold' : 'text-dark';
                    return '<span class="' . $color . '">' . $date->format('d-M-Y') . '</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('payable_at_maturity', fn($row) => '<span class="font-weight-bold text-primary">৳ ' . number_format($row->payable_at_maturity, 2) . '</span>')
            ->addColumn('status', function ($row) {
                return match($row->status) {
                    'Pending'   => '<span class="badge badge-warning">Pending</span>',
                    'Active'    => '<span class="badge badge-success">Active</span>',
                    'Matured'   => '<span class="badge badge-primary">Matured</span>',
                    'Defaulted' => '<span class="badge badge-danger">Defaulted</span>',
                    default     => '<span class="badge badge-secondary">Closed</span>',
                };
            })
            ->addColumn('action', function ($row) {
                $btn = '';
                if (auth()->user()->can('shomriddhi_dps_account_view')) {
                    $btn .= '<a href="' . route('shomriddhi-dps.accounts.show', $row->id) . '" class="btn btn-primary btn-sm" title="View Details"><i class="fa fa-eye"></i></a>';
                }

                if ($row->status === 'Pending') {
                    if (auth()->user()->can('shomriddhi_dps_account_approve')) {
                        $btn .= ' <a href="' . route('shomriddhi-dps.accounts.show', $row->id) . '" class="btn btn-success btn-sm" title="Approve"><i class="fa fa-check"></i></a>';
                    }
                    if (auth()->user()->can('shomriddhi_dps_account_edit')) {
                        $btn .= ' <a href="' . route('shomriddhi-dps.accounts.edit', $row->id) . '" class="btn btn-info btn-sm" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (auth()->user()->can('shomriddhi_dps_account_delete')) {
                        $btn .= ' <a role="button" data-id="' . $row->id . '" class="btn btn-danger btn-sm btn-delete" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                }

                if (in_array($row->status, ['Active', 'Matured'])) {
                    if (auth()->user()->can('shomriddhi_dps_account_pay_installment')) {
                        $btn .= ' <a role="button" data-id="' . $row->id . '" data-amount="' . $row->installment_amount . '" class="btn btn-info btn-sm btn-pay-installment" title="Pay Installment"><i class="fa fa-money-bill-wave"></i></a>';
                    }
                    if (auth()->user()->can('shomriddhi_dps_account_encash')) {
                        $btn .= ' <a role="button" data-id="' . $row->id . '" class="btn btn-warning btn-sm btn-encash" title="Encash"><i class="fa fa-hand-holding-usd"></i></a>';
                    }
                }

                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['dps_no', 'customer', 'scheme', 'installment_amount', 'progress', 'next_installment_date', 'payable_at_maturity', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $customers = Supplier::select('id', 'name', 'member_no')
            ->where('type', SupplierType::$CUSTOMER)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereHas('shomriddhiAccount')
            ->orderBy('name')->get();

        $schemes  = DpsScheme::where('status', 'Active')->get();
        return view('shomriddhi_dps.accounts.create', compact('customers', 'schemes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'  => 'required|exists:suppliers,id',
            'scheme_id'    => 'required|exists:dps_schemes,id',
            'opening_date' => 'required|date',
            'nominees'     => 'nullable|array',
            'nominees.*.id'               => 'required_with:nominees|exists:nominees,id',
            'nominees.*.share_percentage' => 'required_with:nominees|numeric|min:1|max:100',
        ]);

        // Check if the customer has an active Shomriddhi Advance (Savings) account
        $hasSavings = ShomriddhiAccount::where('customer_id', $request->customer_id)
            ->where('status', 'Active')
            ->exists();

        if (!$hasSavings) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['customer_id' => 'This customer does not have an Active Shomriddhi Advance (Savings) account. DPS cannot be opened.']);
        }

        // Validate nominee total
        if ($request->filled('nominees')) {
            $total = collect($request->nominees)->sum('share_percentage');
            if ($total != 100) {
                return back()->withInput()->withErrors(['nominees' => "Total nominee share must be 100%. Currently: {$total}%."]);
            }
        }

        try {
            DB::beginTransaction();

            $scheme      = DpsScheme::findOrFail($request->scheme_id);
            
            try {
                $openingDate = Carbon::createFromFormat('d-m-Y', $request->opening_date);
            } catch (\Exception $e) {
                $openingDate = Carbon::parse($request->opening_date);
            }
            
            $maturityDate = $openingDate->copy()->addMonths($scheme->duration_months);

            // Pre-calculate maturity amount using Recurring Deposit formula:
            // M = P × [((1+r)^n - 1) / r] × (1+r)
            $p = $scheme->installment_amount;
            $annualRate = $scheme->interest_rate / 100;
            
            if ($scheme->installment_type === 'Daily') {
                $n = $scheme->duration_months * 30; // Approx 30 days per month
                $r = $annualRate / 365;
                $nextInstallmentDate = $openingDate->copy()->addDay();
            } elseif ($scheme->installment_type === 'Weekly') {
                $n = $scheme->duration_months * 4; // Approx 4 weeks per month
                $r = $annualRate / 52;
                $nextInstallmentDate = $openingDate->copy()->addWeek();
            } else {
                $n = $scheme->duration_months;
                $r = $annualRate / 12;
                $nextInstallmentDate = $openingDate->copy()->addMonth();
            }

            $payableAtMaturity = $r > 0
                ? round($p * (((pow(1 + $r, $n) - 1) / $r) * (1 + $r)), 2)
                : $p * $n;

            $shomriddhiAccount = ShomriddhiAccount::where('customer_id', $request->customer_id)->first();
            $savingsAcNo = $shomriddhiAccount ? $shomriddhiAccount->account_number : '000000000';
            $customerDpsCount = DpsAccount::where('customer_id', $request->customer_id)->count();
            $dpsNo = $savingsAcNo . '-D' . str_pad($customerDpsCount + 1, 2, '0', STR_PAD_LEFT);

            $account = DpsAccount::create([
                'dps_no'                   => $dpsNo,
                'customer_id'              => $request->customer_id,
                'scheme_id'                => $request->scheme_id,
                'origin_sister_concern_id' => auth()->user()->activeConcernId(),
                'installment_type'         => $scheme->installment_type,
                'installment_amount'       => $scheme->installment_amount,
                'total_installments'       => $n,
                'interest_rate'            => $scheme->interest_rate,
                'paid_installments'        => 0,
                'missed_installments'      => 0,
                'total_deposited'          => 0,
                'total_penalty_charged'    => 0,
                'opening_date'             => $openingDate->format('Y-m-d'),
                'maturity_date'            => $maturityDate->format('Y-m-d'),
                'next_installment_date'    => $nextInstallmentDate->format('Y-m-d'),
                'payable_at_maturity'      => $payableAtMaturity,
                'auto_renew'               => $request->boolean('auto_renew'),
                'status'                   => 'Pending',
                'notes'                    => $request->notes,
                'created_by'               => auth()->id(),
            ]);

            // Save nominees using Eloquent relationship
            if ($request->filled('nominees')) {
                foreach ($request->nominees as $nominee) {
                    $account->nominees()->create([
                        'nominee_id'       => $nominee['id'],
                        'share_percentage' => $nominee['share_percentage'] ?? 100,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('shomriddhi-dps.accounts.index')->with('success', 'DPS Account created successfully and is pending approval.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create DPS account: ' . $e->getMessage());
        }
    }

    public function edit(DpsAccount $account)
    {
        if ($account->status !== 'Pending') {
            return redirect()->route('shomriddhi-dps.accounts.index')->with('error', 'Only pending accounts can be edited.');
        }

        $customers = Supplier::select('id', 'name', 'member_no')
            ->where('type', SupplierType::$CUSTOMER)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereHas('shomriddhiAccount')
            ->orderBy('name')->get();
        $schemes   = DpsScheme::where('status', 'Active')->orWhere('id', $account->scheme_id)->get();
        return view('shomriddhi_dps.accounts.edit', compact('account', 'customers', 'schemes'));
    }

    public function update(Request $request, DpsAccount $account)
    {
        if ($account->status !== 'Pending') {
            return redirect()->route('shomriddhi-dps.accounts.index')->with('error', 'Only pending accounts can be edited.');
        }

        $request->validate([
            'customer_id'  => 'required|exists:suppliers,id',
            'scheme_id'    => 'required|exists:dps_schemes,id',
            'opening_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();
            
            $scheme       = DpsScheme::findOrFail($request->scheme_id);
            
            try {
                $openingDate = Carbon::createFromFormat('d-m-Y', $request->opening_date);
            } catch (\Exception $e) {
                $openingDate = Carbon::parse($request->opening_date);
            }
            
            $maturityDate = $openingDate->copy()->addMonths($scheme->duration_months);

            $p = $scheme->installment_amount;
            $annualRate = $scheme->interest_rate / 100;
            
            if ($scheme->installment_type === 'Daily') {
                $n = $scheme->duration_months * 30;
                $r = $annualRate / 365;
                $nextInstallmentDate = $openingDate->copy()->addDay();
            } elseif ($scheme->installment_type === 'Weekly') {
                $n = $scheme->duration_months * 4;
                $r = $annualRate / 52;
                $nextInstallmentDate = $openingDate->copy()->addWeek();
            } else {
                $n = $scheme->duration_months;
                $r = $annualRate / 12;
                $nextInstallmentDate = $openingDate->copy()->addMonth();
            }

            $payableAtMaturity = $r > 0
                ? round($p * (((pow(1 + $r, $n) - 1) / $r) * (1 + $r)), 2)
                : $p * $n;

            $account->update([
                'customer_id'           => $request->customer_id,
                'scheme_id'             => $request->scheme_id,
                'installment_type'      => $scheme->installment_type,
                'installment_amount'    => $scheme->installment_amount,
                'total_installments'    => $n,
                'interest_rate'         => $scheme->interest_rate,
                'opening_date'          => $openingDate->format('Y-m-d'),
                'maturity_date'         => $maturityDate->format('Y-m-d'),
                'next_installment_date' => $nextInstallmentDate->format('Y-m-d'),
                'payable_at_maturity'   => $payableAtMaturity,
                'auto_renew'            => $request->boolean('auto_renew'),
                'notes'                 => $request->notes,
            ]);
            
            DB::commit();
            return redirect()->route('shomriddhi-dps.accounts.index')->with('success', 'DPS Account updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update DPS account: ' . $e->getMessage());
        }
    }

    public function show(DpsAccount $account)
    {
        $account->load(['customer.division', 'customer.district', 'customer.upazila', 'customer.union', 'customer.pourashava', 'customer.village', 'scheme', 'nominees.nominee', 'transactions.paymentHead']);
        
        $paymentModes = [];
        if ($account->status === 'Pending') {
            $paymentModes = AccountHead::where(function ($q) {
                $q->whereNull('sister_concern_id')
                  ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
            })->where('payment_mode', '>', 0)->where('status', 1)->get();
        }

        return view('shomriddhi_dps.accounts.show', compact('account', 'paymentModes'));
    }

    public function destroy(DpsAccount $account)
    {
        try {
            if ($account->status !== 'Pending') {
                return response()->json(['success' => false, 'message' => 'Only pending accounts can be deleted.']);
            }
            $account->delete();
            return response()->json(['success' => true, 'message' => 'DPS Account deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function approve(Request $request, DpsAccount $account)
    {
        if ($account->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Account is already approved or closed.']);
        }
        $request->validate(['payment_mode' => 'required|exists:account_heads,id']);

        $result = $this->service->approve($account, (int) $request->payment_mode);
        return response()->json($result);
    }

    public function installmentData(DpsAccount $account)
    {
        return response()->json([
            'success'               => true,
            'dps_no'                => $account->dps_no,
            'installment_amount'    => $account->installment_amount,
            'missed_installments'   => $account->missed_installments,
            'total_penalty_charged' => $account->total_penalty_charged,
            'total_installments'    => $account->total_installments,
            'paid_installments'     => $account->paid_installments,
        ]);
    }

    public function encashData(DpsAccount $account)
    {
        if (!in_array($account->status, ['Active', 'Matured'])) {
            return response()->json(['success' => false, 'message' => 'Account is not active or matured.']);
        }

        $data = $this->service->calculateEncashmentData($account->load('scheme'));
        return response()->json(array_merge(['success' => true], $data));
    }

    public function encash(Request $request, DpsAccount $account)
    {
        if (!in_array($account->status, ['Active', 'Matured'])) {
            return response()->json(['success' => false, 'message' => 'Account cannot be encashed.']);
        }

        $request->validate([
            'payment_mode'    => 'required|exists:account_heads,id',
            'interest_amount' => 'required|numeric|min:0',
            'penalty_amount'  => 'required|numeric|min:0',
        ]);

        $result = $this->service->encash($account, $request->all());
        return response()->json($result);
    }
}
