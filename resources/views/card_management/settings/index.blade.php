@extends('layouts.app')
@section('title', 'Card Settings')
@section('content')

<div class="mb-3">
    @if(auth()->user()->role === 'Admin' || auth()->user()->can('shomriddhi_advance_transaction_approve'))
    <a class="btn btn-primary" href="{{ route('card-management.cashout.index') }}">Membership Cash-out</a>
    @endif
    @if(auth()->user()->role === 'Admin')
    <a class="btn btn-outline-secondary" href="{{ route('card-management.cashout.settings') }}">Cash-out Settings</a>
    @endif
</div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Card Fee & Distribution Settings</h3>
                </div>

                @if($pendingCardsCount > 0)
                    <div class="alert alert-warning m-3">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Warning!</h5>
                        There are currently <strong>{{ $pendingCardsCount }} pending card(s)</strong> awaiting payment collection. 
                        You cannot change these settings until all pending cards have been approved or rejected.
                    </div>
                @endif

                <form action="{{ route('card-management.settings.update') }}" method="POST" id="cardSettingForm">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="card_fee" class="col-sm-2 col-form-label">Total Card Fee</label>
                            <div class="col-sm-4">
                                <input type="number" step="0.01" class="form-control" id="card_fee" name="card_fee"
                                    value="{{ old('card_fee', $setting->card_fee) }}" required>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mt-3 mb-3 text-info">First Time Card Issue Rules</h5>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="first_card_agent_commission">Employee Commission</label>
                                    <input type="number" step="0.01" class="form-control first_group"
                                        id="first_card_agent_commission" name="first_card_agent_commission"
                                        value="{{ old('first_card_agent_commission', $setting->first_card_agent_commission) }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="first_card_member_bonus">Member Bonus Deposit</label>
                                    <input type="number" step="0.01" class="form-control first_group"
                                        id="first_card_member_bonus" name="first_card_member_bonus"
                                        value="{{ old('first_card_member_bonus', $setting->first_card_member_bonus) }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="first_card_company_income">Company Income</label>
                                    <input type="number" step="0.01" class="form-control first_group"
                                        id="first_card_company_income" name="first_card_company_income"
                                        value="{{ old('first_card_company_income', $setting->first_card_company_income) }}"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <small class="text-muted">Total: <span id="first_total"
                                    class="font-weight-bold">0.00</span></small>
                            <span id="first_error" class="text-danger ml-2" style="display: none;">Must equal Card
                                Fee</span>
                        </div>

                        <hr>
                        <h5 class="mt-3 mb-3 text-info">Replacement/2nd Card Issue Rules</h5>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="replacement_card_agent_commission">Employee Commission</label>
                                    <input type="number" step="0.01" class="form-control rep_group"
                                        id="replacement_card_agent_commission" name="replacement_card_agent_commission"
                                        value="{{ old('replacement_card_agent_commission', $setting->replacement_card_agent_commission) }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="replacement_card_member_bonus">Member Bonus Deposit</label>
                                    <input type="number" step="0.01" class="form-control rep_group"
                                        id="replacement_card_member_bonus" name="replacement_card_member_bonus"
                                        value="{{ old('replacement_card_member_bonus', $setting->replacement_card_member_bonus) }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="replacement_card_company_income">Company Income</label>
                                    <input type="number" step="0.01" class="form-control rep_group"
                                        id="replacement_card_company_income" name="replacement_card_company_income"
                                        value="{{ old('replacement_card_company_income', $setting->replacement_card_company_income) }}"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <small class="text-muted">Total: <span id="rep_total"
                                    class="font-weight-bold">0.00</span></small>
                            <span id="rep_error" class="text-danger ml-2" style="display: none;">Must equal Card Fee</span>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="btn-save" {{ $pendingCardsCount > 0 ? 'disabled' : '' }}>Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            let pendingCount = {{ $pendingCardsCount ?? 0 }};
            if (pendingCount > 0) {
                $('input').prop('readonly', true);
            }

            function calculateTotals() {
                let fee = parseFloat($('#card_fee').val()) || 0;

                // First Issue
                let f1 = parseFloat($('#first_card_agent_commission').val()) || 0;
                let f2 = parseFloat($('#first_card_member_bonus').val()) || 0;
                let f3 = parseFloat($('#first_card_company_income').val()) || 0;
                let fTotal = f1 + f2 + f3;
                $('#first_total').text(fTotal.toFixed(2));

                if (Math.abs(fTotal - fee) > 0.01) {
                    $('#first_error').show();
                    $('#first_total').removeClass('text-success').addClass('text-danger');
                } else {
                    $('#first_error').hide();
                    $('#first_total').removeClass('text-danger').addClass('text-success');
                }

                // Replacement Issue
                let r1 = parseFloat($('#replacement_card_agent_commission').val()) || 0;
                let r2 = parseFloat($('#replacement_card_member_bonus').val()) || 0;
                let r3 = parseFloat($('#replacement_card_company_income').val()) || 0;
                let rTotal = r1 + r2 + r3;
                $('#rep_total').text(rTotal.toFixed(2));

                if (Math.abs(rTotal - fee) > 0.01) {
                    $('#rep_error').show();
                    $('#rep_total').removeClass('text-success').addClass('text-danger');
                } else {
                    $('#rep_error').hide();
                    $('#rep_total').removeClass('text-danger').addClass('text-success');
                }
            }

            $('#card_fee, .first_group, .rep_group').on('input', calculateTotals);
            calculateTotals();

            $('#cardSettingForm').submit(function (e) {
                let fee = parseFloat($('#card_fee').val()) || 0;
                let fTotal = parseFloat($('#first_total').text());
                let rTotal = parseFloat($('#rep_total').text());

                if (Math.abs(fTotal - fee) > 0.01) {
                    e.preventDefault();
                    alert('First Card Distribution total must be exactly equal to Card Fee.');
                    return false;
                }
                if (Math.abs(rTotal - fee) > 0.01) {
                    e.preventDefault();
                    alert('Replacement Card Distribution total must be exactly equal to Card Fee.');
                    return false;
                }
            });
        });
    </script>
@endsection