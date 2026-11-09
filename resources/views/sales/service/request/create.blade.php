@extends('layouts.app')
@section('title','Service Request Create')
@section('style')
    <style>
        .product-card {
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fc;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title">New Service Request</h3>
                </div>
                <form action="{{ route('service.request.store') }}" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sale_type">Sale Type <span class="text-danger">*</span></label>
                                    <select name="sale_type" id="sale_type" class="form-control select2" {{ isset($preselectedOrder) ? 'disabled' : '' }}>
                                        <option value="">Select Type</option>
                                        <option value="{{ \App\Enumeration\SaleType::$GENERAL }}" {{ (isset($preselectedSaleType) && $preselectedSaleType == \App\Enumeration\SaleType::$GENERAL) ? 'selected' : '' }}>General Sale</option>
                                        <option value="{{ \App\Enumeration\SaleType::$EMI }}" {{ (isset($preselectedSaleType) && $preselectedSaleType == \App\Enumeration\SaleType::$EMI) ? 'selected' : '' }}>EMI Sale</option>
                                        <option value="{{ \App\Enumeration\SaleType::$PRE_ORDER }}" {{ (isset($preselectedSaleType) && $preselectedSaleType == \App\Enumeration\SaleType::$PRE_ORDER) ? 'selected' : '' }}>Pre-Order</option>
                                        <option value="{{ \App\Enumeration\SaleType::$CORPORATE }}" {{ (isset($preselectedSaleType) && $preselectedSaleType == \App\Enumeration\SaleType::$CORPORATE) ? 'selected' : '' }}>Corporate Sale</option>
                                        <option value="{{ \App\Enumeration\SaleType::$DEALER }}" {{ (isset($preselectedSaleType) && $preselectedSaleType == \App\Enumeration\SaleType::$DEALER) ? 'selected' : '' }}>Dealer Sale</option>
                                    </select>
                                    @if(isset($preselectedOrder))
                                        <input type="hidden" name="sale_type" value="{{ $preselectedSaleType }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sale_order">Sales Order <span class="text-danger">*</span></label>
                                    <select name="sale_order" id="sale_order" class="form-control select2" {{ isset($preselectedOrder) ? 'disabled' : '' }}>
                                        @if(isset($preselectedOrder))
                                            <option value="{{ $preselectedOrder->id }}" selected>{{ $preselectedOrder->order_no }}</option>
                                        @else
                                            <option value="">Search Order No</option>
                                        @endif
                                    </select>
                                    @if(isset($preselectedOrder))
                                        <input type="hidden" name="sale_order" value="{{ $preselectedOrder->id }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="technician">Assign Technician <span class="text-danger">*</span></label>
                                    <select name="technician" id="technician" class="form-control select2">
                                        <option value="">Select Technician</option>
                                        @foreach($technicians as $tech)
                                            <option value="{{ $tech->id }}">{{ $tech->name }} - {{ $tech->mobile_no }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" name="date" value="{{ date('Y-m-d') }}" class="form-control date-picker" readonly>
                                </div>
                            </div>
                        </div>

                        <div id="customer_info_section" style="display: none;">
                            <div class="alert alert-info">
                                <strong>Customer:</strong> <span id="customer_name"></span> | 
                                <strong>Mobile:</strong> <span id="customer_mobile"></span>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5>Products for Service (With Active Warranty)</h5>
                                <hr>
                                <div id="product_list_container">
                                    <div class="text-center text-muted">Please select a Sales Order first.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="Additional notes..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info">Submit Request</button>
                        <a href="{{ route('service.request.index') }}" class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2();

            $('#sale_order').select2({
                ajax: {
                    url: "{{ route('service.request.get_orders') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term,
                            sale_type: $('#sale_type').val()
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                placeholder: 'Search Order No',
                minimumInputLength: 1,
            });

            $('#sale_order').on('change', function() {
                let orderId = $(this).val();
                if (orderId) {
                    preloaderToggle(true);
                    $.ajax({
                        method: "GET",
                        url: "{{ route('service.request.get_order_products') }}",
                        data: { order_id: orderId }
                    }).done(function( response ) {
                        preloaderToggle(false);
                        if (response.success) {
                            $('#customer_info_section').show();
                            $('#customer_name').text(response.customer.name);
                            $('#customer_mobile').text(response.customer.mobile_no);

                            let productHtml = '';
                            if (response.items.length > 0) {
                                $.each(response.items, function(index, item) {
                                    productHtml += `
                                        <div class="product-card">
                                            <div class="row">
                                                <div class="col-md-1">
                                                    <div class="icheck-info d-inline">
                                                        <input type="checkbox" name="product_id[]" value="${item.product_id}" id="prod_${item.id}" checked>
                                                        <label for="prod_${item.id}"></label>
                                                    </div>
                                                    <input type="hidden" name="sale_order_item_id[]" value="${item.id}">
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>${item.product_name}</strong><br>
                                                    <small class="text-muted">Code: ${item.product_code}</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="badge badge-success">Warranty Expires: ${item.warranty_expiry}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="cause[]" class="form-control" placeholder="Describe the problem (Cause)" required>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                });
                            } else {
                                productHtml = '<div class="alert alert-warning">No products with active warranty found in this order.</div>';
                            }
                            $('#product_list_container').html(productHtml);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });

            $('#sale_type').on('change', function() {
                $('#sale_order').val(null).trigger('change');
                $('#product_list_container').html('<div class="text-center text-muted">Please select a Sales Order first.</div>');
                $('#customer_info_section').hide();
            });

            @if(isset($preselectedOrder))
                $('#sale_order').trigger('change');
            @endif
        });
    </script>
@endsection
