<?php

namespace App\Http\Controllers\FixedAsset;

use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\FixedAsset\FixedAsset;
use App\Models\FixedAsset\FixedAssetDisposal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisposalController extends Controller
{
    // ── Sell Form ────────────────────────────────────────────────────

    public function sellForm(FixedAsset $fixedAsset)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_sell'), 403);
        abort_if($fixedAsset->status !== 'active', 403, 'Only active assets can be sold.');

        $paymentHeads = AccountHead::where('payment_mode', '>', 0)
            ->where(function ($q) {
                $q->whereNull('sister_concern_id')
                  ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
            })
            ->orderBy('name')->get();

        return view('fixed_asset.disposal.sell', compact('fixedAsset', 'paymentHeads'));
    }

    // ── Sell Store ───────────────────────────────────────────────────

    public function sellStore(Request $request, FixedAsset $fixedAsset)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_sell'), 403);
        abort_if($fixedAsset->status !== 'active', 403);

        $request->validate([
            'disposal_date'           => 'required|date',
            'sale_amount'             => 'required|numeric|min:0',
            'payment_account_head_id' => 'required|exists:account_heads,id',
            'notes'                   => 'nullable|string|max:500',
        ]);

        $saleAmount   = (float) $request->sale_amount;
        $bookValue    = (float) $fixedAsset->book_value;
        $gainLoss     = $saleAmount - $bookValue;        // positive = gain, negative = loss
        $accDep       = (float) $fixedAsset->accumulated_depreciation;
        $originalCost = (float) $fixedAsset->total;
        $today        = Carbon::parse($request->disposal_date)->format('Y-m-d');
        $scId         = auth()->user()->activeConcernId();
        $user         = auth()->user();

        // Get Gain/Loss on Asset Sale account heads
        $gainHeadId = reservedAccountHeadId(ReservedAccountHead::GAIN_ON_ASSET_SALE);
        $lossHeadId = reservedAccountHeadId(ReservedAccountHead::LOSS_ON_ASSET_SALE);

        DB::beginTransaction();
        try {
            // ── Journal Voucher ─────────────────────────────────────────
            $voucherNo        = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = Voucher::create([
                'sister_concern_id'               => $scId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'amount'                          => $saleAmount,
                'date'                            => $today,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $fixedAsset->account_head_id,
                'notes'                           => 'Asset Sale: ' . $fixedAsset->asset_code . ' - ' . $fixedAsset->name,
                'user_id'                         => $user->id,
            ]);

            $txData = [
                'sister_concern_id'               => $scId,
                'voucher_id'                      => $voucher->id,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'date'                            => $today,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $fixedAsset->account_head_id,
                'user_id'                         => $user->id,
            ];

            // Dr. Cash/Bank (sale proceeds)
            if ($saleAmount > 0) {
                Transaction::create(array_merge($txData, [
                    'account_head_id'  => $request->payment_account_head_id,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount'           => $saleAmount,
                    'notes'            => 'Sale proceeds: ' . $fixedAsset->name,
                ]));
            }

            // Dr. Accumulated Depreciation (clear it)
            if ($accDep > 0) {
                $accDepHeadId = $fixedAsset->category->accumulated_dep_head_id
                    ?? reservedAccountHeadId(ReservedAccountHead::ACCUMULATED_DEPRECIATION);
                Transaction::create(array_merge($txData, [
                    'account_head_id'  => $accDepHeadId,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount'           => $accDep,
                    'notes'            => 'Clear accumulated depreciation: ' . $fixedAsset->name,
                ]));
            }

            // Dr. Loss on Asset Sale (if loss)
            if ($gainLoss < 0 && $lossHeadId) {
                Transaction::create(array_merge($txData, [
                    'account_head_id'  => $lossHeadId,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount'           => abs($gainLoss),
                    'notes'            => 'Loss on sale: ' . $fixedAsset->name,
                ]));
            }

            // Cr. Fixed Asset (original cost)
            Transaction::create(array_merge($txData, [
                'account_head_id'  => $fixedAsset->account_head_id,
                'transaction_type' => TransactionType::$CREDIT,
                'amount'           => $originalCost,
                'notes'            => 'Remove asset from books: ' . $fixedAsset->name,
            ]));

            // Cr. Gain on Asset Sale (if gain)
            if ($gainLoss > 0 && $gainHeadId) {
                Transaction::create(array_merge($txData, [
                    'account_head_id'  => $gainHeadId,
                    'transaction_type' => TransactionType::$CREDIT,
                    'amount'           => $gainLoss,
                    'notes'            => 'Gain on sale: ' . $fixedAsset->name,
                ]));
            }

            // ── Disposal Record ─────────────────────────────────────────
            FixedAssetDisposal::create([
                'fixed_asset_id'          => $fixedAsset->id,
                'sister_concern_id'       => $scId,
                'disposal_date'           => $today,
                'disposal_type'           => 'sold',
                'sale_amount'             => $saleAmount,
                'book_value_at_disposal'  => $bookValue,
                'gain_loss_amount'        => $gainLoss,
                'payment_account_head_id' => $request->payment_account_head_id,
                'voucher_id'              => $voucher->id,
                'notes'                   => $request->notes,
                'user_id'                 => $user->id,
            ]);

            // ── Mark asset as sold ──────────────────────────────────────
            $fixedAsset->update(['status' => 'sold']);

            DB::commit();
            return redirect()->route('fixed-assets.index')
                ->with('success', 'Asset sold successfully. JV: ' . $voucherNo . ($gainLoss >= 0 ? ' | Gain: ৳' . number_format($gainLoss, 2) : ' | Loss: ৳' . number_format(abs($gainLoss), 2)));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ── Scrap Form ───────────────────────────────────────────────────

    public function scrapForm(FixedAsset $fixedAsset)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_scrap'), 403);
        abort_if($fixedAsset->status !== 'active', 403, 'Only active assets can be scrapped.');
        return view('fixed_asset.disposal.scrap', compact('fixedAsset'));
    }

    // ── Scrap Store ──────────────────────────────────────────────────

    public function scrapStore(Request $request, FixedAsset $fixedAsset)
    {
        abort_if(!auth()->user()->hasPermissionTo('fixed_asset_scrap'), 403);
        abort_if($fixedAsset->status !== 'active', 403);

        $request->validate([
            'disposal_date' => 'required|date',
            'notes'         => 'nullable|string|max:500',
        ]);

        $bookValue    = (float) $fixedAsset->book_value;
        $accDep       = (float) $fixedAsset->accumulated_depreciation;
        $originalCost = (float) $fixedAsset->total;
        $today        = Carbon::parse($request->disposal_date)->format('Y-m-d');
        $scId         = auth()->user()->activeConcernId();
        $user         = auth()->user();
        $lossHeadId   = reservedAccountHeadId(ReservedAccountHead::LOSS_ON_SCRAP);

        DB::beginTransaction();
        try {
            $voucherNo        = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = Voucher::create([
                'sister_concern_id'               => $scId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'amount'                          => $originalCost,
                'date'                            => $today,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $fixedAsset->account_head_id,
                'notes'                           => 'Asset Scrapped: ' . $fixedAsset->asset_code . ' - ' . $fixedAsset->name,
                'user_id'                         => $user->id,
            ]);

            $txData = [
                'sister_concern_id'               => $scId,
                'voucher_id'                      => $voucher->id,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'date'                            => $today,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $fixedAsset->account_head_id,
                'user_id'                         => $user->id,
            ];

            // Dr. Accumulated Depreciation
            if ($accDep > 0) {
                $accDepHeadId = $fixedAsset->category->accumulated_dep_head_id
                    ?? reservedAccountHeadId(ReservedAccountHead::ACCUMULATED_DEPRECIATION);
                Transaction::create(array_merge($txData, [
                    'account_head_id'  => $accDepHeadId,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount'           => $accDep,
                    'notes'            => 'Clear accumulated depreciation: ' . $fixedAsset->name,
                ]));
            }

            // Dr. Loss on Scrap (remaining book value)
            if ($bookValue > 0 && $lossHeadId) {
                Transaction::create(array_merge($txData, [
                    'account_head_id'  => $lossHeadId,
                    'transaction_type' => TransactionType::$DEBIT,
                    'amount'           => $bookValue,
                    'notes'            => 'Loss on scrap: ' . $fixedAsset->name,
                ]));
            }

            // Cr. Fixed Asset (original cost)
            Transaction::create(array_merge($txData, [
                'account_head_id'  => $fixedAsset->account_head_id,
                'transaction_type' => TransactionType::$CREDIT,
                'amount'           => $originalCost,
                'notes'            => 'Remove scrapped asset from books: ' . $fixedAsset->name,
            ]));

            FixedAssetDisposal::create([
                'fixed_asset_id'         => $fixedAsset->id,
                'sister_concern_id'      => $scId,
                'disposal_date'          => $today,
                'disposal_type'          => 'scrapped',
                'sale_amount'            => 0,
                'book_value_at_disposal' => $bookValue,
                'gain_loss_amount'       => -$bookValue,
                'voucher_id'             => $voucher->id,
                'notes'                  => $request->notes,
                'user_id'                => $user->id,
            ]);

            $fixedAsset->update(['status' => 'scrapped']);

            DB::commit();
            return redirect()->route('fixed-assets.index')
                ->with('success', 'Asset scrapped successfully. Loss: ৳' . number_format($bookValue, 2) . ' | JV: ' . $voucherNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
