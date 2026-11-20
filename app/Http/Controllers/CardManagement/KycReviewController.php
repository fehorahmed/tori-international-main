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

    public function index(Request $request)
    {
        $this->authorizeReview();
        if (! $request->ajax()) {
            return view('card_management.kyc.index');
        }
        $request->validate(['from_date' => 'nullable|date_format:Y-m-d', 'to_date' => 'nullable|date_format:Y-m-d', 'status' => 'nullable|in:Pending,Verified,Rejected']);
        if ($request->filled('from_date') && $request->filled('to_date') && $request->to_date < $request->from_date) {
            throw \Illuminate\Validation\ValidationException::withMessages(['to_date' => 'To date must be on or after from date.']);
        }
        $query = CustomerKyc::with('customer:id,member_no,origin_sister_concern_id')->whereHas('customer', fn ($q) => $q->where('origin_sister_concern_id', auth()->user()->activeConcernId()))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        return \Yajra\DataTables\Facades\DataTables::eloquent($query)
            ->addColumn('review', fn () => '<button type="button" class="btn btn-sm btn-primary kyc-review">View / Review</button>')
            ->addColumn('details', fn ($submission) => view('card_management.kyc.review', compact('submission'))->render())
            ->rawColumns(['review', 'details'])->toJson();
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
