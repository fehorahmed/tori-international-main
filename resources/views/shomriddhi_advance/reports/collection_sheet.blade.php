@extends('layouts.app')

@section('title', 'Samity Collection Sheet')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-info text-white">
            <h5 class="card-title mb-0"><i class="fas fa-print mr-2"></i> Generate Monthly Collection Sheet</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('shomriddhi-advance.reports.collection_sheet') }}" method="GET" target="_blank" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <div class="form-group mb-0">
                            <label>Select Samity / Group <span class="text-danger">*</span></label>
                            <select name="group_id" class="form-control select2" required style="width: 100%;">
                                <option value="">Select Samity...</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->code }} - {{ $group->name_en }} ({{ $group->name_bn }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label>Select Month <span class="text-danger">*</span></label>
                            <input type="month" name="month" class="form-control" value="{{ date('Y-m') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search"></i> Generate Sheet</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2({ theme: 'bootstrap4' });
        });
    </script>
@endsection
