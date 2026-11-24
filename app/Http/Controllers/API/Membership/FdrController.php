<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Nominee;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrEncashmentRequest;
use App\Models\ShomriddhiFdr\FdrScheme;
use App\Models\Supplier;
use App\Services\Membership\CashoutService;
use App\Services\Membership\KycEligibilityService;
use App\Services\ShomriddhiFdr\FdrAccountService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FdrController extends Controller
{
    public function __construct(private FdrAccountService $service) {}

    private function owner(Request $r): void
    {
        abort_unless($r->user() instanceof Supplier && $r->user()->type === 'Customer' && $r->user()->status == 1, 403);
    }

    public function index(Request $r)
    {
        $this->owner($r);
        $s = ShomriddhiAccount::where('customer_id', $r->user()->id)->where('status', 'Active')->get();

        return response()->json(['success' => true, 'data' => [
            'kyc_approved' => KycEligibilityService::status($r->user()) === 'Verified',
            'available_balance' => $s->count() === 1 ? CashoutService::money(CashoutService::available($s->first())) : '0.00',
            'schemes' => FdrScheme::where('status', 'Active')->where('visible_on_app', 1)->get(['id', 'name', 'duration', 'interest_rate', 'penalty_rate', 'min_amount', 'max_amount']),
            'frequencies' => ['At Maturity'],
            'nominees' => Nominee::where('customer_id', $r->user()->id)->get(['id', 'name']),
            'accounts' => FdrAccount::with('scheme:id,name')->where('customer_id', $r->user()->id)->latest('id')->get(),
        ]]);
    }

    public function quote(Request $r)
    {
        $this->owner($r);
        $d = $r->validate(['scheme_id' => 'required|integer', 'amount' => 'required|numeric|min:0.01|max:9999999999.99|decimal:0,2']);
        $this->service->member($r->user()->id, true);
        $scheme = FdrScheme::findOrFail($d['scheme_id']);
        $q = $this->service->schemeQuote($scheme, (string) $d['amount']);
        $q['terms_hash'] = $this->service->termsHash($scheme);
        $s = $this->service->savings($r->user()->id);
        $b = $this->service->branch($s->origin_sister_concern_id);
        $q['opening_date'] = $b->business_date;
        $q['maturity_date'] = Carbon::parse($b->business_date)->addMonthsNoOverflow($scheme->duration)->toDateString();

        return response()->json(['success' => true, 'data' => $q]);
    }

    public function open(Request $r)
    {
        $this->owner($r);
        $d = $r->validate(['scheme_id' => 'required|integer', 'amount' => 'required|numeric|min:0.01|max:9999999999.99|decimal:0,2',
            'frequency' => 'required|in:At Maturity', 'auto_renew' => 'required|boolean|declined',
            'pin' => 'required|digits:6', 'idempotency_key' => 'required|string|min:16|max:64', 'terms_hash' => 'required|string|size:64',
            'nominees' => 'nullable|array', 'nominees.*.id' => 'required|integer', 'nominees.*.share_percentage' => 'required|numeric|min:0.01|max:100']);
        $a = $this->service->openFromSavings($r->user(), $d);

        return response()->json(['success' => true, 'data' => $a->load('scheme:id,name')], 201);
    }

    public function show(Request $r, int $id)
    {
        $this->owner($r);
        $a = FdrAccount::with(['scheme:id,name', 'transactions', 'nominees'])->where('customer_id', $r->user()->id)->findOrFail($id);
        $date = SisterConcern::findOrFail($a->origin_sister_concern_id)->business_date;

        return response()->json(['success' => true, 'data' => ['account' => $a, 'settlement' => in_array($a->status, ['Active', 'Matured']) ? $this->service->settlement($a, $date) : null,
            'requests' => FdrEncashmentRequest::where('fdr_account_id', $a->id)->latest('id')->get(),
        ]]);
    }

    public function requestEncash(Request $r, int $id)
    {
        $this->owner($r);
        $d = $r->validate(['pin' => 'required|digits:6', 'idempotency_key' => 'required|string|min:16|max:64']);

        return response()->json(['success' => true, 'data' => $this->service->requestEncash($r->user(), $id, $d['pin'], $d['idempotency_key'])], 201);
    }
}
