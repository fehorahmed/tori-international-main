<h6 class="mt-4 mb-4 border-bottom pb-2 font-weight-bold text-warning"><i class="fas fa-users mr-2"></i>Nominee Information</h6>
<div class="row">
    @foreach(['nominee_name' => ['Nominee Name', 'name'], 'nominee_relation' => ['Relation with Member', 'relation'], 'nominee_nid' => ['Nominee NID Number', 'nid_number'], 'nominee_phone' => ['Nominee Phone Number', 'phone']] as $field => [$label, $column])
    <div class="col-md-6 form-group">
        <label for="{{ $field }}">{{ $label }} @if($field === 'nominee_name' && $nominee)<span class="text-danger">*</span>@else<span class="text-muted">(Optional)</span>@endif</label>
        <input type="text" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $nominee?->{$column}) }}" class="form-control @error($field) is-invalid @enderror" maxlength="{{ $field === 'nominee_phone' ? 25 : 255 }}">
        @error($field)<div class="text-danger">{{ $message }}</div>@enderror
    </div>
    @endforeach
    <div class="col-md-6 form-group">
        <label for="nominee_image">Nominee Image <span class="text-muted">(Optional)</span></label>
        <input type="file" id="nominee_image" name="nominee_image" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control @error('nominee_image') is-invalid @enderror">
        @error('nominee_image')<div class="text-danger">{{ $message }}</div>@enderror
        @if($nominee?->image)<img src="{{ asset($nominee->image) }}" alt="Current nominee image" class="img-thumbnail mt-2" width="100">@endif
    </div>
    @php
        $unionValue = old('nominee_union', $nominee?->union_id ? 'union_'.$nominee->union_id : ($nominee?->pourashava_id ? 'pourashava_'.$nominee->pourashava_id : ''));
        $locations = [
            'nominee_division' => ['Division', $divisions, $nominee?->division_id],
            'nominee_district' => ['District', $nomineeDistricts, $nominee?->district_id],
            'nominee_upazila' => ['Upazila', $nomineeUpazilas, $nominee?->upazila_id],
            'nominee_village_id' => ['Village / Road', $nomineeVillages, $nominee?->village_id],
        ];
    @endphp
    @foreach($locations as $field => [$label, $items, $current])
        @if($field === 'nominee_village_id')
        <div class="col-md-6 form-group">
            <label for="nominee_union">Pourashava / Union <span class="text-muted">(Optional)</span></label>
            <select id="nominee_union" name="nominee_union" class="form-control select2 @error('nominee_union') is-invalid @enderror">
                <option value="">Select Pourashava / Union</option>
                @foreach($nomineePourashavas as $item)<option value="pourashava_{{ $item->id }}" @selected($unionValue === 'pourashava_'.$item->id)>{{ $item->name }} (P)</option>@endforeach
                @foreach($nomineeUnions as $item)<option value="union_{{ $item->id }}" @selected($unionValue === 'union_'.$item->id)>{{ $item->name }} (U)</option>@endforeach
            </select>
            @error('nominee_union')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        @endif
        <div class="col-md-6 form-group">
            <label for="{{ $field }}">{{ $label }} <span class="text-muted">(Optional)</span></label>
            <select id="{{ $field }}" name="{{ $field }}" class="form-control select2 @error($field) is-invalid @enderror">
                <option value="">Select {{ $label }}</option>
                @foreach($items as $item)<option value="{{ $item->id }}" @selected(old($field, $current) == $item->id)>{{ $item->name }}</option>@endforeach
            </select>
            @error($field)<div class="text-danger">{{ $message }}</div>@enderror
        </div>
    @endforeach
    <div class="col-12 form-group">
        <label for="nominee_address">Address Details <span class="text-muted">(Optional)</span></label>
        <textarea id="nominee_address" name="nominee_address" class="form-control @error('nominee_address') is-invalid @enderror" maxlength="2000" rows="2">{{ old('nominee_address', $nominee?->address) }}</textarea>
        @error('nominee_address')<div class="text-danger">{{ $message }}</div>@enderror
    </div>
</div>
<div id="nominee-location-error" class="text-danger mb-3 d-none" role="alert">Unable to load nominee address options. Please try selecting the location again.</div>
