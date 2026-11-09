@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h3 class="card-title mb-0"><i class="fa fa-university mr-2"></i> {{ $pageTitle }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('microfinance.savings-account.create') }}" class="btn btn-light btn-sm shadow-sm" style="border-radius: 10px;">
                            <i class="fa fa-plus-circle mr-1"></i> Open New Account
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover data-table" style="width: 100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Account No</th>
                                    <th>Member</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Current Balance</th>
                                    <th>Status</th>
                                    <th width="100px">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('microfinance.savings-account.datatable') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'account_no', name: 'account_no'},
                {data: 'member.name', name: 'member.name'},
                {data: 'product.name', name: 'product.name'},
                {data: 'type', name: 'type', defaultContent: '---'},
                {data: 'current_balance', name: 'current_balance'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $(document).on('click', '.btn-approve', function() {
            var id = $(this).data('id');
            var productType = parseInt($(this).data('product-type')) || 1;
            var openingBalance = parseFloat($(this).data('opening-balance')) || 0;
            var openingFee = parseFloat($(this).data('opening-fee')) || 0;
            var admissionFee = parseFloat($(this).data('admission-fee')) || 0;
            var formFee = parseFloat($(this).data('form-fee')) || 0;
            var passbookFee = parseFloat($(this).data('passbook-fee')) || 0;

            var totalPayable = openingBalance + openingFee;
            if (productType === 1) {
                totalPayable += admissionFee + formFee + passbookFee;
            }

            var feesHtml = '';
            if (productType === 1) {
                feesHtml += `
                    <tr class="align-middle">
                        <td class="text-muted align-middle"><i class="fas fa-user-plus mr-1 text-info"></i> ভর্তি ফি (Admission Fee):</td>
                        <td class="text-right" style="width: 130px;">
                            <input type="number" step="any" min="0" id="swal_admission_fee" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${admissionFee}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                        </td>
                    </tr>
                    <tr class="align-middle">
                        <td class="text-muted align-middle"><i class="fas fa-file-alt mr-1 text-info"></i> ফরম ফি (Form Fee):</td>
                        <td class="text-right" style="width: 130px;">
                            <input type="number" step="any" min="0" id="swal_form_fee" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${formFee}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                        </td>
                    </tr>
                    <tr class="align-middle">
                        <td class="text-muted align-middle"><i class="fas fa-book mr-1 text-info"></i> পাসবই ফি (Passbook Fee):</td>
                        <td class="text-right" style="width: 130px;">
                            <input type="number" step="any" min="0" id="swal_passbook_fee" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${passbookFee}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                        </td>
                    </tr>
                `;
            }

            Swal.fire({
                title: 'হিসাব অনুমোদন করবেন?',
                html: `
                    <div class="text-left p-3 border bg-light shadow-sm" style="font-size: 14px; border-radius: 12px;">
                        <p class="mb-3 text-muted text-center" style="font-size: 13px;">অনুমোদনের পূর্বে ক্যাশ কাউন্টার থেকে নিচের হিসাব অনুযায়ী নগদ গ্রহণ নিশ্চিত করুন।</p>
                        <table class="table table-sm table-borderless mb-0">
                            <tr class="align-middle">
                                <td class="text-muted align-middle"><i class="fas fa-wallet mr-1 text-primary"></i> সঞ্চয় জমা (Savings Deposit):</td>
                                <td class="text-right" style="width: 130px;">
                                    <input type="number" step="any" min="0" id="swal_opening_balance" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${openingBalance}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                                </td>
                            </tr>
                            <tr class="align-middle">
                                <td class="text-muted align-middle"><i class="fas fa-receipt mr-1 text-warning"></i> হিসাব খোলার ফি (Opening Fee):</td>
                                <td class="text-right" style="width: 130px;">
                                    <input type="number" step="any" min="0" id="swal_opening_fee" class="form-control form-control-sm text-right swal-calc-input font-weight-bold text-dark" value="${openingFee}" style="border-radius: 6px; height: 30px; padding: 2px 6px;">
                                </td>
                            </tr>
                            ${feesHtml}
                            <tr class="border-top" style="border-top: 2px dashed #dee2e6 !important;">
                                <td class="text-muted pt-2 font-weight-bold align-middle" style="font-size: 15px;"><i class="fas fa-coins mr-1 text-success"></i> মোট আদায়যোগ্য নগদ (Total Cash):</td>
                                <td class="text-right font-weight-bold text-success pt-2 align-middle pr-2" style="font-size: 16px;" id="swal_total_cash">৳ ${totalPayable.toFixed(2)}</td>
                            </tr>
                        </table>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'হ্যাঁ, অনুমোদন করুন!',
                cancelButtonText: 'বাতিল',
                showLoaderOnConfirm: true,
                didOpen: () => {
                    const updateSum = () => {
                        let opBal = parseFloat($('#swal_opening_balance').val()) || 0;
                        let opFee = parseFloat($('#swal_opening_fee').val()) || 0;
                        let admFee = parseFloat($('#swal_admission_fee').val()) || 0;
                        let frmFee = parseFloat($('#swal_form_fee').val()) || 0;
                        let psbFee = parseFloat($('#swal_passbook_fee').val()) || 0;

                        let total = opBal + opFee + admFee + frmFee + psbFee;
                        $('#swal_total_cash').text('৳ ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    };

                    $(document).on('input change', '.swal-calc-input', updateSum);
                },
                preConfirm: () => {
                    let opBal = parseFloat($('#swal_opening_balance').val()) || 0;
                    let opFee = parseFloat($('#swal_opening_fee').val()) || 0;
                    let admFee = parseFloat($('#swal_admission_fee').val()) || 0;
                    let frmFee = parseFloat($('#swal_form_fee').val()) || 0;
                    let psbFee = parseFloat($('#swal_passbook_fee').val()) || 0;

                    return $.ajax({
                        url: "{{ route('microfinance.savings-account.approve', ':id') }}".replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            opening_balance: opBal,
                            opening_fee: opFee,
                            admission_fee: admFee,
                            form_fee: frmFee,
                            passbook_fee: psbFee
                        }
                    }).catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || error.statusText}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value.success) {
                    Swal.fire('Approved!', result.value.message, 'success').then(() => {
                        $('.data-table').DataTable().ajax.reload();
                    });
                } else if (result.isConfirmed) {
                    Swal.fire('Error!', result.value.message, 'error');
                }
            });
        });
    });
</script>
@endsection
