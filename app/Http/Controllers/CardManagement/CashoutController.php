<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\MembershipCashout;
use App\Models\MembershipCashoutBooth;
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
        $setting = membership_transaction_settings('cashout');
        $booth = MembershipCashoutBooth::where('branch_id', $branch)->first();
        $requests = MembershipCashout::with('customer')->where('branch_id', $branch)->latest('id')->paginate(20);
        $cashHeads = AccountHead::where('payment_mode', 2)->where('nature', 'Debit')->where('status', 1)
            ->where(fn ($q) => $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', $branch))->get();

        return view('card_management.cashout.index', compact('setting', 'booth', 'requests', 'cashHeads'));
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
