@extends('layouts.app')
@section('title','Opportunity Update History')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Opportunity Update History</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="timeline">

                                    <div class="time-label">
                                        <span  class="bg-success">{{ $opportunity->order_no }}, {{ \Carbon\Carbon::parse($opportunity->created_at)->format('d M. Y') }}</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-user bg-green"></i>
                                        <div class="timeline-item">
                                            <span class="time"><i class="fas fa-clock"></i> {{ $opportunity->created_at->format('d-m-Y, h:i:s a') }}</span>
                                            <h3 class="timeline-header">Client: <a href="#">{{ $opportunity->crmClient->name ?? '' }}</a></h3>
                                            <div class="timeline-body">
                                                Contact No: {{ $opportunity->crmClient->contact_no ?? '' }} <br>
                                                Email: {{ $opportunity->crmClient->email ?? '' }} <br>
                                                Address: {{ $opportunity->crmClient->address ?? '' }} <br>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <i class="fas fa-project-diagram bg-green"></i>
                                        <div class="timeline-item">
                                            <h3 class="timeline-header no-border">Lead Source: <a
                                                    href="#">{{ $opportunity->prospect->lead->lead_source ?? '' }}</a></h3>
                                        </div>
                                    </div>
                                    @foreach($opportunity->updateHistories as $updateHistory)
                                        <div>
                                            <i class="fas fa-envelope bg-blue"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fas fa-clock"></i> {{ $updateHistory->created_at->format('d-m-Y, h:i:s a') }}</span>
                                                <h3 class="timeline-header">{{ $loop->iteration }}. {{ ucfirst($updateHistory->action) }} By: <a
                                                        href="#">{{ $updateHistory->updateBy->name ?? '' }} </a>
                                                </h3>
                                                <div class="timeline-body">
                                                    {{ $updateHistory->notes }}
                                                </div>
                                                <div class="timeline-footer">
                                                        <span>Status:
                                                            <b class="">{{ $updateHistory->crmStatus->name ?? '' }}</b>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>

                                    @endforeach


                            </div>
                        </div>

                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>

@endsection

