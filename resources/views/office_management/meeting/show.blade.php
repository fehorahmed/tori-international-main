@extends('layouts.app')
@section('title','Meeting Summary')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">{{ $meeting->title }}</div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <b>Date:</b> {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('d-m-Y') }}
                        <br>
                        <b>Title:</b> {{ $meeting->title }}
                        <br>
                       {!! $meeting->content !!}
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection

