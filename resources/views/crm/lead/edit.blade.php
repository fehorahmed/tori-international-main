@extends('layouts.app')
@section('title','Outlet Setup Edit')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Outlet Setup Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('leads.update',['lead'=>$lead->id]) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('client_name') ? 'has-error' :'' }}">
                                    <label for="client_name">Client Name <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('client_name',$lead->crmClient->name ?? '') }}" name="client_name" class="form-control" id="client_name" placeholder="Enter Client Name">
                                    @error('client_name')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('client_contact_no') ? 'has-error' :'' }}">
                                    <label for="client_contact_no">Client Contact No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('client_contact_no',$lead->crmClient->contact_no ?? '') }}" name="client_contact_no" class="form-control" id="client_contact_no" placeholder="Enter Client Contact No.">
                                    @error('client_contact_no')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('customer_type_id') ? 'has-error' :'' }}">
                                    <label for="customer_type_id">Customer Type</label>
                                    <select name="customer_type_id" id="customer_type_id" class="form-control select2">
                                        <option value="">Select Type</option>
                                        @foreach($customerTypes as $type)
                                            <option {{ old('customer_type_id', $lead->crmClient->customer_type_id ?? '') == $type->id ? 'selected' : '' }} value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_type_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" value="{{ old('date', $lead->date) }}" name="date" class="form-control date-picker" id="date" placeholder="Enter Date">
                                    @error('date')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('client_email') ? 'has-error' :'' }}">
                                    <label for="client_email">Client Email</label>
                                    <input type="email" value="{{ old('client_email',$lead->crmClient->email ?? '') }}" name="client_email" class="form-control" id="client_email" placeholder="Enter Client Email">
                                    @error('client_email')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('division') ? 'has-error' :'' }}">
                                    <label for="division">Division <span class="text-danger">*</span></label>
                                    <select name="division" id="division" class="form-control select2">
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                            <option {{ old('division',$lead->crmClient->division_id ?? '') == $division->id ? 'selected' : '' }} value="{{ $division->id }}">{{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('division')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('district') ? 'has-error' :'' }}">
                                    <label for="district">District <span class="text-danger">*</span></label>
                                    <select name="district" id="district" class="form-control select2">
                                        <option value="">Select District</option>
                                    </select>
                                    @error('district')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('upazila') ? 'has-error' :'' }}">
                                    <label for="upazila">Upazila <span class="text-danger">*</span></label>
                                    <select name="upazila" id="upazila" class="form-control select2">
                                        <option value="">Select Upazila</option>
                                    </select>
                                    @error('upazila')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('market_id') ? 'has-error' :'' }}">
                                    <label for="market_id">Market / Area <span class="text-danger">*</span></label>
                                    <select name="market_id" id="market_id" class="form-control select2" required>
                                        <option value="">Select Market</option>
                                    </select>
                                    @error('market_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('union') ? 'has-error' :'' }}">
                                    <label for="union">Union/Pourashava</label>
                                    <select name="union" id="union" class="form-control select2">
                                        <option value="">Select Union/Pourashava</option>
                                        @if($lead->crmClient->pourashava_id ?? null)
                                            <option value="pourashava_{{ $lead->crmClient->pourashava_id }}" selected>{{ $lead->crmClient->pourashava->name ?? '' }} (Pourashava)</option>
                                        @elseif($lead->crmClient->union_id ?? null)
                                            <option value="union_{{ $lead->crmClient->union_id }}" selected>{{ $lead->crmClient->union->name ?? '' }} (Union)</option>
                                        @endif
                                    </select>
                                    @error('union')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('village_id') ? 'has-error' :'' }}">
                                    <label for="village_id">Village</label>
                                    <select name="village_id" id="village_id" class="form-control select2" data-selected="{{ old('village_id', $lead->crmClient->village_id ?? '') }}">
                                        <option value="">Select Village</option>
                                    </select>
                                    @error('village_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                    <label for="notes">Notes <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('notes',$lead->notes) }}" name="notes" class="form-control" id="notes" placeholder="Enter Notes..">
                                    @error('notes')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('image') ? 'has-error' :'' }}">
                                    <label for="image">Client Image</label>
                                    <input type="file" name="image" class="form-control" id="image">
                                    @if($lead->crmClient->image ?? '')
                                        <img height="50px" src="{{ asset($lead->crmClient->image) }}" alt="">
                                    @endif
                                    @error('image')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('leads.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('#division').change(function () {
                let divisionId = $(this).val();
                $('#district').html('<option value="">Select District</option>');
                $('#upazila').html('<option value="">Select Upazila</option>');
                $('#union').html('<option value="">Select Union/Pourashava</option>');

                if (divisionId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_districts') }}",
                        data: { divisionId: divisionId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#district').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                        @if($lead->crmClient->district_id)
                            $('#district').val('{{ $lead->crmClient->district_id }}').trigger('change');
                        @endif
                    });
                }
            });

            $('#district').change(function () {
                let districtId = $(this).val();
                $('#upazila').html('<option value="">Select Upazila</option>');
                $('#union').html('<option value="">Select Union/Pourashava</option>');

                if (districtId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_upazilas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#upazila').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                        @if($lead->crmClient->upazila_id)
                            $('#upazila').val('{{ $lead->crmClient->upazila_id }}').trigger('change');
                        @endif
                    });

                    // Load Pourashavas directly into Union/Pourashava dropdown
                    $.get("{{ url('admin/get-pourashavas') }}", {districtId: districtId}, function(data) {
                        data.forEach(item => {
                            let selected = '{{ $lead->crmClient->pourashava_id ?? "" }}' == item.id ? 'selected' : '';
                            $('#union').append(`<option value="pourashava_${item.id}" ${selected}>${item.name} (Pourashava)</option>`);
                        });
                    });
                }
            });

            $('#upazila').change(function () {
                let upazilaId = $(this).val();
                $('#market_id').html('<option value="">Select Market</option>');
                
                // Remove only Union options, keep Pourashava options if loaded from District
                $('#union option').filter(function() {
                    return $(this).text().includes('(Union)');
                }).remove();

                if (upazilaId != '') {
                    // Fetch Markets
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_markets') }}",
                        data: { upazilaId: upazilaId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#market_id').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                        @if($lead->crmClient->market_id ?? null)
                            $('#market_id').val('{{ $lead->crmClient->market_id }}').trigger('change');
                        @endif
                    });

                    // Fetch Unions
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_unions') }}",
                        data: { upazilaId: upazilaId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            let selected = '{{ $lead->crmClient->union_id ?? "" }}' == item.id ? 'selected' : '';
                            $('#union').append(`<option value="union_${item.id}" ${selected}>${item.name} (Union)</option>`);
                        });
                        @if($lead->crmClient->union_id ?? null)
                            $('#union').val('union_{{ $lead->crmClient->union_id }}').trigger('change');
                        @elseif($lead->crmClient->pourashava_id ?? null)
                            $('#union').val('pourashava_{{ $lead->crmClient->pourashava_id }}').trigger('change');
                        @endif
                    });
                }
            });

            $('#union').change(function () {
                let id = $(this).val();
                let targetEl = $('#village_id');
                targetEl.html('<option value="">Select Village</option>');

                if (id && id != '') {
                    let data = {};
                    if (id.includes('union_')) {
                        data.unionId = id.replace('union_', '');
                    } else if (id.includes('pourashava_')) {
                        data.pourashavaId = id.replace('pourashava_', '');
                    }

                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_villages') }}",
                        data: data
                    }).done(function( response ) {
                        $.each(response, function( index, item ) {
                            targetEl.append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                        let oldVillage = targetEl.data('selected');
                        if (oldVillage) {
                            targetEl.val(oldVillage).trigger('change');
                        }
                    });
                }
            });

            @if($lead->crmClient->division_id)
                $('#division').trigger('change');
            @endif
        });
    </script>
@endsection

