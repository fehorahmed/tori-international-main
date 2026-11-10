# Membership device-aware login

- New installations: phone → OTP → existing PIN or initial PIN setup.
- Remembered installations: phone → PIN; no login OTP is generated or sent.
- Forgot PIN: explicit OTP recovery, even on remembered installations. Resetting PIN clears previous device trust and remembers the installation completing recovery.

The Flutter client stores a random installation ID and a separate device secret per normalized phone number. The backend stores only SHA-256 hashes of device secrets. ID alone never bypasses OTP; both the device credential and correct PIN are required. Device trust is issued only after OTP verification and successful PIN login/setup. Clearing app data, browser storage or using a new installation requires OTP again. Existing device records without a trusted hash must verify once after this update. A normal logout retains device trust.

## Request/response changes

Send `device_id` and optional saved `device_token` to `POST auth/initiate`. Its `data.otp_required` determines whether Flutter opens OTP or PIN. `reset_pin: true` requests OTP recovery explicitly.

Send the same `device_id` to `verify-otp`. OTP caches and verification tokens are bound to the installation as well as the phone number. `verify-otp` returns `has_pin: false` for recovery so the app shows a new PIN and confirmation form.

`login-pin` accepts either an OTP `verification_token` bound to that phone/device, or remembered `device_id` + `device_token`. Successful OTP/PIN enrollment returns `data.device_token` together with the Sanctum access token. Subsequent PIN logins leave the existing device secret unchanged. Do not overwrite the saved secret with a null response.

`set-pin` requires the OTP verification token and matching device ID. Trusted-device proof alone cannot reset a PIN. Old clients without device fields may continue using OTP every time; they do not become remembered devices.

The login screen always authenticates against the backend; the former local PIN fallback has been removed. All auth routes retain their existing request throttle. Production SMS delivery and physical-device persistence need deployment/device checks; local/testing runs do not send real SMS.

## Migration and tests

```sh
php artisan migrate --path=database/migrations/2026_11_10_010000_add_trusted_token_to_customer_devices.php
vendor/bin/phpunit tests/Integration/MembershipDeviceLoginTest.php
```

Tests use rollback transactions and array cache. They do not reseed or refresh the database.

```sh
flutter test --no-pub --dart-define-from-file=.env test/device_login_test.dart
```
