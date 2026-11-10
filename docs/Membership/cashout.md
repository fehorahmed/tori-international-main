# QR cash-out: setup, lifecycle and database postings

Cash-out uses the member's existing **Shomriddhi savings principal**. Settings support a fixed Tk charge or a percentage charge. The member receives the requested cash amount; the charge is deducted **in addition**. Maximum amount applies to cash received, not cash plus charge. No daily limit, compulsory retained balance, automatic card issue or cash-in feature is introduced here.

## Setup

ERP: **Card Management → Membership Cash-out**, `/admin/card-management/cashout`. Global limits and charges: **Card Management → Cash-out Settings**, `/admin/card-management/cashout/settings`.

1. Admin saves the global maximum per transaction, charge mode/rate, pending expiry (1–60 minutes), and an active global revenue ledger for charge income. Enable cash-out. No production limit or fee is guessed; the feature starts disabled.
2. At each active branch, an operator selects the branch/global cash ledger and enables its counter. Print the locally generated QR. Replacing a QR invalidates the old code for new requests.
3. The cash ledger must have sufficient recorded branch cash. Cash-out uses cash heads (`payment_mode = 2`), not bank or mobile banking heads.

Viewing/configuring a counter requires the existing `shomriddhi_advance_transaction_approve` permission (Admin can also view/configure). Accept/payment requires that approval permission and the active payout branch. Reject requires `shomriddhi_advance_transaction_reject`. Global financial settings require scalar Admin role. New permissions or role assignments are not seeded.

Flutter: **Payments & Transfers → Branch Cash Out**. Camera permission is required; web camera scanning needs a secure browser context such as HTTPS/localhost. The scanner uses `mobile_scanner`; QR images are generated on the ERP server using `bacon/bacon-qr-code`.

## Lifecycle

1. Scan branch QR, enter amount, request a server quote. The encrypted quote binds customer, savings account, branch, amount, charge and settings version and expires in two minutes.
2. Review branch, cash, charge and total; confirm with the real six-digit PIN. The server checks available balance again under an account row lock.
3. `Pending`: cash plus charge is held. Principal and accounting ledgers remain unchanged. The member can cancel. Unaccepted requests expire after the configured interval.
4. `Accepted`: branch confirms it can pay. The savings hold remains; the cash amount is also reserved when checking further cash payments. Accepted requests do **not** expire automatically, and members cannot cancel them while a counter may be paying cash. Operator can reject/release them with a reason.
5. `Completed`: operator explicitly confirms cash payment. Ledger postings, principal debit and closing the hold commit in one DB transaction. A repeated completion returns the original result without posting again.

`Cancelled`, `Rejected`, and effective `Expired` release the savings hold. Expiry is evaluated by timestamp in balance queries and display; no financial cron is required. An expired database row may retain raw `Pending` status, so consumers must use `effectiveStatus()` and the expiry-aware hold query.

PIN validation and request creation are throttled. The client saves a request UUID and encrypted quote before submission; it never saves the PIN. Timeouts reuse that UUID across retries/restarts. The server stores a unique `(customer_id, idempotency_key)` and quote hash. A retry of a request already recorded returns the same request even after quote expiry. A different quote cannot reuse its key.

## Database map

| Table | Writes and purpose |
| --- | --- |
| `membership_cashout_settings` | Singleton global enable flag, per-request maximum, fixed/percentage charge/rate, expiry and charge income head. |
| `membership_cashout_booths` | One QR identifier and payout cash head per branch; enable/rotation settings. |
| `membership_cashouts` | Member/account/payout branch, immutable amount/fee/total and charge snapshot, idempotency/quote hashes, status/expiry, operator IDs/timestamps, reasons, links to all vouchers and settlement. Pending/accepted rows are the source of savings holds. |
| `shomriddhi_accounts` | Principal decreases exactly once by `cash + charge` at successful completion. Holds do not decrease principal. Accrued interest is not automatically capitalized or paid by this flow. |
| `shomriddhi_transactions` | Approved `withdraw` records for the savings debit, with cash-out reference and breakdown in notes. Same branch: cash withdrawal plus a separate charge withdrawal if nonzero. Cross branch: one gross withdrawal in the origin branch linked to its journal. |
| `vouchers` | Cash payment voucher for **net cash only**, separate noncash charge journal when applicable, plus origin journal for cross-branch payouts. Member savings head is linked as payee/depositor. |
| `transactions` | Balanced debit/credit entries for each voucher; branch, account head, payee head, date, operator and purpose are recorded. Purposes 26 = cash-out, 27 = charge. |
| `inter_company_settlements` | Cross-branch clearing record, origin→payout branch, gross amount, origin/payout vouchers, status 2 (received/posted). No physical cash is falsely posted in the origin branch. |

