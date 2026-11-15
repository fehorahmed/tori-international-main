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
            'membership.registration_fee' => '0.00', 'membership.account_hold_amount' => '0.00',
            'membership.income_head_id' => null,
            'referral_milestones.enabled' => true, 'referral_milestones.tiers' => [
                ['id' => 'bc276331-ae45-43e8-8230-000000000001', 'threshold' => 100, 'percentage' => '25.00', 'enabled' => true],
                ['id' => 'bc276331-ae45-43e8-8230-000000000002', 'threshold' => 200, 'percentage' => '50.00', 'enabled' => true],
                ['id' => 'bc276331-ae45-43e8-8230-000000000003', 'threshold' => 300, 'percentage' => '100.00', 'enabled' => true],
            ],
            'referral.enabled' => false, 'referral.member_points' => 0, 'referral.point_value' => '30.00',
            'referral.agent_bonus' => '30.00', 'referral.expense_head_id' => null,
            'referral.payable_head_id' => null, 'referral.posting_user_id' => null,
            'membership_discount.enabled' => true, 'membership_discount.minimum_balance' => '1000.00',
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
                } elseif (in_array($field, ['minimum_amount', 'maximum_amount', 'minimum_balance', 'charge_rate', 'registration_fee', 'account_hold_amount', 'point_value', 'agent_bonus'], true)) {
                    $value = number_format((float) $value, 2, '.', '');
                } elseif (in_array($field, ['hold_minutes', 'income_head_id', 'posting_user_id', 'expense_head_id', 'payable_head_id', 'member_points'], true)) {
                    $value = $value === null || $value === '' ? null : (int) $value;
                }
                static::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        });
    }
}
