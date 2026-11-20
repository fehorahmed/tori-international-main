<?php

namespace App\Services\Membership;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegistrationAddressService
{
    public static function validate(array $input): array
    {
        // Older installed apps may omit all address fields; new forms require the first three.
        return Validator::make($input, [
            'division_id' => ['nullable', 'required_with:district_id,upazila_id,union_id,pourashava_id,village_id,address', 'integer', Rule::exists('divisions', 'id')],
            'district_id' => ['nullable', 'required_with:division_id', 'integer', Rule::exists('districts', 'id')->where('division_id', $input['division_id'] ?? 0)],
            'upazila_id' => ['nullable', 'required_with:division_id', 'integer', Rule::exists('upazilas', 'id')->where('district_id', $input['district_id'] ?? 0)],
            'union_id' => ['nullable', 'prohibits:pourashava_id', 'integer', Rule::exists('unions', 'id')->where('upazila_id', $input['upazila_id'] ?? 0)],
            'pourashava_id' => ['nullable', 'prohibits:union_id,village_id', 'integer', Rule::exists('pourashavas', 'id')->where('district_id', $input['district_id'] ?? 0)],
            'village_id' => ['nullable', 'integer', Rule::exists('villages', 'id')->where('union_id', $input['union_id'] ?? 0)],
            'address' => 'nullable|string|max:500',
        ])->validate();
    }

    public static function locations(string $level, int $parent): array
    {
        $tables = ['districts' => ['districts', 'division_id'], 'upazilas' => ['upazilas', 'district_id'], 'villages' => ['villages', 'union_id']];
        $present = fn ($rows) => $rows->map(fn ($row) => ['id' => $row->id, 'name' => $row->name, 'bn_name' => $row->bn_name ?? null])->values()->all();
        if ($level === 'areas') {
            $upazila = DB::table('upazilas')->where('id', $parent)->first();
            abort_unless($upazila, 422, 'Invalid upazila.');
            $unions = $present(DB::table('unions')->where('upazila_id', $parent)->orderBy('name')->get());
            $towns = $present(DB::table('pourashavas')->where('district_id', $upazila->district_id)->orderBy('name')->get());

            return array_merge(array_map(fn ($r) => ['value' => 'union_'.$r['id']] + $r, $unions), array_map(fn ($r) => ['value' => 'pourashava_'.$r['id']] + $r, $towns));
        }
        [$table, $foreign] = $tables[$level];

        return $present(DB::table($table)->where($foreign, $parent)->orderBy('name')->get());
    }
}
