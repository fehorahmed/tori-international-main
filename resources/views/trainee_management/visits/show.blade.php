<div class="row">
    <div class="col-md-6">
        <table class="table table-bordered table-sm">
            <tr>
                <th width="40%">Visit Date</th>
                <td>{{ \Carbon\Carbon::parse($visit->date)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Trainee Name</th>
                <td>{{ $visit->trainee->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Category</th>
                <td>{{ $visit->visitType->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Client Name</th>
                <td>{{ $visit->visitClient->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Mobile No</th>
                <td>{{ $visit->visitClient->mobile_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Client Address</th>
                <td>
                    @php
                        $addr = [];
                        $obj = $visit->visitClient;
                        if ($obj) {
                            if ($obj->village) $addr[] = $obj->village;
                            if ($obj->union) $addr[] = $obj->union->name ?? '';
                            if ($obj->pourashava) $addr[] = $obj->pourashava->name ?? '';
                            if ($obj->upazila) $addr[] = $obj->upazila->name ?? '';
                            if ($obj->district) $addr[] = $obj->district->name ?? '';
                            if ($obj->division) $addr[] = $obj->division->name ?? '';
                        }
                        echo !empty($addr) ? implode(', ', array_filter($addr)) : 'N/A';
                    @endphp
                </td>
            </tr>
            <tr>
                <th>Live Address</th>
                <td>{{ $visit->location ?? 'N/A' }}</td>
            </tr>
        </table>
        
        <div class="mt-3">
            <strong>Visit Notes:</strong>
            <p class="p-2 border rounded bg-light">{{ $visit->notes ?: 'No notes available' }}</p>
        </div>
        
        @if($visit->lat && $visit->long)
        <div class="mt-2 text-center">
            <a href="https://www.google.com/maps?q={{ $visit->lat }},{{ $visit->long }}" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-map-marked-alt mr-1"></i> View on Google Maps
            </a>
        </div>
        @endif
    </div>
    <div class="col-md-6 text-center">
        <strong>Visit Photo:</strong>
        <div class="mt-2">
            @if($visit->image)
                <img src="{{ asset($visit->image) }}" class="img-fluid border rounded shadow-sm" style="max-height: 400px;" alt="Visit Photo">
            @else
                <div class="p-5 border rounded bg-light text-muted">
                    <i class="fas fa-image fa-3x mb-2"></i><br>
                    No Photo Captured
                </div>
            @endif
        </div>
    </div>
</div>

