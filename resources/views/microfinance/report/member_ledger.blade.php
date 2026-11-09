@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Member Filter</h3>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('microfinance.report.member-ledger') }}" method="get" class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Select Member: <span class="text-danger">*</span></label>
                                <select name="member_id" class="form-control select2" required>
                                    <option value="">Select Member</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}" {{ request('member_id') == $m->id ? 'selected' : '' }}>{{ $m->member_no }} - {{ $m->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" name="search" class="btn btn-info bg-gradient-info form-control"><i class="fa fa-search"></i> Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2();
        });
    </script>
@endsection
