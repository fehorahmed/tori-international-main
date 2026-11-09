<div class="row">
    <!-- Current Address -->
    <div class="col-md-6">
        <h5 class="border-bottom pb-2 mb-3 text-info"><i class="fa fa-map-marker-alt"></i> Current Address</h5>
        <table class="table table-sm table-borderless">
            <tr>
                <th width="35%">Division</th>
                <td>: {{ $employee->currentDivision->name ?? '' }}</td>
            </tr>
            <tr>
                <th>District</th>
                <td>: {{ $employee->currentDistrict->name ?? '' }}</td>
            </tr>
            <tr>
                <th>Upazila</th>
                <td>: {{ $employee->currentUpazila->name ?? '' }}</td>
            </tr>
            @if ($employee->currentPourashava)
                <tr>
                    <th>Pourashava</th>
                    <td>: {{ $employee->currentPourashava->name ?? '' }}</td>
                </tr>
            @endif
            @if ($employee->currentUnion)
                <tr>
                    <th>Union</th>
                    <td>: {{ $employee->currentUnion->name ?? '' }}</td>
                </tr>
            @endif
            <tr>
                <th>Village</th>
                <td>: {{ $employee->currentVillage->name ?? '' }}</td>
            </tr>
        </table>
    </div>

    <!-- Permanent Address -->
    <div class="col-md-6">
        <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa fa-home"></i> Permanent Address</h5>
        <table class="table table-sm table-borderless">
            <tr>
                <th width="35%">Division</th>
                <td>: {{ $employee->permanentDivision->name ?? '' }}</td>
            </tr>
            <tr>
                <th>District</th>
                <td>: {{ $employee->permanentDistrict->name ?? '' }}</td>
            </tr>
            <tr>
                <th>Upazila</th>
                <td>: {{ $employee->permanentUpazila->name ?? '' }}</td>
            </tr>
            @if ($employee->permanentPourashava)
                <tr>
                    <th>Pourashava</th>
                    <td>: {{ $employee->permanentPourashava->name ?? '' }}</td>
                </tr>
            @endif
            @if ($employee->permanentUnion)
                <tr>
                    <th>Union</th>
                    <td>: {{ $employee->permanentUnion->name ?? '' }}</td>
                </tr>
            @endif
            <tr>
                <th>Village</th>
                <td>: {{ $employee->permanentVillage->name ?? '' }}</td>
            </tr>
        </table>
    </div>
</div>
