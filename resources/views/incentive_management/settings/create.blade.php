@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Category Filter — Bulk Incentive Setup</h3>
                    <div class="card-tools">
                        <a href="{{ route('incentive-settings.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('incentive-settings.create') }}" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label>Branch <span class="text-danger">*</span></label>
                                <select name="sister_concern_id" id="sister_concern_id" class="form-control select2" required>
                                    <option value="">-- Branch সিলেক্ট করুন --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $sisterConcernId == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Product Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-control select2" required>
                                    <option value="">-- Category সিলেক্ট করুন --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i> Load Products
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($products->isNotEmpty())
            <div class="card card-default">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-2"></i>
                        ইনসেনটিভ সেটিং — {{ $products->count() }} টি Product
                    </h3>
                    <button type="button" id="btnSaveAll" class="btn btn-success btn-sm ml-auto">
                        <i class="fas fa-save mr-1"></i> Save All
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="settingsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width:5%">#</th>
                                    <th>Product Name</th>
                                    <th class="text-center" style="width:20%">SR <small class="text-muted">(Sales Rep)</small></th>
                                    <th class="text-center" style="width:20%">BM <small class="text-muted">(Branch Manager)</small></th>
                                    <th class="text-center" style="width:20%">AM <small class="text-muted">(Area Manager)</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $i => $product)
                                    @php $s = $existingSettings->get($product->id); @endphp
                                    <tr>
                                        <td class="text-center align-middle">{{ $i + 1 }}</td>
                                        <td class="align-middle">
                                            <strong>{{ $product->name }}</strong>
                                            <input type="hidden" name="settings[{{ $i }}][product_id]" value="{{ $product->id }}">
                                        </td>

                                        {{-- SR --}}
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <input type="number" step="0.01" min="0" max="100"
                                                       name="settings[{{ $i }}][sr_percent]"
                                                       class="form-control form-control-sm percent-input"
                                                       value="{{ $s->sr_percent ?? 0 }}"
                                                       style="width: 70px;">
                                                <span class="ml-1 mr-2">%</span>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input"
                                                           id="sr_active_{{ $i }}"
                                                           name="settings[{{ $i }}][sr_active]"
                                                           value="1"
                                                           {{ (!$s || $s->sr_active) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="sr_active_{{ $i }}"></label>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- BM --}}
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <input type="number" step="0.01" min="0" max="100"
                                                       name="settings[{{ $i }}][bm_percent]"
                                                       class="form-control form-control-sm percent-input"
                                                       value="{{ $s->bm_percent ?? 0 }}"
                                                       style="width: 70px;">
                                                <span class="ml-1 mr-2">%</span>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input"
                                                           id="bm_active_{{ $i }}"
                                                           name="settings[{{ $i }}][bm_active]"
                                                           value="1"
                                                           {{ (!$s || $s->bm_active) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="bm_active_{{ $i }}"></label>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- AM --}}
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-1">
                                                <input type="number" step="0.01" min="0" max="100"
                                                       name="settings[{{ $i }}][am_percent]"
                                                       class="form-control form-control-sm percent-input"
                                                       value="{{ $s->am_percent ?? 0 }}"
                                                       style="width: 70px;">
                                                <span class="ml-1 mr-2">%</span>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input"
                                                           id="am_active_{{ $i }}"
                                                           name="settings[{{ $i }}][am_active]"
                                                           value="1"
                                                           {{ (!$s || $s->am_active) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="am_active_{{ $i }}"></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" id="btnSaveAllBottom" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Save All Settings
                    </button>
                </div>
            </div>
            @elseif($categoryId)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    এই Category-তে কোনো Active Product পাওয়া যায়নি।
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
<script>
$(function () {
    // ─── Save All (Bulk Upsert) ───────────────────────────────────
    function saveAll() {
        const settings = [];
        let valid = true;

        $('#settingsTable tbody tr').each(function (i) {
            const row      = $(this);
            const productId = row.find('input[type=hidden]').val();
            const srPercent = parseFloat(row.find('[name*="sr_percent"]').val()) || 0;
            const bmPercent = parseFloat(row.find('[name*="bm_percent"]').val()) || 0;
            const amPercent = parseFloat(row.find('[name*="am_percent"]').val()) || 0;
            const srActive  = row.find('[name*="sr_active"]').is(':checked') ? 1 : 0;
            const bmActive  = row.find('[name*="bm_active"]').is(':checked') ? 1 : 0;
            const amActive  = row.find('[name*="am_active"]').is(':checked') ? 1 : 0;

            settings.push({ product_id: productId, sr_percent: srPercent, bm_percent: bmPercent, am_percent: amPercent, sr_active: srActive, bm_active: bmActive, am_active: amActive });
        });

        if (!valid || settings.length === 0) return;

        const sisterConcernId = $('#sister_concern_id').val();

        if (!sisterConcernId) {
            toastr.error('Branch সিলেক্ট করুন!');
            return;
        }

        $.ajax({
            url: '{{ route('incentive-settings.bulk-store') }}',
            method: 'POST',
            data: { sister_concern_id: sisterConcernId, settings: settings, _token: '{{ csrf_token() }}' },
            beforeSend: () => { $('#btnSaveAll, #btnSaveAllBottom').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...'); },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(() => {
                        window.location.href = '{{ route('incentive-settings.index') }}';
                    }, 1500);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    Object.values(errors).forEach(e => toastr.error(e[0]));
                } else {
                    toastr.error('Something went wrong!');
                }
            },
            complete: () => { $('#btnSaveAll, #btnSaveAllBottom').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save All'); },
        });
    }

    $('#btnSaveAll, #btnSaveAllBottom').on('click', saveAll);
});
</script>
@endsection
