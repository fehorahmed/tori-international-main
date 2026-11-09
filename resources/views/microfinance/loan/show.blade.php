@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .loan-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .loan-header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 20px 20px 0 0 !important; padding: 25px; }
    .info-box { background: #f8f9fa; border-radius: 15px; padding: 20px; margin-bottom: 20px; height: 100%; }
    .info-label { font-size: 0.85rem; color: #6c757d; margin-bottom: 5px; }
    .info-value { font-size: 1.1rem; font-weight: 700; color: #343a40; }
    .status-badge { padding: 8px 15px; border-radius: 10px; font-weight: 600; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar: Loan Account & Security Summary -->
        <div class="col-md-4">
            <div class="card loan-card shadow-sm border-0" style="border-radius: 20px;">
                <div class="card-header text-white text-center py-4" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border-radius: 20px 20px 0 0;">
                    <span class="status-badge px-3 py-2 rounded font-weight-bold {{ $loan->status == 1 ? 'bg-warning text-dark' : ($loan->status == 2 ? 'bg-info text-white' : ($loan->status == 3 ? 'bg-success text-white' : 'bg-secondary text-white')) }}" style="font-size: 0.8rem; letter-spacing: 0.5px; border-radius: 8px !important;">
                        {{ $loan->status == 1 ? 'আবেদন পেন্ডিং' : ($loan->status == 2 ? 'অনুমোদিত' : ($loan->status == 3 ? 'সচল ঋণ' : 'অন্যান্য')) }}
                    </span>
                    <h4 class="mt-3 mb-0 font-weight-bold" style="letter-spacing: 0.5px;">{{ $loan->loan_no }}</h4>
                </div>
                
                <div class="card-body p-4">
                    <!-- Member Profile Card Block -->
                    <div class="p-3 mb-4 rounded" style="background-color: #f0fdf4; border: 1px dashed #28a745; border-radius: 12px !important;">
                        <div class="small text-success font-weight-bold mb-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;"><i class="fa fa-user-tag mr-1"></i> সদস্যের বিবরণ (Member Profile)</div>
                        <h5 class="font-weight-bold text-dark mb-1" style="font-size: 1.1rem;">{{ $loan->member->name }}</h5>
                        <div class="small text-muted">সদস্য নং: <strong>{{ $loan->member->member_no }}</strong></div>
                        <div class="small text-muted mt-1">সমিতি: <strong class="text-dark">{{ $loan->somiti->name ?? 'N/A' }}</strong></div>
                    </div>

                    <!-- Parameter Section -->
                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #1e293b;"><i class="fa fa-cog mr-1 text-success"></i> ঋণের প্যারামিটারসমূহ</h6>
                    <div class="row mb-3">
                        <div class="col-12 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">লোন স্কিম (Loan Product Scheme)</div>
                            <div class="info-value font-weight-bold text-dark" style="font-size: 0.95rem;">{{ $loan->product->name }} ({{ $loan->product->interest_rate }}%)</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">আবেদনকৃত টাকা (Applied)</div>
                            <div class="info-value font-weight-bold text-dark" style="font-size: 1.1rem; color: #0f172a !important;">৳ {{ number_format($loan->applied_amount, 2) }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">মোট কিস্তি (Installments)</div>
                            <div class="info-value font-weight-bold text-dark" style="font-size: 1.1rem;">{{ $loan->total_installments }} টি</div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">রেয়াতকাল (Grace Period)</div>
                            <div class="info-value font-weight-bold text-dark" style="font-size: 0.95rem;">{{ $loan->grace_period_days ?? 0 }} টি কিস্তি</div>
                        </div>
                    </div>

                    <!-- Fees & Cash Section -->
                    @if($loan->status == 2 || $loan->status == 3)
                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3 mt-2" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #1e293b;"><i class="fa fa-receipt mr-1 text-success"></i> অনুমোদন ও ফিস বিবরণী</h6>
                    <div class="row mb-3">
                        <div class="col-6 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">অনুমোদিত টাকা (Approved)</div>
                            <div class="info-value font-weight-bold text-primary" style="font-size: 1.1rem;">৳ {{ number_format($loan->approved_amount, 2) }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">প্রসেসিং ফি (Processing)</div>
                            <div class="info-value font-weight-bold text-dark" style="font-size: 1rem;">৳ {{ number_format($loan->processing_fee, 2) }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">বীমা প্রিমিয়াম (Insurance)</div>
                            <div class="info-value font-weight-bold text-dark" style="font-size: 1rem;">৳ {{ number_format($loan->insurance_amount, 2) }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">বিতরণকৃত মোট (Net Pay)</div>
                            <div class="info-value font-weight-bold text-success" style="font-size: 1.1rem;">৳ {{ number_format($loan->net_disbursed_amount, 2) }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- Security & Guarantor Section -->
                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3 mt-2" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #1e293b;"><i class="fa fa-shield-alt mr-1 text-success"></i> জামিনদার ও জামানত (Security)</h6>
                    @if($loan->guarantor_name || $loan->guarantor_phone || $loan->guarantor_nid || $loan->guarantor_photo)
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">জামিনদারের নাম</div>
                            <div class="info-value font-weight-bold text-dark" style="font-size: 0.95rem;">{{ $loan->guarantor_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">জামিনদারের মোবাইল</div>
                            <div class="info-value font-weight-bold" style="font-size: 0.95rem;">
                                @if($loan->guarantor_phone)
                                    <a href="tel:{{ $loan->guarantor_phone }}" class="text-success"><i class="fa fa-phone mr-1"></i> {{ $loan->guarantor_phone }}</a>
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="info-label text-muted small" style="font-size: 0.75rem;">জামিনদারের এনআইডি (NID)</div>
                            <div class="info-value font-weight-bold text-dark" style="font-size: 0.95rem;">{{ $loan->guarantor_nid ?? 'N/A' }}</div>
                        </div>
                        @if($loan->guarantor_photo)
                        <div class="col-12 mb-3 text-center">
                            <div class="info-label text-muted small text-left mb-2" style="font-size: 0.75rem;">জামিনদারের ছবি</div>
                            <img src="{{ asset($loan->guarantor_photo) }}" alt="Guarantor Photo" class="img-fluid rounded shadow-sm" style="max-height: 120px; border-radius: 12px !important; width: auto; object-fit: cover; border: 2px solid #dee2e6;">
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="text-muted small mb-3"><i class="fa fa-info-circle mr-1"></i> কোনো জামিনদারের তথ্য দেওয়া হয়নি।</div>
                    @endif

                    <div class="mt-2 mb-3">
                        <div class="info-label text-muted small" style="font-size: 0.75rem;">জামানত বিবরণী (Collateral Details)</div>
                        <div class="info-value p-2 rounded" style="font-size: 0.9rem; font-weight: 500; color: #475569; background-color: #f8fafc; border: 1px solid #f1f5f9; line-height: 1.4;">
                            {{ $loan->collateral_description ?? 'কোনো জামানত রেজিষ্টার করা হয়নি।' }}
                        </div>
                    </div>

                    <!-- Edit Button for Pending Loans -->
                    @if($loan->status == 1)
                    <div class="mt-4">
                        <a href="{{ route('microfinance.loan.edit', $loan->id) }}" class="btn btn-success btn-block font-weight-bold py-3 text-white" style="border-radius: 12px; transition: all 0.3s; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);">
                            <i class="fa fa-edit mr-1"></i> আবেদন পরিবর্তন করুন (Edit)
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Content: Action or Schedule -->
        <div class="col-md-8">
            @if($loan->status == 1)
            <!-- Application Review Panel -->
            <div class="card loan-card shadow-sm border-0" style="border-radius: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-file-invoice mr-2 text-success"></i> ঋণ আবেদন পর্যালোচনা (Application Review)</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Progress Step Indicator -->
                    <div class="d-flex justify-content-around align-items-center mb-4 pb-3 border-bottom">
                        <div class="text-center">
                            <span class="badge badge-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.9rem; background-color: #28a745 !important;"><i class="fa fa-check text-white"></i></span>
                            <div class="small font-weight-bold text-success mt-1">আবেদন তৈরি</div>
                        </div>
                        <div style="flex: 1; height: 2px; background-color: #28a745; margin-bottom: 20px;" class="mx-2"></div>
                        <div class="text-center">
                            <span class="badge badge-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.9rem; color: #fff !important; background-color: #ffc107 !important;">২</span>
                            <div class="small font-weight-bold text-warning mt-1">যাচাই ও সিদ্ধান্ত</div>
                        </div>
                        <div style="flex: 1; height: 2px; background-color: #e2e8f0; margin-bottom: 20px;" class="mx-2"></div>
                        <div class="text-center">
                            <span class="badge badge-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.9rem; background-color: #cbd5e1 !important; color: #64748b !important;">৩</span>
                            <div class="small font-weight-bold text-muted mt-1">অর্থ বিতরণ</div>
                        </div>
                    </div>

                    <!-- Review Details Grid -->
                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #1e293b;"><i class="fa fa-info-circle mr-1 text-success"></i> আবেদনের প্রাথমিক সারসংক্ষেপ</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small d-block">আবেদনকারীর নাম (Member Name)</span>
                            <strong class="text-dark">{{ $loan->member->name }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small d-block">নির্বাচিত ঋণ স্কিম (Loan Scheme)</span>
                            <strong class="text-dark">{{ $loan->product->name }} ({{ $loan->product->interest_rate }}%)</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small d-block">আবেদনকৃত টাকার পরিমাণ (Applied)</span>
                            <strong class="text-success" style="font-size: 1.1rem;">৳ {{ number_format($loan->applied_amount, 2) }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small d-block">মোট কিস্তি সংখ্যা (Installments)</span>
                            <strong class="text-dark">{{ $loan->total_installments }} টি</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small d-block">আবেদনের তারিখ (Application Date)</span>
                            <strong class="text-dark">{{ $loan->application_date ? date('d M, Y', strtotime($loan->application_date)) : 'N/A' }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small d-block">ঋণ গ্রহণের উদ্দেশ্য (Loan Purpose)</span>
                            <strong class="text-dark">{{ $loan->loan_purpose ?? 'উল্লেখ করা হয়নি।' }}</strong>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 p-3 mb-4" style="background-color: #fffbeb; border-radius: 12px; color: #b45309;">
                        <h6 class="font-weight-bold mb-1" style="font-size: 0.9rem;"><i class="fa fa-exclamation-circle mr-1"></i> সিদ্ধান্ত গ্রহণের পূর্বে সতর্কবার্তা:</h6>
                        <span class="small" style="line-height: 1.5; display: inline-block;">
                            ঋণ অনুমোদন করার পূর্বে সদস্যের সাধারণ সঞ্চয় হিসাব (GS Account) সচল আছে কিনা এবং জামিনদার ও জামানতের বিবরণ সঠিক আছে কিনা তা পুনরায় যাচাই করে নিন। অনুমোদিত হলে ঋণ আবেদনটি আর এডিট করা যাবে না।
                        </span>
                    </div>

                    <!-- Direct Action Buttons -->
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <button type="button" class="btn btn-success btn-lg btn-block font-weight-bold py-3 text-white" data-toggle="modal" data-target="#approveModal" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2); border: none;">
                                <i class="fa fa-check-circle mr-1"></i> ঋণ অনুমোদন করুন (Approve)
                            </button>
                        </div>
                        <div class="col-md-6 mb-2">
                            <button type="button" class="btn btn-danger btn-lg btn-block font-weight-bold py-3 text-white btn-reject-show" data-id="{{ $loan->id }}" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2); border: none;">
                                <i class="fa fa-times-circle mr-1"></i> আবেদন বাতিল করুন (Reject)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approve Modal -->
            <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                        <div class="modal-header bg-success text-white py-3" style="border-radius: 20px 20px 0 0;">
                            <h5 class="modal-title font-weight-bold" id="approveModalLabel"><i class="fa fa-check-circle mr-2"></i> ঋণ আবেদন অনুমোদন (Approve Loan)</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('microfinance.loan.approve', $loan->id) }}" method="POST">
                            @csrf
                            <div class="modal-body p-4">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold text-dark">অনুমোদিত ঋণের পরিমাণ (Approved Amount) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light font-weight-bold">৳</span>
                                        </div>
                                        <input type="number" step="0.01" name="approved_amount" class="form-control form-control-lg font-weight-bold text-success" value="{{ $loan->applied_amount }}" required style="font-size: 1.3rem;">
                                    </div>
                                    <small class="text-muted mt-1 d-block">প্রয়োজনে আবেদনকৃত টাকার চেয়ে অনুমোদিত টাকার পরিমাণ পরিবর্তন করতে পারেন।</small>
                                </div>

                                <div class="alert alert-info border-0 p-3 mt-3 mb-0" style="background-color: #eff6ff; border-radius: 12px; color: #1d4ed8;">
                                    <i class="fa fa-info-circle mr-1"></i> এই আনুষ্ঠানিক অনুমোদনের পর ক্যাশিয়ার যেকোনো সময় ঋণটি বিতরণ করতে পারবেন এবং পরিশোধের সময়সূচী স্বয়ংক্রিয়ভাবে তৈরি হবে।
                                </div>
                            </div>
                            <div class="modal-footer bg-light p-3" style="border-radius: 0 0 20px 20px; border-top: none;">
                                <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 10px;">বন্ধ করুন</button>
                                <button type="submit" class="btn btn-success font-weight-bold px-4" style="border-radius: 10px; background-color: #28a745; border: none;">অনুমোদন নিশ্চিত করুন</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if($loan->status == 2)
            <!-- Approved Loan Review & Disburse Trigger Card -->
            <div class="card loan-card shadow-sm border-0" style="border-radius: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-hand-holding-usd mr-2 text-primary"></i> লোন বিতরণ পর্যালোচনা (Disbursement Review)</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Progress Step Indicator -->
                    <div class="d-flex justify-content-around align-items-center mb-4 pb-3 border-bottom">
                        <div class="text-center">
                            <span class="badge badge-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.9rem; background-color: #28a745 !important;"><i class="fa fa-check text-white"></i></span>
                            <div class="small font-weight-bold text-success mt-1">আবেদন তৈরি</div>
                        </div>
                        <div style="flex: 1; height: 2px; background-color: #28a745; margin-bottom: 20px;" class="mx-2"></div>
                        <div class="text-center">
                            <span class="badge badge-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.9rem; background-color: #28a745 !important;"><i class="fa fa-check text-white"></i></span>
                            <div class="small font-weight-bold text-success mt-1">যাচাই ও সিদ্ধান্ত</div>
                        </div>
                        <div style="flex: 1; height: 2px; background-color: #28a745; margin-bottom: 20px;" class="mx-2"></div>
                        <div class="text-center">
                            <span class="badge badge-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.9rem; color: #fff !important; background-color: #ffc107 !important;">৩</span>
                            <div class="small font-weight-bold text-warning mt-1">অর্থ বিতরণ</div>
                        </div>
                    </div>

                    <!-- Financial Summary Grid -->
                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #1e293b;"><i class="fa fa-wallet mr-1 text-primary"></i> ঋণের হিসাব ও ফিস বিবরণী</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small d-block">আবেদনকৃত টাকা (Applied Principal)</span>
                            <strong class="text-dark">৳ {{ number_format($loan->applied_amount, 2) }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small d-block">অনুমোদিত টাকা (Approved Principal)</span>
                            <strong class="text-success" style="font-size: 1.1rem;">৳ {{ number_format($loan->approved_amount, 2) }}</strong>
                        </div>
                        <div class="col-md-4 mb-3">
                            <span class="text-muted small d-block">প্রসেসিং ফি (Processing Fee)</span>
                            <strong class="text-danger">- ৳ {{ number_format($loan->processing_fee, 2) }}</strong>
                        </div>
                        <div class="col-md-4 mb-3">
                            <span class="text-muted small d-block">বীমা প্রিমিয়াম (Insurance Premium)</span>
                            <strong class="text-danger">- ৳ {{ number_format($loan->insurance_amount, 2) }}</strong>
                        </div>
                        <div class="col-md-4 mb-3">
                            <span class="text-muted small d-block">মোট রেয়াতকাল (Grace Period)</span>
                            <strong class="text-dark">{{ $loan->grace_period_days ?? 0 }} কিস্তি</strong>
                        </div>
                    </div>

                    <!-- Highlighted Hand-Cash Balance -->
                    <div class="p-3 mb-4 rounded-lg d-flex justify-content-between align-items-center" style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;">
                        <div>
                            <span class="text-primary small font-weight-bold d-block text-uppercase" style="letter-spacing: 0.5px;">গ্রাহকের হাতে পাওয়ার মোট টাকা (Net Hand Cash)</span>
                            <small class="text-muted font-weight-500">অনুমোদিত লোন হতে প্রসেসিং ও বীমা ফি বাদ দিয়ে নির্ধারিত নেট অংক।</small>
                        </div>
                        <div class="text-right">
                            <h2 class="text-primary font-weight-bold mb-0" style="font-size: 1.75rem;">৳ {{ number_format($loan->approved_amount - $loan->processing_fee - $loan->insurance_amount, 2) }}</h2>
                        </div>
                    </div>

                    <!-- Direct Disburse Trigger Button -->
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary btn-lg btn-block font-weight-bold py-3 text-white" data-toggle="modal" data-target="#disburseModal" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2); border: none; background-color: #007bff;">
                                <i class="fa fa-hand-holding-usd mr-1"></i> ঋণ বিতরণ নিশ্চিত করুন (Disburse Loan)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disburse Modal -->
            <div class="modal fade" id="disburseModal" tabindex="-1" role="dialog" aria-labelledby="disburseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                        <div class="modal-header bg-primary text-white py-3" style="border-radius: 20px 20px 0 0;">
                            <h5 class="modal-title font-weight-bold" id="disburseModalLabel"><i class="fa fa-hand-holding-usd mr-2"></i> ঋণ বিতরণ ও কিস্তি নির্ধারণ</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('microfinance.loan.disburse', $loan->id) }}" method="POST">
                            @csrf
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold text-dark">বিতরণের তারিখ (Disbursement Date) <span class="text-danger">*</span></label>
                                        <input type="date" name="disbursement_date" class="form-control form-control-lg" value="{{ date('Y-m-d') }}" required style="font-size: 1rem; border-radius: 8px;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold text-dark">প্রথম কিস্তির তারিখ (First Installment) <span class="text-danger">*</span></label>
                                        <input type="date" name="first_installment_date" class="form-control form-control-lg" required style="font-size: 1rem; border-radius: 8px;">
                                    </div>
                                    
                                    @if(($loan->grace_period_days ?? 0) > 0)
                                    <div class="col-md-12 mt-2">
                                        <div class="p-3 border rounded shadow-sm" style="background-color: #fffbeb; border: 1px dashed #d97706 !important; color: #b45309; border-radius: 12px !important;">
                                            <h6 class="font-weight-bold mb-1" style="font-size: 0.9rem;"><i class="fa fa-exclamation-triangle mr-2 text-warning"></i> গ্রেস পিরিয়ড সতর্কবার্তা (Grace Period Active)</h6>
                                            <span style="font-size: 0.8rem; line-height: 1.5; display: inline-block;">
                                                এই ঋণে <strong>{{ $loan->grace_period_days }} টি কিস্তি</strong> রেয়াতকাল রয়েছে। প্রথম কিস্তির তারিখটি গ্রেস পিরিয়ড যোগ করে <strong>{{ $loan->grace_period_days + 1 }}টি কিস্তির সময়কাল</strong> পিছিয়ে নির্ধারণ করা হয়েছে। ক্যাশিয়ার চাইলে এটি পরিবর্তন করতে পারবেন।
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="alert alert-info border-0 p-3 mt-3 mb-0" style="background-color: #eff6ff; border-radius: 12px; color: #1d4ed8; font-size: 0.85rem;">
                                    <i class="fa fa-info-circle mr-1"></i> বিতরণ সম্পন্ন হলে স্বয়ংক্রিয়ভাবে কিস্তির সময়সূচী (Repayment Schedule) তৈরি হবে এবং লোনটি সক্রিয় হবে।
                                </div>
                            </div>
                            <div class="modal-footer bg-light p-3" style="border-radius: 0 0 20px 20px; border-top: none;">
                                <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 10px;">বন্ধ করুন</button>
                                <button type="submit" class="btn btn-primary font-weight-bold px-4" style="border-radius: 10px; background-color: #007bff; border: none;">বিতরণ নিশ্চিত করুন</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if($loan->status == 3)
            <!-- Installment Schedule -->
            <div class="card loan-card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-dark mb-0"><i class="fa fa-calendar-alt mr-2"></i> Repayment Schedule</h3>
                    <a href="{{ route('microfinance.loan.repayment_schedule', $loan->id) }}" target="_blank" class="btn btn-outline-primary btn-sm font-weight-bold">
                        <i class="fa fa-print mr-1"></i> Print Schedule
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Due Date</th>
                                    <th>Principal</th>
                                    <th>Interest</th>
                                    <th>Total Due</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @foreach($loan->installments as $installment)
                                <tr>
                                    <td>{{ $installment->installment_no }}</td>
                                    <td>{{ date('d M, Y', strtotime($installment->due_date)) }}</td>
                                    <td>৳ {{ number_format($installment->principal_amount, 0) }}</td>
                                    <td>৳ {{ number_format($installment->interest_amount, 0) }}</td>
                                    <td class="font-weight-bold text-primary">৳ {{ number_format($installment->total_due, 0) }}</td>
                                    <td>
                                        @if($installment->status == 1) <span class="badge badge-warning">Pending</span>
                                        @elseif($installment->status == 2) <span class="badge badge-success">Paid</span>
                                        @else <span class="badge badge-danger">Overdue</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var gracePeriod = parseInt("{{ $loan->grace_period_days ?? 0 }}") || 0;
        var frequency = parseInt("{{ $loan->product->installment_frequency }}") || 2; // 1=Daily, 2=Weekly, 3=Monthly...

        function calculateFirstInstallmentDate() {
            var disburseDateVal = $('input[name="disbursement_date"]').val();
            if (!disburseDateVal) return;

            var date = new Date(disburseDateVal);
            if (isNaN(date.getTime())) return;

            // Shift date by 1 + gracePeriod intervals
            var periodsToAdd = 1 + gracePeriod;

            if (frequency === 1) {
                // Daily: add days
                date.setDate(date.getDate() + periodsToAdd);
            } else if (frequency === 2) {
                // Weekly: add weeks (periods * 7 days)
                date.setDate(date.getDate() + (periodsToAdd * 7));
            } else if (frequency === 3) {
                // Monthly: add months
                date.setMonth(date.getMonth() + periodsToAdd);
            } else if (frequency === 4) {
                // Quarterly: add periods * 3 months
                date.setMonth(date.getMonth() + (periodsToAdd * 3));
            } else if (frequency === 5) {
                // Half-Yearly: add periods * 6 months
                date.setMonth(date.getMonth() + (periodsToAdd * 6));
            } else if (frequency === 6) {
                // At Maturity / One-Time: add periods * 12 months
                date.setFullYear(date.getFullYear() + periodsToAdd);
            }

            // Format to YYYY-MM-DD
            var yyyy = date.getFullYear();
            var mm = String(date.getMonth() + 1).padStart(2, '0');
            var dd = String(date.getDate()).padStart(2, '0');
            
            $('input[name="first_installment_date"]').val(yyyy + '-' + mm + '-' + dd);
        }

        // Calculate on disbursement date change
        $('input[name="disbursement_date"]').on('change keyup', function() {
            calculateFirstInstallmentDate();
        });

        // Trigger on load if form is present
        if ($('input[name="disbursement_date"]').length) {
            calculateFirstInstallmentDate();
        }

        // Handle Reject Button Click inside details page Review Card
        $(document).on('click', '.btn-reject-show', function() {
            var loanId = $(this).data('id');
            var rejectUrl = "{{ route('microfinance.loan.reject', ':id') }}".replace(':id', loanId);
            
            if (confirm('আপনি কি নিশ্চিত যে এই ঋণের আবেদনটি বাতিল (Reject) করতে চান?')) {
                $.ajax({
                    url: rejectUrl,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('সাফল্যের সাথে ঋণ আবেদনটি বাতিল করা হয়েছে।');
                            window.location.reload();
                        } else {
                            alert('ভুল: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        var err = xhr.responseJSON ? xhr.responseJSON.message : 'আবেদন বাতিল করতে সমস্যা হয়েছে।';
                        alert('ভুল: ' + err);
                    }
                });
            }
        });
    });
</script>
@endsection
