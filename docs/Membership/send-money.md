# Member Send Money

Source and destination: each member must have exactly one Active Shomriddhi savings account. Recipient lookup accepts member number or a Bangladesh mobile number (01, +880, 880 and stored 10-digit variants); ambiguous matches and self-transfers are rejected. Recipient name and member number are displayed for review. PIN plus explicit final confirmation creates an immediate transfer, without branch approval.

## Administration

Card Management → Member Send Money shows completed transfers involving the active branch. Card Management → Send Money Settings (Admin only) controls enabled state, maximum net amount, fixed/percentage charge, and a global revenue ledger for charges. Charge zero means free transfers. Initially disabled; configure and enable explicitly.

Automatic accounting uses the admin who last saves these settings as its authorized posting user because existing voucher and Shomriddhi audit columns require an ERP user. Every transfer separately records the authenticated sending member, recipient, both savings account IDs and request key. This does not log the member in as the admin.

## Database and accounting

- `membership_transfer_settings`: singleton configuration and posting user.
- `membership_transfers`: immutable completed transfer audit, reference, sender/recipient account IDs, amount, fee, total debit, signed quote hash, idempotency key and linked vouchers/settlement.
- `shomriddhi_accounts`: sender principal minus amount+charge; recipient principal plus amount, inside one database transaction.
- `vouchers` / `transactions`: same branch Journal DR sender savings / CR recipient savings. Charge Journal DR sender savings / CR charge revenue. Purposes 28 and 29.
- Cross branch: source Journal DR sender savings / CR inter-company payable; destination Journal DR inter-company receivable / CR recipient savings. A completed `inter_company_settlements` row links both journals. Fee remains in the sender branch.
- `shomriddhi_transactions`: Approved sender withdraw and recipient deposit, plus an additional sender withdraw when charged. All linked to their respective journals. No cash ledger changes.

Supplier and account rows lock in ascending ID order; branch rows serialize voucher numbers. Recheck member/account status and savings availability, subtracting existing pending/accepted cash-out holds. Integer poisha arithmetic; percentage charges round half up. Signed quotes bind sender, recipient/account IDs, amount, charge and configuration version for two minutes. Sender+request key is unique and replays return the completed transfer even after quote expiry. Replay retrieval does not post or authorize new money movement; a fresh transfer always requires current PIN validation under the member lock.

Transfer journals are locked against ordinary voucher edits/deletes and Shomriddhi transaction reversal. A transfer cannot be reversed by deleting just one participant's entry; a future reversal workflow must post matching entries atomically.

## App and API

- GET `/api/membership/v1/transfers`: configuration, available savings and last 30 sent/received transfers, restricted to the authenticated member.
- POST `/api/membership/v1/transfers/quote`: recipient identifier and amount; returns recipient review data, net/charge/total and signed quote.
- POST `/api/membership/v1/transfers`: quote, six digit PIN and UUID idempotency key. Completed response includes reference and receipt data.

Flutter Dashboard Send Money and internal TORI transfer entry points use this API. Review then PIN then bilingual confirmation dialog. Before posting, persist signed quote and request UUID under a member-specific Send Money key; never persist PIN. A timeout prevents editing/starting another transfer until refreshing history or retrying the same request resolves the outcome. Cash-out retry state uses a separate storage key.

## Verification

Integration tests use DatabaseTransactions (rollback), never schema resets. Cover same/cross branch balancing, charges, PIN, self-transfer, holds, quote expiry, idempotency, API history ownership and locked reversal. Flutter widget tests verify recipient/charge review, cancel does not post, confirmation posts once and ambiguous retries preserve the key without PIN storage.