All monetary calculations and comparisons in the cash-out service use integer poisha. Percentage fees round half up to the nearest poisha. The existing ledger schema still contains floating-point amount columns; this feature does not migrate historical accounting columns.

### Example: same branch, Tk 1,000 cash + Tk 10 fee

Savings principal decreases Tk 1,010.

| Voucher | Debit | Credit |
| --- | --- | --- |
| Payment voucher, Tk 1,000 | Member savings liability 1,000 | Branch cash 1,000 |
| Charge journal, Tk 10 | Member savings liability 10 | Charge income 10 |

### Example: origin A, cash paid at B

| Branch / voucher | Debit | Credit |
| --- | --- | --- |
| A / origin journal, Tk 1,010 | Member savings liability 1,010 | Inter-company payable 1,010 |
| B / cash payment, Tk 1,000 | Inter-company receivable 1,000 | Branch B cash 1,000 |
| B / charge journal, Tk 10 | Inter-company receivable 10 | Charge income 10 |

Charge income belongs to the payout branch. Global inter-company receivable/payable heads (reserved IDs 4/5) must exist. The settlement's gross amount includes the fee; its payout voucher holds the net cash, with the additional fee journal linked from the cash-out record. These are accounting clearing entries, not a second cash transfer.

## Existing withdrawal integration

Legacy withdrawal approval now locks/rechecks the savings account and transaction, rejects spending reserved savings and checks branch cash after accepted cash-out reservations. Savings model updates/decrements also check holds and reject stale balance writes. Existing full-withdrawal interest-capitalization behavior remains in ordinary withdrawal; QR cash-out always pays the reviewed amount exactly.

Cash-out vouchers are locked in ordinary voucher edit/update/delete and in Shomriddhi transaction deletion, including direct endpoint calls. Completed cash-outs cannot be cancelled or reversed through these general screens; a separate audited reversal design would be needed for future disputes.

## Migrations and discovered legacy database issue

```sh
php artisan migrate --path=database/migrations/2026_11_10_090000_create_membership_cashout_tables.php
php artisan migrate --path=database/migrations/2026_11_10_090100_retire_obsolete_transaction_balance_triggers.php
```

Local MySQL had three stale `transactions` triggers trying to update `account_heads.current_balance`, although that column no longer exists and balances are transaction-derived. This broke transaction inserts, including legacy withdrawals. The second migration only removes those named obsolete triggers when the column is absent. Exact trigger bodies are backed up first to `storage/app/private/migration-backups/obsolete_transaction_balance_triggers.json`; `down()` restores those definitions. No historic transaction rows or balances are rewritten. Keep this private backup for rollback.

## API

- `GET /api/membership/v1/cashout`: settings, principal/held/available balance and latest 20 requests.
- `POST /api/membership/v1/cashout/quote`: `qr_code`, `amount`.
- `POST /api/membership/v1/cashouts`: `quote`, `pin`, UUID `idempotency_key`.
- `GET /api/membership/v1/cashouts/{id}` and `POST .../{id}/cancel`: owner only.

Members must have active customer identities and active savings accounts; employee tokens cannot use the member endpoints. Dashboard responses now include available and held savings separately, and Flutter uses available savings for spending.

## Verification

Targeted backend tests use rollback transactions with balanced test funding, never seed or refresh the schema. They exercise actual MySQL accounting, fixed/percentage rounding, limits and charge-inclusive balance checks, holds, expiry, cancellation/rejection, duplicate requests/completion, old withdrawal protection, cross-branch postings, owner/branch restrictions, settings/QR generation and failure recovery.

```sh
vendor/bin/phpunit tests/Integration/MembershipCashoutTest.php
flutter test --no-pub --dart-define-from-file=.env test/cashout_test.dart
```

Flutter tests verify server quote review, PIN submission, pending state and retry-key persistence without saving the PIN. They do not test a physical camera or an actual cash handover. Test counter QR scanning on a real phone before operational use.
