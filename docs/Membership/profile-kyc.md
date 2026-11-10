# Membership profile and KYC

Implemented for the Flutter membership app. Camera selfie is selected using `ImageSource.camera`; NID front and back each have separate camera and gallery actions. This is manual document review, not automated liveness, face matching or NID database verification. Physical-device camera permissions and capture behavior still require device testing.

## Member API

All routes require a customer/Supplier Sanctum token for an active non-supplier party. Employee tokens are rejected. Send `Accept: application/json` and `Accept-Language: en` or `bn`.

- `GET /api/membership/v1/profile`: profile, verification status, latest KYC and authenticated document URLs. No submission returns `Not submitted` and an empty documents object.
- `PUT /api/membership/v1/profile`: name, optional email and address. Identity, mobile, branch, card and balances cannot be changed here. Name changes after pending/verified KYC are handled by the branch. Other profile fields remain editable.
- `GET /api/membership/v1/card`: the member's active issued card, or latest issued card if none is active. Returns `data.card: null` if no card exists. Includes real card number/status, issuing branch, issue date, issue fee/payment state and last print timestamp. It does not create cards. Admin issuance remains unchanged. The app masks the card number initially and offers reveal/copy and refresh. Issue fee is not a wallet balance; no invented CVV, expiry or payment credentials are returned.
- `POST /api/membership/v1/kyc`: multipart `nid_number`, `selfie`, `nid_front`, `nid_back`. All three files are required on each submission; JPG/PNG/WebP, at most 5 MB each. NID accepts 10, 13 or 17 ASCII digits. Files are stored privately; successful submission returns HTTP 201 with `Pending` status. Pending submissions cannot be replaced. After rejection or approval a new complete submission is possible, retaining previous records.
- `GET /api/membership/v1/kyc/{id}/documents/{kind}`: authenticated binary image; only the document owner can read it. `kind` is `selfie`, `nid_front` or `nid_back`. Responses disable caching.

The live selfie is used in the app profile through the private document endpoint. It is not copied to public uploads. NID is copied to the ERP customer record only after approval.

## ERP review

Open **Shomriddhi Advance → Members → Membership KYC Review**, or `/admin/card-management/kyc`.

The existing `customer_edit` permission is required. Submissions and images are restricted to the active branch, including direct document requests. Reviewer can approve or reject; rejection requires a note. Review records store reviewer and timestamp. A decision cannot be applied twice. Members see the latest status and reviewer note and may submit again after rejection.

## Setup and verification

Run only the additive migration for this feature:

```sh
php artisan migrate --path=database/migrations/2026_11_09_190000_create_customer_kycs_table.php
```

The `kyc` disk uses `storage/app/private/kyc`, separate from public storage. Keep it private and back it up with the database. No storage symlink is required for these documents.

Targeted backend tests use database transactions and fake document storage. They need an accessible local MySQL database with the feature migration applied. They do not run seeders or refresh/drop the schema:

```sh
vendor/bin/phpunit tests/Integration/MembershipProfileTest.php tests/Integration/MembershipCardTest.php tests/Unit/MembershipLanguageTest.php
```

Flutter checks:

```sh
flutter test --no-pub --dart-define-from-file=.env test/profile_service_test.dart test/member_card_test.dart test/flows_test.dart
flutter build web --no-pub --dart-define-from-file=.env
```

These checks cover profile persistence, multipart field names, status mapping, required documents, owner/branch access, review and resubmission. They do not replace physical-device camera testing.
