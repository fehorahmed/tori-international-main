# Referral rewards

## Setup

Card Management → Transaction Settings → Referral Bonus & Commission:

- `referral.enabled`: false until configured; enables new rewards and member redemption.
- `referral.member_points`: whole points per successfully fee-paid referral; default 0. Admin chooses the award.
- `referral.point_value`: Tk per point; default 30.00, applies at redemption time.
- `referral.agent_bonus`: Tk per fee-paid agent referral; default 30.00.
- `referral.expense_head_id`: active global Debit expense head (top reserved group 5).
- `referral.payable_head_id`: active global Credit liability head (top reserved group 2), required for positive agent bonus.
- `referral.posting_user_id`: authenticated admin who last saved settings; never taken from client input.

Use dedicated referral expense and agent payable ledgers for clear accounting. Existing card issuance commission accounting is separate. Saving settings persists key/value records through the shared transaction settings helper. No existing balances or historical referrals are backfilled.

## Earning

Existing registration stores a permanent typed referrer ID with the submitted code snapshot. Code changes do not change that relationship.

After the existing registration fee service successfully deducts the complete fee (following approved deposit or incoming Send Money), it creates one reward in the same database transaction. Unique referral and fee IDs prevent duplicate awards. Insufficient funding, unpaid fee, no fee obligation or failed posting does not earn a reward. No reward is awarded at registration itself. Disabled/zero awards are recorded with zero value so later settings cannot retroactively award already collected fees.

Member points are snapshotted from the current settings when the fee is collected. They are non-cash points until redeemed; no automatic savings credit. Agent bonus amount, payable ledger and branch are snapshotted at accrual. Invalid configured agent ledgers roll back the fee and its funding transaction rather than create unbalanced accounting.

## Member redemption

Flutter: My Profile → Referral Points. Shows available points, current point value, value in Tk, referral/reward counts and redemption history. Member enters a whole point count and 6-digit PIN, reviews the amount and confirms. The backend checks active membership, PIN, one active savings account, sufficient unspent points and the current point rate.

Point redemption uses the current rate, with a rate change requiring a fresh confirmation. A unique request key plus member row lock prevents double credits and concurrent overspending. Flutter persists an uncertain request without its PIN and retries the exact payload. Repeated requests return the original redemption even if the rate changed after its completion.

Accounting in the savings account's originating branch: DR referral expense / CR customer savings. The principal and an Approved savings deposit log are updated atomically. Existing protected minimum balance and cash-out holds still govern spending the credited balance. Redemptions do not collect registration fees; those remain limited to the existing deposit/transfer triggers.

## Agent payment and report

Card Management → Referral Bonus & Commission is Admin-only. Summary by typed referrer shows new members, fees paid/pending, points earned/redeemed/available and agent commission earned/paid/pending. Details show code at registration, fee and reward status. Referrals without a fee obligation show Not applicable rather than Pending.

Fee collection accounting: DR referral expense / CR agent commission payable. The reward's agent ID identifies the beneficiary; voucher user ID identifies the posting operator.

Admin switches to the accrual branch, selects a funded cash/bank/mobile payment ledger and confirms payment in SweetAlert. Accounting: DR the original payable head / CR cash or bank. Paid-at, paying admin and payment voucher are recorded on the reward. Concurrent/repeated payments are serialized and do not pay twice. Inactive agents, another branch, inactive/wrong ledgers and insufficient cash (including accepted cash-out reservations) reject payment.

Referral vouchers (purposes 34/35/36) are protected from generic voucher edits/deletes and ordinary savings transaction deletion. Member redemption appears in recent transactions as Referral points redeemed.

## Storage and API

Migration: `2026_11_15_120000_create_membership_referral_rewards.php`.

- `membership_referral_rewards`: unique referral and registration fee, typed beneficiary, branch, awarded points or agent amount, original payable and accrual/payment voucher IDs, payment actor/time.
- `membership_point_redemptions`: member, unique per-member request key, points spent, rate/amount snapshot, savings account and voucher.

Authenticated membership API: `GET /api/membership/v1/referrals`, `POST /api/membership/v1/referrals/redeem`. Staff tokens cannot redeem member points. Responses never include another member's rewards or redemption keys.

## Milestone cash bonuses

Transaction Settings → Referral Milestone Bonus stores `referral_milestones.enabled` and `referral_milestones.tiers` (array of permanent UUID `id`, integer `threshold`, decimal `percentage`, boolean `enabled`). Default tiers: 100 / 25%, 200 / 50%, 300 / 100%, enabled when referral rewards are also enabled. Admin can add tiers or edit/disable existing ones; existing IDs remain permanently recorded even if omitted from an update. Changing a paid tier does not re-award it. Neither the same tier ID nor the same threshold can be paid to a member twice.

Progress = ordinary referral points earned minus points redeemed. Admission counts and milestone cash never contribute to progress. Redeeming points reduces progress. Bonus does not consume or add points.

Bonus Tk = threshold × percentage / 100 × current Tk per point, rounded once to the nearest paisa. At Tk 30/point the defaults pay Tk 750, Tk 3000 and Tk 9000. Crossing several thresholds together awards each newly qualified tier once, rather than only the highest tier.

After a positive ordinary member referral award, the system locks the referring member, uses current locked point/redemption records, records each earned bonus in `membership_milestone_bonuses`, and credits savings immediately. Each earned record snapshots qualifying point balance, threshold, percentage, point value and amount. Missing/inactive/ambiguous savings, missing actor, inactive branch or invalid expense setup leaves it pending without blocking the referred member's fee collection. Pending amounts remain unchanged by later point redemption, rate changes or tier edits. Pending collection requires overall referral/milestone rewards enabled. Turning a tier inactive prevents new awards but does not erase earned obligations.

Accounting: DR referral expense / CR member savings (purpose 37), principal credit and Approved deposit log atomically. These entries are protected from generic edits/deletes and ordinary savings transaction deletion. The existing account hold amount remains protected, and bonus credits do not trigger a new registration fee or referral award.

`GET /membership/v1/referrals` adds `data.milestones` with `enabled`, `tiers` and the member's `bonuses` history. Flutter shows progress and cash paid/pending. `POST /membership/v1/referrals/milestones/collect` (empty body, authenticated member, throttle 6/minute) checks current eligibility and retries pending credits; repeated calls do not double-pay. It also lets existing eligible members collect newly configured milestones. No other customer's ID or client-provided amount is accepted. GET remains read-only. Admin referral report adds milestone cash paid/pending per referring member.

Run the migration `2026_11_15_150000_create_membership_milestone_bonuses.php` before deploying the milestone code.
