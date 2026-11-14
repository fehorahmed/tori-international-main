# Mobile self-registration

The membership app now registers new customers through public, rate-limited `/api/membership/v1/auth/register/*` endpoints. Website password and app PIN are separate credentials. Existing website registration/login and mobile login endpoints remain compatible.

## Flow

1. `GET auth/registration/options`: active branch `id`, `name`, `code`.
2. `POST auth/register/initiate`: `name`, normalized BD `mobile_no`, active `branch_id`, `password`, `password_confirmation`, accepted `terms_accepted`, installation `device_id`; optional `email`, `referral_code`, `device_name`, `platform`.
3. `POST auth/register/verify-otp`: `registration_token`, same `device_id`, 6-digit `otp`; returns `verification_token` valid for 15 minutes.
4. `POST auth/register/complete`: both tokens, same device, 6-digit `pin` and `pin_confirmation`; creates the customer, branch member number, trusted device, and Sanctum token atomically. Returns HTTP 201 with `data.token`, `data.device_token`, `data.customer`.

`POST auth/register/resend` accepts the registration token and device ID. Resend cooldown is 60 seconds; OTP lifetime is 3 minutes and verification allows five failed attempts before blocking further verification until resend. All routes use the existing public auth throttle `10,1`.

## Account behaviour

Registration creates an active `Customer` so the user can log in and submit profile/KYC. It does **not** automatically issue a membership card, create a savings account, credit balance, verify KYC, or make a referral reward payment. Branch staff continue existing savings/card workflows. Referral text is retained in the registration audit record; it is not validated as an agent relationship or used to post commissions.

KYC remains a separate existing flow: selfie from camera; front/back NID from camera/gallery; documents reviewed by admin. No DOB/profile-schema changes were introduced by registration.

## Storage and credentials

Apply `2026_11_13_100000_create_membership_registration_requests.php` before deploying the new endpoints. This table holds the draft, normalized unique mobile, hashed website password, hashed OTP, hashed registration/verification tokens, consent time, optional referral, and eventual customer ID. No customer exists before successful completion. After completion the draft's password hash and verification/OTP hashes are cleared; the customer's password and PIN remain individually hashed.

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
