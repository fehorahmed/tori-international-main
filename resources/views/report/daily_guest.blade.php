@extends('layouts.app')
@section('title','Daily Guest Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 2px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
        }
        .custom-p-tag{
            font-size: 22px;
        }

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.daily_guests') }}">
                        <div class="row">
                            <div class="col-12 col-md-5">
                                <div class="form-group">
                                    <label for="guest_type" class="col-form-label">Guest Type <span
                                            class="text-danger">*</span></label>
                                    <select required name="guest_type" class="form-control select2" id="guest_type">
                                        <option value="">Select Guest Type</option>
                                        <option {{ request('guest_type') == 3 ? 'selected' : '' }} value="3">All</option>
                                        <option {{ request('guest_type') == 1 ? 'selected' : '' }} value="1">Local</option>
                                        <option {{ request('guest_type') == 2 ? 'selected' : '' }} value="2">Foreign</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
                                           class="btn btn-info bg-gradient-info form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if(request('guest_type') != '')
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a role="button" onclick="getprint('printArea')" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-print"></i></a>
                    <a href="{{ route('report.daily_excel_convert',['guest_type'=>request('guest_type')]) }}" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-file-excel"></i></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive-md" id="printArea">
                        <div class="row">
                            <div class="col-12">
                                <p class="custom-p-tag">To</p>
                                <p class="custom-p-tag">Rajshahi Metropolitan Police (RMP)</p>
                                <p class="custom-p-tag">Rajshahi</p>
                                <p class="custom-p-tag pb-4">Sub: Reporting of Daily Guests</p>
                                <p class="custom-p-tag">Sir,</p>
                                <p class="custom-p-tag pb-4">With due regards, I would like to notify that, the particulars of boarders at Hotel Mukta International on the date of {{ date('d-m-Y') }} are listed hereunder for your kind attention.</p>
                            </div>
                        </div>
                        @if(request('guest_type') == 1)
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th contenteditable="true" class="text-center">S/L</th>
                                <th contenteditable="true" class="text-center">Name</th>
                                <th contenteditable="true" class="text-center">Age</th>
                                <th contenteditable="true" class="text-center">Occupation</th>
                                <th contenteditable="true" class="text-center">Address</th>
                                <th contenteditable="true" class="text-center">Room</th>
                                <th contenteditable="true" class="text-center">Mobile</th>


                            </tr>
                            </thead>
                            <tbody>
                            @foreach($checkedIns as $checkedIn)
                                @php
                                    $otherGuests = \App\Models\Guest::whereIn('id',json_decode($checkedIn->guests_id))
                                                ->where('id','!=',$checkedIn->guest_id)
                                                ->get();
                                @endphp
                                <tr>
                                    <td contenteditable="true" class="text-center">{{ $loop->iteration }}</td>
                                    <td contenteditable="true" class="text-left">
                                        {{ $checkedIn->guest_name }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                                        @foreach($otherGuests as $otherGuest)
                                            {{ $otherGuest->name }} <br>
                                        @endforeach
                                    </td>
                                    <td contenteditable="true" class="text-center">
                                        {{ $checkedIn->guest->age ?? '' }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                                        @foreach($otherGuests as $otherGuest)
                                            {{ $otherGuest->age }} <br>
                                        @endforeach

                                    </td>
                                    <td contenteditable="true"></td>
                                    <td contenteditable="true" class="text-center">
                                        {{ $checkedIn->address }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                                        @foreach($otherGuests as $otherGuest)
                                            {{ $otherGuest->current_address }} <br>
                                        @endforeach
                                    </td>
                                    <td contenteditable="true" class="text-center">{{ $checkedIn->room->room_no ?? '' }}</td>
                                    <td contenteditable="true" class="text-center">
                                        {{ $checkedIn->mobile_no }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                                        @foreach($otherGuests as $otherGuest)
                                            {{ $otherGuest->mobile_no }} <br>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @elseif(request('guest_type') == 2)
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th contenteditable="true" class="text-center">S/L</th>
                                    <th contenteditable="true" class="text-center">Guest Name</th>
                                    <th contenteditable="true" class="text-center">Check-In Date</th>
                                    <th contenteditable="true" class="text-center">Check-In Time</th>
                                    <th contenteditable="true" class="text-center">Passport No.</th>
                                    <th contenteditable="true" class="text-center">Nationality</th>
                                    <th contenteditable="true" class="text-center">Room No.</th>
                                    <th contenteditable="true" class="text-center">Purpose of Visit</th>
                                    <th contenteditable="true" class="text-center">Visa Ex. Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($checkedIns as $checkedIn)
                                    @php
                                        $otherGuests = \App\Models\Guest::whereIn('id',json_decode($checkedIn->guests_id))
                                        ->where('id','!=',$checkedIn->guest_id)
                                        ->get();
                                    @endphp
                                    <tr>
                                        <td contenteditable="true" class="text-center">{{ $loop->iteration }}</td>
                                        <td contenteditable="true" class="text-left">
                                            {{ $checkedIn->guest_name }}{!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                                            @foreach($otherGuests as $otherGuest)
                                                {{ $otherGuest->name }} <br>
                                            @endforeach
                                        </td>
                                        <td contenteditable="true" class="text-center">{{ $checkedIn->created_at->format('d-m-Y') }}</td>
                                        <td contenteditable="true" class="text-center">{{ $checkedIn->created_at->format('h:i a') }}</td>
                                        <td contenteditable="true" class="text-center">
                                            {{ $checkedIn->guest_passport_no }}{!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                                            @foreach($otherGuests as $otherGuest)
                                                {{ $otherGuest->passport_no }} <br>
                                            @endforeach
                                        </td>
                                        <td contenteditable="true" class="text-center"></td>
                                        <td contenteditable="true" class="text-center">{{ $checkedIn->room->room_no ?? '' }}</td>
                                        <td contenteditable="true" class="text-center"></td>
                                        <td contenteditable="true" class="text-center"></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @elseif(request('guest_type') == 3)
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th contenteditable="true" class="text-center">S/L</th>
                                    <th contenteditable="true" class="text-center">Name</th>
                                    <th contenteditable="true" class="text-center">Age</th>
                                    <th contenteditable="true" class="text-center">Occupation</th>
                                    <th contenteditable="true" class="text-center">Address</th>
                                    <th contenteditable="true" class="text-center">Room</th>
                                    <th contenteditable="true" class="text-center">Mobile</th>


                                </tr>
                                </thead>
                                <tbody>
                                @foreach($checkedIns as $checkedIn)
                                    @php
                                        $otherGuests = \App\Models\Guest::whereIn('id',json_decode($checkedIn->guests_id))
                                                    ->where('id','!=',$checkedIn->guest_id)
                                                    ->get();
                                    @endphp
                                    <tr>
                                        <td contenteditable="true" class="text-center">{{ $loop->iteration }}</td>
                                        <td contenteditable="true" class="text-left">
                                            {{ $checkedIn->guest_name }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                                            @foreach($otherGuests as $otherGuest)
                                                {{ $otherGuest->name }} <br>
                                            @endforeach
                                        </td>
                                        <td contenteditable="true" class="text-center">
                                            {{ $checkedIn->guest->age ?? '' }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                                            @foreach($otherGuests as $otherGuest)
                                                {{ $otherGuest->age }} <br>
                                            @endforeach

                                        </td>
                                        <td contenteditable="true"></td>
                                        <td contenteditable="true" class="text-center">
                                            {{ $checkedIn->address }} {!! count($otherGuests->whereNull('guest_passport_no')) > 0 ? '<br>' : ''  !!}
                                            @foreach($otherGuests->whereNull('guest_passport_no') as $otherGuest)
                                                {{ $otherGuest->current_address }} <br>
                                            @endforeach
                                            @if($checkedIn->guest_passport_no != '')
                                                Passport No. {{ $checkedIn->guest_passport_no }}
                                            @endif
                                            {!! count($otherGuests->whereNotNull('guest_passport_no')) > 0 ? '<br>' : ''  !!}
                                            @foreach($otherGuests->whereNotNull('guest_passport_no') as $otherGuest)
                                                Passport No. {{ $otherGuest->passport_no }} <br>
                                            @endforeach
                                        </td>
                                        <td contenteditable="true" class="text-center">{{ $checkedIn->room->room_no ?? '' }}</td>
                                        <td contenteditable="true" class="text-center">
                                            {{ $checkedIn->mobile_no }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                                            @foreach($otherGuests as $otherGuest)
                                                {{ $otherGuest->mobile_no }} <br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                        <div class="row signature-area" style="position: fixed;bottom: 0;left: 0;width: 100%;">
                            <div class="col-3 text-center"><span style="border-top: 1px dotted #000 !important;display: block;padding: 5px;font-size: 20px;font-weight: bold">Authorized Signature</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@section('script')
    <script>
        var APP_URL = '{!! url()->full()  !!}';
        function getprint(print) {
            document.title = "{{ request('start_date') }}_to_{{ request('end_date') }}_receipt_and_payment_report";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
        $('table tbody tr').hover(function(){
            $(this).append('<button class="delete-row-btn">❌</button>');
        }, function(){
            $(this).find('.delete-row-btn').remove();
        });

        // Delete row when cross button is clicked
        $('table').on('click', '.delete-row-btn', function(){
            $(this).closest('tr').remove();
            // Update serial numbers
            $('table tbody tr').each(function(index){
                $(this).find('td:first').text(index + 1);
            });
        });

    </script>
@endsection
