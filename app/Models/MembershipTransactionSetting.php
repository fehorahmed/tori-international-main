<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MembershipTransactionSetting extends Model
{
    protected $guarded = [];

    protected $casts = ['value' => 'json'];

    public static function defaults(): array
    {
        return [
            'cashout.enabled' => false, 'cashout.minimum_amount' => '1.00', 'cashout.maximum_amount' => '0.00',
            'cashout.charge_type' => 'fixed', 'cashout.charge_rate' => '0.00',
            'cashout.hold_minutes' => 10, 'cashout.income_head_id' => null,
            'transfer.enabled' => false, 'transfer.minimum_amount' => '1.00', 'transfer.maximum_amount' => '0.00',
            'transfer.charge_type' => 'fixed', 'transfer.charge_rate' => '0.00',
            'transfer.income_head_id' => null, 'transfer.posting_user_id' => null,
            // Reserved until the Cash In workflow is implemented.
            'cashin.enabled' => false,
        ];
    }

    public static function read(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();

        return $row ? $row->value : (static::defaults()[$key] ?? $default);
    }

    public static function group(string $group): object
    {
        $defaults = static::defaults();
        $values = static::where('key', 'like', $group.'.%')->pluck('value', 'key')->all();
        $result = [];
        foreach ($defaults as $key => $default) {
            if (str_starts_with($key, $group.'.')) {
                $result[substr($key, strlen($group) + 1)] = array_key_exists($key, $values) ? $values[$key] : $default;
            }
        }

        return (object) $result;
    }

    public static function writeGroup(string $group, array $values): void
    {
        $defaults = static::defaults();
        DB::transaction(function () use ($group, $values, $defaults) {
            foreach ($values as $field => $value) {
                $key = $group.'.'.$field;
                if (! array_key_exists($key, $defaults)) {
                    throw new InvalidArgumentException('Unknown transaction setting: '.$key);
                }
                if ($field === 'enabled') {
                    $value = (bool) $value;
                } elseif (in_array($field, ['minimum_amount', 'maximum_amount', 'charge_rate'], true)) {
                    $value = number_format((float) $value, 2, '.', '');
                } elseif (in_array($field, ['hold_minutes', 'income_head_id', 'posting_user_id'], true)) {
                    $value = $value === null || $value === '' ? null : (int) $value;
                }
                static::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        });
    }
}
