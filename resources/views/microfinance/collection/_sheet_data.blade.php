<table class="table table-bordered table-sheet">
    <thead class="bg-light">
        <tr>
            <th width="50">SL</th>
            <th>Member Info</th>
            <th class="text-center" style="background: #e3f2fd;">Loan Installment (ঋণের কিস্তি)</th>
            <th class="text-center" style="background: #e8f5e9;">General Savings (সাধারণ সঞ্চয়)</th>
            <th class="text-center" style="background: #fff8e1;">DPS Installment (ডিপিএস কিস্তি)</th>
            <th class="text-center">Total Collection</th>
        </tr>
    </thead>
    <tbody>
        @foreach($members as $index => $member)
        <tr class="member-row">
            <td>{{ $index + 1 }}</td>
            <td>
                <strong>{{ $member->name }}</strong><br>
                <small class="text-muted">{{ $member->member_no }}</small>
            </td>
            
            <!-- Loan Section -->
            <td style="background: #f1f8ff;">
                @if($member->loans->count() > 0)
                    @foreach($member->loans as $loan)
                        @php $dueInstallment = $loan->installments->first(); @endphp
                        @if($dueInstallment)
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <small class="text-muted mr-2">{{ $loan->product?->code ?? 'N/A' }} (Due: {{ number_format($dueInstallment->total_due, 0) }})</small>
                                <input type="number" 
                                       name="collection[{{ $member->id }}][loans][{{ $dueInstallment->id }}]" 
                                       class="input-collect loan-input" 
                                       data-member="{{ $member->id }}"
                                       value="{{ $dueInstallment->total_due }}">
                            </div>
                        @else
                            <small class="text-muted">No due today</small>
                        @endif
                    @endforeach
                @else
                    <small class="text-muted">No active loan</small>
                @endif
            </td>

            <!-- General Savings Section (product_type == 1) -->
            <td style="background: #f1fbf1;">
                @php 
                    $gsAccounts = $member->savingsAccounts->filter(fn($a) => $a->product->product_type == 1); 
                @endphp
                @if($gsAccounts->count() > 0)
                    @foreach($gsAccounts as $account)
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <small class="text-muted mr-2">{{ $account->product->code }} (Bal: {{ number_format($account->current_balance, 0) }})</small>
                            <input type="number" 
                                   name="collection[{{ $member->id }}][savings][{{ $account->id }}]" 
                                   class="input-collect savings-input" 
                                   data-member="{{ $member->id }}"
                                   value="0">
                        </div>
                    @endforeach
                @else
                    <small class="text-muted">No GS account</small>
                @endif
            </td>

            <!-- DPS Section (product_type == 2) -->
            <td style="background: #fffdf6;">
                @php 
                    $dpsAccounts = $member->savingsAccounts->filter(fn($a) => $a->product->product_type == 2); 
                @endphp
                @if($dpsAccounts->count() > 0)
                    @foreach($dpsAccounts as $account)
                        @php
                            $isPaid = false;
                            $frequency = $account->installment_frequency; // 1=Daily, 2=Weekly, 3=Monthly
                            $carbonDate = \Carbon\Carbon::parse($date);
                            
                            if ($frequency == 1) {
                                $isPaid = $account->transactions->contains(function($t) use ($date) {
                                    return \Carbon\Carbon::parse($t->date)->toDateString() == $date;
                                });
                            } elseif ($frequency == 2) {
                                $startOfWeek = $carbonDate->copy()->startOfWeek();
                                $endOfWeek = $carbonDate->copy()->endOfWeek();
                                $isPaid = $account->transactions->contains(function($t) use ($startOfWeek, $endOfWeek) {
                                    return \Carbon\Carbon::parse($t->date)->between($startOfWeek, $endOfWeek);
                                });
                            } else {
                                $startOfMonth = $carbonDate->copy()->startOfMonth();
                                $endOfMonth = $carbonDate->copy()->endOfMonth();
                                $isPaid = $account->transactions->contains(function($t) use ($startOfMonth, $endOfMonth) {
                                    return \Carbon\Carbon::parse($t->date)->between($startOfMonth, $endOfMonth);
                                });
                            }
                            
                            // Overdue and Penalty calculations
                            $opening = \Carbon\Carbon::parse($account->opening_date)->startOfDay();
                            $target = \Carbon\Carbon::parse($date)->startOfDay();
                            $expected = 0;
                            
                            if ($target->greaterThanOrEqualTo($opening)) {
                                if ($frequency == 1) {
                                    $expected = intval(floor($opening->diffInDays($target))) + 1;
                                } elseif ($frequency == 2) {
                                    $expected = intval(floor($opening->diffInDays($target) / 7)) + 1;
                                } else {
                                    $expected = intval(floor($opening->diffInMonths($target))) + 1;
                                }
                            }
                            
                            $actual = intval($account->total_deposits);
                            $overdue = intval(max(0, $expected - $actual));
                            $fineAmount = floatval($account->product->installment_fine_amount) ?: 0;
                            $accruedPenalty = intval(round($overdue * $fineAmount));
                            
                            $defaultValue = $isPaid ? 0 : intval($account->installment_amount ?? ($account->product->installment_amount ?? 0));
                        @endphp
                        <div class="dps-item {{ !$loop->last ? 'mb-2 pb-2' : '' }}" style="{{ !$loop->last ? 'border-bottom: 1px dashed #e0e0e0;' : '' }}">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <small class="text-dark font-weight-bold" style="font-size: 0.8rem;">
                                    {{ $account->product->code }} (Bal: {{ number_format($account->current_balance, 0) }})
                                    @if($isPaid)
                                        <span class="badge badge-success px-1.5 py-0.5 rounded-pill ml-1" style="font-size: 0.65rem; font-weight: 700;"><i class="fas fa-check"></i> Paid</span>
                                    @endif
                                </small>
                            </div>
                            
                            <!-- Overdue warning badge -->
                            @if(!$isPaid && $overdue > 0 && $fineAmount > 0)
                                <div class="mb-1">
                                    <span class="badge badge-warning px-1.5 py-0.5" style="font-size: 0.68rem; font-weight: 600; color: #856404; background-color: #fff3cd; border-radius: 4px;">
                                        ⚠️ বকেয়া কিস্তি: {{ $overdue }} (জরিমানা: ৳{{ number_format($accruedPenalty, 0) }})
                                    </span>
                                </div>
                            @endif

                            <!-- Split Inputs for Deposit and Late Fine -->
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted mr-1" style="font-size: 0.72rem;">Deposit:</span>
                                    <input type="number" 
                                           name="collection[{{ $member->id }}][savings][{{ $account->id }}]" 
                                           class="input-collect savings-input text-center" 
                                           data-member="{{ $member->id }}"
                                           value="{{ $defaultValue }}"
                                           style="width: 70px; height: 24px; font-size: 0.78rem; border: 1px solid #ced4da; border-radius: 4px; padding: 2px;">
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="text-muted mr-1" style="font-size: 0.72rem;">Fine:</span>
                                    <input type="number" 
                                           name="collection[{{ $member->id }}][penalties][{{ $account->id }}]" 
                                           class="input-collect savings-input text-center text-danger font-weight-bold" 
                                           data-member="{{ $member->id }}"
                                           value="{{ !$isPaid && $accruedPenalty > 0 ? $accruedPenalty : 0 }}"
                                           style="width: 55px; height: 24px; font-size: 0.78rem; border: 1px solid #ced4da; border-radius: 4px; padding: 2px; background-color: #fff8f8;">
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <small class="text-muted">No DPS account</small>
                @endif
            </td>

            <td class="text-center font-weight-bold">
                <span id="total_{{ $member->id }}" class="member-total">0</span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    function calculateMemberTotal(memberId) {
        var total = 0;
        $('.loan-input[data-member="' + memberId + '"], .savings-input[data-member="' + memberId + '"]').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        $('#total_' + memberId).text(total.toLocaleString());
    }

    $(document).ready(function() {
        $('.input-collect').on('input', function() {
            var memberId = $(this).data('member');
            calculateMemberTotal(memberId);
        });

        // Initial calculation
        @foreach($members as $member)
            calculateMemberTotal({{ $member->id }});
        @endforeach
    });
</script>
