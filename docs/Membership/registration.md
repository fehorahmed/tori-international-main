# Mobile self-registration

The membership app now registers new customers through public, rate-limited `/api/membership/v1/auth/register/*` endpoints. Website password and app PIN are separate credentials. Existing website registration/login and mobile login endpoints remain compatible.

## Flow

1. `GET auth/registration/options`: active branch `id`, `name`, `code`.
2. `POST auth/register/initiate`: `name`, normalized BD `mobile_no`, active `branch_id`, `password`, `password_confirmation`, accepted `terms_accepted`, installation `device_id`; optional `email`, `referral_code`, `device_name`, `platform`. A nonblank referral code requires `referral_type` (`member` or `agent`).
3. `POST auth/register/verify-otp`: `registration_token`, same `device_id`, 6-digit `otp`; returns `verification_token` valid for 15 minutes.
4. `POST auth/register/complete`: both tokens, same device, 6-digit `pin` and `pin_confirmation`; creates the customer, branch member number, trusted device, and Sanctum token atomically. Returns HTTP 201 with `data.token`, `data.device_token`, `data.customer`.

`POST auth/register/resend` accepts the registration token and device ID. Resend cooldown is 60 seconds; OTP lifetime is 3 minutes and verification allows five failed attempts before blocking further verification until resend. All routes use the existing public auth throttle `10,1`.

## Account behaviour

Registration creates an active `Customer` so the user can log in and submit profile/KYC. Registration now creates an Active zero-balance Shomriddhi savings account and its Credit savings ledger atomically, so the member can receive transfers immediately. It does **not** automatically issue a membership card, credit money, verify KYC, or pay a referral reward. Branch staff continue the existing card workflow. Existing completed app registrations missing savings provisioning can be audited with `php artisan membership:repair-registration-savings` and repaired with `--apply`; closed/deleted/ambiguous accounts and historical funded ledgers are never reopened or reset. The optional referral is validated against the selected source and persisted as a typed relationship in `membership_referrals`; no commission is posted. Member uses active Customer `suppliers.member_no`; Agent uses active `users.username`, regardless of employee linkage or role. Blank code means no referral. Duplicate active Member codes are rejected rather than selecting an arbitrary record. At completion the originally resolved ID is rechecked for active status under lock; it is not reassigned if a code changes owner. The one-row-per-customer constraint and registration replay guard prevent duplicate referrals. Old in-flight drafts with code but no resolved type/ID must restart registration.

KYC remains a separate existing flow: selfie from camera; front/back NID from camera/gallery; documents reviewed by admin. No DOB/profile-schema changes were introduced by registration.

## Storage and credentials

Apply `2026_11_13_100000_create_membership_registration_requests.php` and `2026_11_15_090000_add_membership_referrals.php` before deploying the endpoints. This table holds the draft, normalized unique mobile, hashed website password, hashed OTP, hashed registration/verification tokens, consent time, optional referral, and eventual customer ID. No customer exists before successful completion. After completion the draft's password hash and verification/OTP hashes are cleared; the customer's password and PIN remain individually hashed.

Existing normalized phone/email matches (including soft-deleted customers) are rejected rather than creating/replacing their accounts. Request-row locking and branch locking serialize completion/member numbering for this flow. Existing ERP/admin/website account creation paths retain their own legacy uniqueness rules; the entire shared suppliers table was not given a new global normalized-phone unique constraint.

A replay after successful completion returns 409 with a sign-in instruction; it never creates a second customer. If the success response was lost, use sign-in rather than starting another account. Registration tokens are not login access tokens.

## SMS

Direct transactional `SmsService::sendSms` now uses the configured active gateway and records an OTP-redacted log. Registration fails with 503 when delivery reports failure; it does not claim an OTP was sent. Actual production delivery still depends on the configured gateway and provider credentials. Local environment uses `123456` and returns `debug_otp`; testing skips actual SMS, and production does not expose the OTP.

## Validation

- Website password: 8–128 characters with confirmation.
- App PIN: exactly 6 digits with confirmation; preserve leading zeroes.
- Name max 255; optional unique email max 255.
- Branch must exist and remain active at completion.
- Referral max 64, device ID max 128, device name max 255, platform max 32.
- Mobile is normalized to `01XXXXXXXXX` and validated as a BD number.

## Verification

`MembershipRegistrationTest` uses real-database transactions that roll back fixtures. It covers no pre-OTP customer creation, normalized duplicates, consent/password confirmation, device binding, OTP failures/cooldown, expired proof, inactive branches, website password login, trusted-device login, replay, gateway failure, and redacted SMS logging. Flutter `registration_test.dart` covers the three-step UI contract and local validation with a mocked HTTP client. Tests never send production SMS or create permanent members.

## Registration fee

New customers snapshot a positive configured membership registration fee. No money is charged at signup. Approved savings deposits and incoming Send Money collect it once only when the complete fee can be paid while retaining the protected minimum balance and cash-out holds. The income ledger must be selected in Transaction Settings. See [transaction settings](./transaction-settings.md) for posting and exemption rules.

## Referrer in profile

`GET /api/membership/v1/profile` returns `referred_by: null` when no typed referral relationship exists, otherwise `{type: "member" | "agent", name: string | null, code: string}`. The code is the registration-time snapshot; the name is read by the saved referrer ID, including soft-deleted records for historical display. Referrer phone/email, internal IDs and credentials are not exposed. Own `member_no` remains separate. Flutter My Profile labels these as Your Referral ID and Referred by.
