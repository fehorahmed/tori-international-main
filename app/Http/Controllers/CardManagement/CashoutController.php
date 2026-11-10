<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\MembershipCashout;
use App\Models\MembershipCashoutBooth;
use App\Models\MembershipCashoutSetting;
use App\Services\Membership\CashoutService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CashoutController extends Controller
{
    private function canView(): void
    {
        abort_unless(auth()->user()->role === 'Admin' || auth()->user()->hasPermissionTo('shomriddhi_advance_transaction_approve'), 403);
    }

    public function index()
    {
        $this->canView();
        $branch = auth()->user()->activeConcernId();
        $setting = MembershipCashoutSetting::current();
        $booth = MembershipCashoutBooth::where('branch_id', $branch)->first();
        $requests = MembershipCashout::with('customer')->where('branch_id', $branch)->latest('id')->paginate(20);
        $cashHeads = AccountHead::where('payment_mode', 2)->where('nature', 'Debit')->where('status', 1)
            ->where(fn ($q) => $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', $branch))->get();

        return view('card_management.cashout.index', compact('setting', 'booth', 'requests', 'cashHeads'));
    }

    public function settingsPage()
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        $setting = MembershipCashoutSetting::current();
        $incomeHeads = AccountHead::with('accountGroup')->where('nature', 'Credit')->whereNull('sister_concern_id')
            ->where('status', 1)->get()->filter(fn ($head) => $head->accountGroup?->top_reserved_id == 4);

        return view('card_management.cashout.settings', compact('setting', 'incomeHeads'));
    }

    public function settings(Request $request)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        $data = $request->validate(['maximum_amount' => 'required|numeric|decimal:0,2|min:1|max:10000000',
            'charge_type' => 'required|in:fixed,percentage', 'charge_rate' => 'required|numeric|decimal:0,2|min:0|max:10000000',
            'hold_minutes' => 'required|integer|min:1|max:60', 'income_head_id' => 'nullable|integer']);
        if ($data['charge_type'] === 'percentage' && $data['charge_rate'] > 100) {
            CashoutService::fail('Percentage cannot exceed 100.');
        }
        if ($data['charge_rate'] > 0) {
            $head = AccountHead::find($data['income_head_id'] ?? null);
            if (! $head || $head->status != 1 || $head->nature !== 'Credit' || $head->sister_concern_id || $head->accountGroup?->top_reserved_id != 4) {
                CashoutService::fail('Select an active global income ledger head for cash-out charges.');
            }
        }
        MembershipCashoutSetting::updateOrCreate(['id' => 1], $data + ['enabled' => $request->boolean('enabled')]);

        return back()->with('success', 'Cash-out settings saved.');
    }

    public function booth(Request $request)
    {
        $this->canView();
        $request->validate(['cash_head_id' => 'required|integer']);
        $branch = auth()->user()->activeConcernId();
        $head = AccountHead::findOrFail($request->cash_head_id);
        abort_unless($head->status == 1 && $head->payment_mode == 2 && $head->nature === 'Debit'
            && (! $head->sister_concern_id || $head->sister_concern_id == $branch), 422);
        $booth = MembershipCashoutBooth::firstOrNew(['branch_id' => $branch]);
        if (! $booth->exists || $request->boolean('rotate')) {
            $booth->qr_token = Str::random(48);
        }
        $booth->fill(['cash_head_id' => $head->id, 'enabled' => $request->boolean('enabled')])->save();

        return back()->with('success', 'Branch cash-out counter saved.');
    }

    public function qr()
    {
        $this->canView();
        $booth = MembershipCashoutBooth::where('branch_id', auth()->user()->activeConcernId())->firstOrFail();
        $writer = new Writer(new ImageRenderer(new RendererStyle(320), new SvgImageBackEnd));

        return response($writer->writeString('tori:cashout:'.$booth->qr_token), 200, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'private, no-store']);
    }

    public function change(Request $request, MembershipCashout $cashout, string $action, CashoutService $service)
    {
        abort_unless(in_array($action, ['accept', 'complete', 'reject'], true), 404);
        if ($action === 'complete') {
            $request->validate(['cash_payment_confirmed' => 'accepted']);
        }
        if ($action === 'reject') {
            $request->validate(['reason' => 'required|string|max:1000']);
        }
        $service->change($cashout, $action, auth()->user(), $request->reason);

        return back()->with('success', 'Cash-out request updated.');
    }
}
