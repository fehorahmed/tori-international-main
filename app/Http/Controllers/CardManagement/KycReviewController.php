<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\CustomerKyc;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KycReviewController extends Controller
{
    private function authorizeReview(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('customer_edit'), 403);
    }

    private function authorizeRecord(CustomerKyc $kyc): void
    {
        $this->authorizeReview();
        abort_unless($kyc->customer && $kyc->customer->origin_sister_concern_id == auth()->user()->activeConcernId(), 403);
    }

    public function index()
    {
        $this->authorizeReview();
        $submissions = CustomerKyc::with('customer')->whereHas('customer', fn ($query) => $query->where('origin_sister_concern_id', auth()->user()->activeConcernId()))
            ->latest('id')->paginate(20);

        return view('card_management.kyc.index', compact('submissions'));
    }

    public function document(CustomerKyc $kyc, string $kind)
    {
        $this->authorizeRecord($kyc);
        abort_unless(in_array($kind, ['nid_front', 'nid_back', 'selfie'], true), 404);

        return Storage::disk('kyc')->response($kyc->{$kind.'_path'}, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function review(Request $request, CustomerKyc $kyc)
    {
        $this->authorizeRecord($kyc);
        $data = $request->validate([
            'status' => 'required|in:Verified,Rejected',
            'reviewer_message' => 'nullable|required_if:status,Rejected|string|max:2000',
        ]);
        DB::transaction(function () use ($kyc, $data) {
            $customer = Supplier::whereKey($kyc->customer_id)->lockForUpdate()->firstOrFail();
            $kyc->refresh();
            abort_unless($kyc->status === 'Pending', 409, 'This submission has already been reviewed.');
            $kyc->update($data + ['reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
            if ($data['status'] === 'Verified') {
                abort_unless($customer->name === $kyc->submitted_name, 409, 'Member name changed. Reject this submission and request a new one.');
                $customer->update(['nid_number' => $kyc->nid_number]);
            }
        });

        return back()->with('success', 'KYC review saved.');
    }
}
