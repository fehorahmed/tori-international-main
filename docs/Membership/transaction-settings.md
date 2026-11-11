# Membership Transaction Settings

Admin: **Card Management → Transaction Settings**, `/admin/card-management/transaction-settings`. Only Admin can view or save global settings. Cash-out and Send Money have separate save buttons on one page. Cash In is reserved, remains disabled and has no update endpoint yet.

All settings live in `membership_transaction_settings`, with a unique `key`, nullable JSON `value`, and timestamps. Values retain their types: enabled is boolean, monetary limits/rates are decimal strings, hold duration and ledger/user IDs are integers, and unselected ledger IDs are null.

```php
membership_transaction_setting('cashout.maximum_amount'); // e.g. "25000.00"
membership_transaction_setting('cashout.enabled');        // boolean
membership_transaction_setting('transfer.charge_rate');
membership_transaction_setting('unknown.key', 'fallback');

// For services/APIs that need a coherent snapshot of several settings:
$cashout = membership_transaction_settings('cashout');
$transfer = membership_transaction_settings('transfer');
```

The single-key helper queries the authoritative table. The group helper reads all keys in a single query to avoid mixing configuration versions while an admin saves. Neither uses persistent/static caches. Admin writes validated, whitelisted keys atomically through `MembershipTransactionSetting::writeGroup()`. Cash-out updates do not change transfer settings. Transfer posting user is set server-side to the saving admin; it cannot be supplied in the form.

Keys:

- `cashout.enabled`, `cashout.maximum_amount`, `cashout.charge_type`, `cashout.charge_rate`, `cashout.hold_minutes`, `cashout.income_head_id`
- `transfer.enabled`, `transfer.maximum_amount`, `transfer.charge_type`, `transfer.charge_rate`, `transfer.income_head_id`, `transfer.posting_user_id`
- `cashin.enabled` (false; reserved)

Migration `2026_11_11_090000_consolidate_membership_transaction_settings` copies the old singleton values (including enabled, fee policy, income heads and transfer posting user) before dropping both legacy settings tables. On rollback it reconstructs the old schemas using the current key-value settings. Fresh installs start disabled with zero limits/charges. Existing cash-out requests retain their stored amounts; new quotes use the current helper settings and configuration version. No member balances, transaction rows, vouchers or branch QR/counter configuration are changed.

Member API paths and response fields are unchanged, so Flutter continues to receive the same limit/charge fields. Financial integration tests use the key-value helper/store directly and continue checking fees, limits, idempotency, accounting and quote-version changes.
