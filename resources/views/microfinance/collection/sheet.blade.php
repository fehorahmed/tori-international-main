@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .sheet-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .sheet-header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 20px 20px 0 0 !important; padding: 25px; }
    .filter-section { background: #f8f9fa; border-radius: 15px; padding: 20px; margin-bottom: 25px; }
    .table-sheet thead th { position: sticky; top: 0; background: #eee; z-index: 10; font-size: 0.85rem; text-transform: uppercase; }
    .member-row:hover { background-color: rgba(0,123,255,0.05); }
    .input-collect { border-radius: 8px; border: 1px solid #ced4da; padding: 5px 10px; text-align: right; width: 100px; transition: all 0.3s; }
    .input-collect:focus { border-color: #007bff; outline: none; box-shadow: 0 0 0 3px rgba(0,123,255,0.1); }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card sheet-card">
                <div class="card-header sheet-header text-white">
                    <h3 class="card-title mb-0"><i class="fa fa-clipboard-list mr-2"></i> {{ $pageTitle }}</h3>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="filter-section">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="font-weight-bold">Select Somiti (সমিতি নির্বাচন করুন)</label>
                                <select id="somiti_id" class="form-control select2">
                                    <option value="">-- Choose Somiti --</option>
                                    @foreach($somitis as $somiti)
                                        <option value="{{ $somiti->id }}">{{ $somiti->name }} ({{ $somiti->somiti_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="font-weight-bold">Collection Date</label>
                                <input type="text" id="collection_date" class="form-control date-picker" value="{{ date('Y-m-d') }}" autocomplete="off" data-date-format="yyyy-mm-dd">
                            </div>
                            <div class="col-md-2">
                                <button id="btn_fetch" class="btn btn-primary btn-block shadow-sm" style="border-radius: 10px; height: 45px;">
                                    <i class="fa fa-search mr-1"></i> Load Sheet
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Data Container -->
                    <form id="collection_form">
                        @csrf
                        <input type="hidden" name="date" id="hidden_date">
                        <div id="sheet_container" class="table-responsive" style="min-height: 300px;">
                            <div class="text-center p-5 text-muted">
                                <i class="fa fa-info-circle fa-3x mb-3"></i>
                                <p>Please select a Somiti and click "Load Sheet" to start collection.</p>
                            </div>
                        </div>
                        
                        <div id="footer_actions" class="text-right mt-4 d-none">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm" style="border-radius: 15px; font-weight: 700;">
                                <i class="fa fa-save mr-2"></i> SAVE COLLECTIONS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        if ($.isFunction($.fn.datepicker)) {
            $('.date-picker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd',
                todayHighlight: true
            });
        }

        $('#btn_fetch').on('click', function() {
            var somitiId = $('#somiti_id').val();
            var date = $('#collection_date').val();

            if(!somitiId) {
                Swal.fire('Wait!', 'Please select a Somiti first.', 'warning');
                return;
            }

            $('#sheet_container').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x text-primary"></i><p class="mt-2">Loading member data...</p></div>');
            $('#footer_actions').addClass('d-none');
            $('#hidden_date').val(date);

            $.ajax({
                url: "{{ route('microfinance.collection.fetch_sheet') }}",
                method: "GET",
                data: { somiti_id: somitiId, date: date },
                success: function(html) {
                    $('#sheet_container').html(html);
                    $('#footer_actions').removeClass('d-none');
                },
                error: function() {
                    $('#sheet_container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
                }
            });
        });

        $('#collection_form').on('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to save the collections for this Somiti.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Save it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('microfinance.collection.save_sheet') }}",
                        method: "POST",
                        data: $(this).serialize(),
                        success: function(res) {
                            if(res.success) {
                                Swal.fire('Saved!', res.message, 'success');
                                $('#sheet_container').html('<div class="text-center p-5 text-success"><i class="fa fa-check-circle fa-4x mb-3"></i><p>Collection successfully saved!</p></div>');
                                $('#footer_actions').addClass('d-none');
                            }
                        },
                        error: function(err) {
                            Swal.fire('Error', 'Something went wrong while saving.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
