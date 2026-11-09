@extends('layouts.app')
@section('title','Notice')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">{{ $notice->title }}</div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <b>Date:</b> {{ \Carbon\Carbon::parse($notice->updated_at)->format('d-m-Y, h:i:s a') }}
                        <br>
                       {!! $notice->content !!}
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection

