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

- `membership.registration_fee`, `membership.account_hold_amount` — non-negative Tk amounts, decimal strings, initially `"0.00"`. A separate Membership section saves these two fields independently. Access through `membership_transaction_setting('membership.registration_fee')` and `membership_transaction_setting('membership.account_hold_amount')`, or the `membership` group helper.
- `cashout.enabled`, `cashout.minimum_amount`, `cashout.maximum_amount`, `cashout.charge_type`, `cashout.charge_rate`, `cashout.hold_minutes`, `cashout.income_head_id`
- `transfer.enabled`, `transfer.minimum_amount`, `transfer.maximum_amount`, `transfer.charge_type`, `transfer.charge_rate`, `transfer.income_head_id`, `transfer.posting_user_id`
- `cashin.enabled` (false; reserved)

Migration `2026_11_11_090000_consolidate_membership_transaction_settings` copies the old singleton values (including enabled, fee policy, income heads and transfer posting user) before dropping both legacy settings tables. On rollback it reconstructs the old schemas using the current key-value settings. Fresh installs start disabled with zero limits/charges. Existing cash-out requests retain their stored amounts; new quotes use the current helper settings and configuration version. No member balances, transaction rows, vouchers or branch QR/counter configuration are changed.

Member API paths and response fields are unchanged, so Flutter continues to receive the same limit/charge fields. Financial integration tests use the key-value helper/store directly and continue checking fees, limits, idempotency, accounting and quote-version changes.

Minimum amounts default to Tk 1.00 to preserve the previous floor. Migration 2026_11_11_100000 inserts only missing minimum keys. Admin can configure a positive amount down to Tk 0.01; minimum cannot exceed maximum. Limits apply to the transaction amount before charges, inclusively. Minimum is included in quote-version checks, API configuration/quote responses, and Flutter range guidance/validation.

Migration `2026_11_14_090000_add_membership_fee_and_hold_settings` inserts only missing membership keys. Admin accepts 0–10,000,000 Tk with at most two decimals. Registration fee collection is enabled for newly created customers with a positive fee; select its active global income ledger in the Membership section.

`membership.account_hold_amount` is the protected minimum principal balance for every member's Shomriddhi savings account. Spendable balance is `max(0, principal - membership minimum - active cash-out holds)`. Charges count towards spending. Cash-out quotes/requests/acceptance/completion, Send Money quotes/posting and ordinary admin withdrawals enforce this rule. Cash-out completion excludes its own reservation while retaining all other holds. The latest minimum is rechecked under the account lock before posting, so increasing the setting also protects existing quotes and pending/accepted cash-out requests; a blocked request can still be rejected or cancelled through its normal flow.

The savings model also rejects any balance decrease below the minimum plus active holds, protecting deposit reversals, card collection reversals and future payment code that saves/decrements the savings model. Existing accounts below the configured minimum can receive deposits/transfers and accrue interest, but cannot spend until sufficient funds exist. This setting does not debit or move funds, automatically top up accounts, or create a product-payment feature. A zero setting disables the membership minimum while retaining existing cash-out holds. Do not bypass the model with raw/bulk balance updates when implementing new payment paths.

Dashboard `minimum_savings_balance` and cash-out configuration `minimum_balance` expose the configured minimum. Dashboard `available_savings_balance` and cash-out/transfer `available_balance` use the spendable amount. Flutter main balance uses total `savings_balance`.

## One-time registration fee

New `Customer` supplier records snapshot the current positive `membership.registration_fee` into `membership_registration_fees` (unique customer ID). Existing customers and customers created while the fee was zero are exempt; changing the setting does not retrospectively enroll them or change a pending fee amount. No historical customers are backfilled.

Approved ordinary cash/bank savings deposits and incoming membership transfers call `RegistrationFeeService::collect` inside the funding transaction, after principal and funding journal postings. It locks the account and fee record and charges only if the entire fee fits in available savings after the current minimum and active cash-out holds. Smaller deposits accumulate without partial charges. The fee is charged once with a balanced origin-branch journal: debit customer savings liability, credit configured global registration fee income ledger (`membership.income_head_id`). It creates an Approved withdrawal log and records the paid voucher/time atomically. Retries do not charge again. Invalid ledgers roll back funding and fee together with a configuration error.

Fee vouchers are locked against generic voucher edit/delete and ordinary withdrawal deletion; fee history is a separate bilingual entry. Registration itself does not create/fund savings accounts. Opening balances, interest capitalization, reversals and promotional card bonuses are not cash-in and do not trigger collection. The dedicated member Cash In workflow remains unimplemented; it must call the same collector within its funding transaction when added. Pending fees are not retroactively charged simply by editing settings or reading a balance.
