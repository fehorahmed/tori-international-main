# Tori International — Project Context & Developer Handoff

Last reviewed: 2026-11-09. ভবিষ্যৎ coding session এই ফাইল থেকে শুরু করা যাবে। Current source code wins when it disagrees with this document. Update this file after structural or business-rule changes.

## Review scope and current state

Repository-wide inventory covered application, routes, resources, migrations/seeders, configuration, socket service, tests and existing Markdown documentation. Core routing, authentication, branch context, accounting models, business workflows, frontend bootstrapping and run configuration received focused review. This is an architecture handoff, not an exhaustive correctness/security audit of every method or bundled asset.

At review time: 475 application files, 51 route files, 950 resource files, 170 database files, 13 configuration files, 5 socket files and 11 test files. Dependencies, uploads and generated output are not useful first-party review targets.

- Earlier in this session the Laravel homepage and Vite JavaScript entry returned HTTP 200. That did not verify every API, browser interaction, login or financial workflow.
- Laravel and Vite were stopped at the user's request. They were not restarted for this review.
- No application logic or database records changed during this documentation task. No migrations, seeders, financial cron commands or database-backed test suite were executed.
- Root README is mostly Laravel boilerplate; use this document for application-specific orientation.

## Product and stack

Modular monolithic ERP with an e-commerce storefront, mobile APIs and a separate realtime location service. Domains include branch operations, accounting, inventory/purchase/sales, CRM/monitoring, HR/payroll, savings/loans, cards and office administration.

| Layer | Implementation / locked version |
| --- | --- |
| Backend | PHP requirement `^8.3`; Laravel 13.6.0 |
| Admin UI | Blade, modular menus, page-specific JavaScript, Yajra DataTables |
| Storefront | Vue 3.5.34, Vue Router 5.0.7, Vuex 4.1.0, Axios |
| Asset tooling | Vite 5.4.2, Vue plugin 6.0.6, Tailwind CSS 3.4.10 |
| Authentication | Sessions, separate customer guard, Sanctum 4.3.1 |
| Authorization | Scalar user role plus Spatie Permission 6.25.0 |
| Realtime | Express/Socket.IO, mysql2 pool, optional Redis bridge |
| Tests | Pest 4.6.3 / PHPUnit 12.5.23; mostly starter auth/profile tests |
| Other | Intervention Image v3/GD, Maatwebsite Excel, Google Auth |

Versions come from lockfiles, not upstream latest-version claims. Keep Composer/root npm/socket npm lockfiles versioned.

## Bootstrap and request flow

- [bootstrap/app.php](bootstrap/app.php): web/API/console routes, `/up`, middleware aliases, e-commerce session middleware, admin login redirects.
- [bootstrap/providers.php](bootstrap/providers.php): application and cart/compare/wishlist providers.
- [routes/web.php](routes/web.php): storefront `/`, ERP routes inside `/admin`, public card verification and mini-statements, final Vue catch-all.
- Root/catch-all handlers query `Ecommerce\Setting` for `primary_color`; PHP running alone is insufficient if the database/schema is missing.
- User agents containing `TORI_INTERNATIONAL_APP` redirect to admin login in root/admin-root handlers.
- [routes/auth.php](routes/auth.php) is included inside `/admin`; ERP login is `/admin/login`. Storefront `/login` is a Vue customer page.
- [routes/api.php](routes/api.php) mounts employee login/location APIs, social lead webhooks, e-commerce APIs and membership APIs, all beneath `/api`.
- [routes/ecommerce_api.php](routes/ecommerce_api.php): session-backed storefront endpoints, customer-protected account/orders/wishlist, guest cart/compare and buy-now.
- [routes/api/tori_membership.php](routes/api/tori_membership.php): `/api/membership/v1/...`.

## Module map

Most domains have corresponding controller/model namespaces and snake_case Blade view folders. Route includes determine reachability.

| Domain | Route entry | Main responsibility |
| --- | --- | --- |
| Branches | [branch_management.php](routes/branch_management.php) | Sister concerns, managers, financial reports, business date |
| Accounts | [accounts workspace](routes/accounts_workspace/accounts_workspace.php) | Account groups/heads, vouchers, receipt/payment, reports, fixed assets |
| CRM | [CRM workspace](routes/crm_workspace/crm_workspace.php) | Leads/clients, monitoring, SR visits, route plans, incentives |
| HR | [HR workspace](routes/hr_workspace/hr_workspace.php) | Attendance/shifts/leave/employees, payroll, contractors |
| Office | [office workspace](routes/office_workspace/office_workspace.php) | Notices/meetings, users/roles, managers, trainees, company loans, SMS, forms |
| Purchase | [purchase workspace](routes/purchase_workspace/purchase_workspace.php) | Purchasing, inventory, utilization, transfers, conversions |
| Sales | [sales workspace](routes/sales_workspace/sales_workspace.php) | Retail/POS, dealer/corporate sales, pre-orders, service jobs, storefront administration |
| Returns | [returns_management.php](routes/returns_management.php) | Sales/purchase returns, replacements and reversals |
| Shomriddhi | [Shomriddhi workspace](routes/shomriddhi_workspace/shomriddhi_workspace.php) | Advance savings, DPS, FDR, loans, cards |
| Legacy MFI | [microfinance.php](routes/microfinance.php) | Somiti/members, savings/loans/installments/collection/day closing; direct web include currently commented out |
| Employee profile | [employee_profile.php](routes/employee_profile.php) | Education, experience, documents |
| Storefront | [ecommerce_api.php](routes/ecommerce_api.php) | Products, categories, reviews, cart, coupons, orders, customer account |
| Membership mobile | [tori_membership.php](routes/api/tori_membership.php) | OTP/PIN/biometric auth, dashboard, profile |

Shared endpoints: [routes/common.php](routes/common.php). Settings and dashboards also have dedicated root route files. Verify actual controller imports before editing similarly named domains.

## Identity, permissions and branch isolation

- [User](app/Models/User.php) is the ERP/employee identity: Sanctum, Spatie roles/permissions and soft deletes.
- [Supplier](app/Models/Supplier.php) is a shared party/customer identity, not just a purchase supplier. It is authenticatable and token-capable, with purchases/sales/account heads/MFI/cards/Shomriddhi relationships.
- [config/auth.php](config/auth.php): `web` guard uses User; `customer` session guard uses Supplier. Avoid mixing default-user and customer-guard contexts.
- Admin middleware combines `auth`, scalar `role_check`, `verified`, active-user status and OTP-session expiry. Route-level Spatie permissions and method-level checks add restrictions.
- [RoleMiddleware](app/Http/Middleware/RoleMiddleware.php) checks scalar `user.role`, distinct from Spatie role assignments. Updating a role can require both representations to stay consistent.
- `User::activeConcernId()` reads session `active_sister_concern_id`, falling back to assigned branch. Use it for ERP operational branch context.
- [BranchSwitchController](app/Http/Controllers/BranchSwitchController.php): Admin can switch branches; Area Manager can switch only to an assigned branch; other roles are rejected.
- `User::business_date` derives from active branch. [CheckBusinessDate](app/Http/Middleware/CheckBusinessDate.php) currently passes through, so its existence does not establish date enforcement.
- Isolation is implemented in queries/action checks, not a universal model global scope. Check individual-record ownership as well as list filters.
- Global-party/account architecture and origin-branch filters coexist. Some supplier dropdowns still use `origin_sister_concern_id`; do not remove all filters by assumption.

### Mobile auth

Cash-out counter and its dedicated settings page are under Card Management (`card-management.cashout.*`). QR cash-out now uses existing Shomriddhi savings with expiry-aware holds, configurable fixed/percentage charges, branch counter approval/payment and balanced cash/charge/inter-branch postings. See [cash-out implementation and database map](docs/Membership/cashout.md), including the additive migrations and discovered obsolete MySQL trigger repair. Configure limits, fees, income ledger and branch cash counter before enabling; no live fee/limit is guessed.

Membership login now remembers verified installations: OTP is required for new/unrecognized devices, with subsequent phone/PIN login using a hashed server-side device credential. PIN recovery still requires OTP. Read [device login](docs/Membership/device-login.md) for the additive migration, cache binding and compatibility details.

Membership profile/KYC now supports profile editing, private selfie/NID submissions and manual ERP review. Read [profile/KYC implementation](docs/Membership/profile-kyc.md) for routes, the additive migration, branch/permission rules and targeted tests. The older mobile summary below describes authentication; the membership route file also includes profile update and KYC document routes.

Employee `/api/auth/login` accepts login/password/device name and issues a User Sanctum token; logout and monitoring use `auth:sanctum`.

Membership flow: mobile OTP → OTP verification → short-lived verification token → six-digit PIN set/login → Supplier Sanctum token. Biometric login/toggle also exists. Guest auth endpoints have `throttle:10,1`. See [membership AuthController](app/Http/Controllers/API/Membership/AuthController.php), [CustomerDevice](app/Models/CustomerDevice.php), [CustomerOtpLog](app/Models/CustomerOtpLog.php). Preserve token-owner distinctions in protected handlers.

## Accounting and inventory invariants

- [AccountHead](app/Models/Account/AccountHead.php), [Voucher](app/Models/Account/Voucher.php), [Transaction](app/Models/Account/Transaction.php) form the shared GL.
- Transaction types: debit `1`, credit `2`. Voucher types: journal `1`, payment `2`, collection `3`, contra `4`. Payment modes: none `0`, bank `1`, cash `2`, mobile banking `3`. Reuse [enumerations](app/Enumeration).
- Account-head balances are transaction-derived. `getOpeningBalance()` selects opening entries; `getCurrentBalance()` sums entries with account nature and optional branch filtering. Omitting branch aggregates globally.
- Cash/bank dropdowns commonly combine global heads (`sister_concern_id IS NULL`) and active-branch heads. Operational vouchers/transactions retain branch context.
- `isSufficient()` checks balances for cash/bank/mobile heads. `Voucher::is_locked()` protects module-linked vouchers from generic edits.
- Reserved account mappings, balance helpers and document numbering live in [helpers.php](app/Helper/helpers.php), enumerations and [seed data](database/seeders/data).
- Preserve balanced debit/credit postings, database transactions, approval/rejection transitions and complete reversals. Many models soft-delete; inspect deletion semantics before changing them.
- [SalesController](app/Http/Controllers/Sales/SalesController.php): POS/search, sale lifecycle, receipts, accounting entries and inventory-log helpers. Dealer/corporate/pre-order/service have separate controllers.
- [PurchaseOrderController](app/Http/Controllers/Purchase/PurchaseOrderController.php): items, inventory/batches/logs, GL, supplier payments and undo/recreate updates.
- Inventory is product/warehouse/branch scoped with batch/barcode data. Quantity changes need matching [InventoryLog](app/Models/Inventory/InventoryLog.php) and relevant GL effects.
- [return services](app/Services/ReturnsManagement) handle inventory and accounting reversal/replacement; returns are not simple stock edits.
- Legacy MFI [DayClosingController](app/Http/Controllers/Microfinance/DayClosingController.php) aggregates operational activity into GL. Newer Shomriddhi services post through approval workflows; do not conflate these architectures.

## Important services

| Area | Entry points and behavior |
| --- | --- |
| Advance savings | [ShomriddhiAdvanceService](app/Services/ShomriddhiAdvance/ShomriddhiAdvanceService.php): approve/reject/delete and GL; uses [repository](app/Repositories/ShomriddhiAdvance), bound in [AppServiceProvider](app/Providers/AppServiceProvider.php) |
| Savings interest | [InterestCalculationService](app/Services/ShomriddhiAdvance/InterestCalculationService.php), [DynamicInterestSlabService](app/Services/ShomriddhiAdvance/DynamicInterestSlabService.php), [CapitalizationService](app/Services/ShomriddhiAdvance/CapitalizationService.php) |
| FDR/DPS | [FdrAccountService](app/Services/ShomriddhiFdr/FdrAccountService.php): approval/deposit/encashment GL; [DpsAccountService](app/Services/ShomriddhiDps/DpsAccountService.php): installments, approval, penalties and encashment |
| Loans | [Shomriddhi loan controllers](app/Http/Controllers/ShomriddhiLoan) are separate from [legacy MFI services](app/Services/Microfinance) |
| Payroll | [SalaryService](app/Services/Payroll/SalaryService.php): attendance/overtime, preview/process, vouchers; employee advance has a separate service/repository |
| Incentives | [IncentiveCalculationService](app/Services/IncentiveManagement/IncentiveCalculationService.php): earned/payment/return ledger and GL; [CalculateIncentiveJob](app/Jobs/CalculateIncentiveJob.php) |
| Fixed assets | [DepreciationService](app/Services/FixedAsset/DepreciationService.php): depreciation preview/process |
| SMS | [SmsService](app/Services/SmsManagement/SmsService.php): active DB gateway/template, strategy send, logs; [SendSmsJob](app/Jobs/SmsManagement/SendSmsJob.php); SSL Wireless strategy implemented |
| Uploads | [UploadTrait](app/Traits/UploadTrait.php): public uploads, WebP via GD, file deletion; distinct from Laravel public-disk storage |

Firebase notifications, social lead webhooks, Google Analytics and geocoding also have implementation classes. Actual integrations require credentials and/or database settings. Verify caller wiring instead of assuming older docs' observer/listener descriptions match current code.

## Frontend working map

- Shell: [ecommerce/app.blade.php](resources/views/ecommerce/app.blade.php). Boot: [app.js](resources/js/app.js), which registers Vue/store/router/head/toasts, fetches customer state, then mounts `#app`.
- [router.js](resources/js/router.js): history routing, products/details/blog/gallery, login/register/reset, account, checkout/orders, wishlist/compare and fallback. Auth guards use Vuex customer state.
- [axios.js](resources/js/axios.js): base URL `/`, credentials enabled. [store modules](resources/js/store/modules): auth/cart/compare/home/order/product/detail/wishlist.
- [App.vue](resources/js/App.vue), [components](resources/js/components), [views](resources/js/views) implement storefront; ERP primarily uses Blade.
- [vite.config.js](vite.config.js): CSS and JS entries, Vue plugin, `@` alias for `resources/js`.
- Admin layout: [app.blade.php](resources/views/layouts/app.blade.php); [modular menus](resources/views/layouts/menu/modular), [sidebar map](resources/views/layouts/partial/__sidebar_modular.blade.php), [quick apps](resources/views/layouts/partial/__quick_apps.blade.php).
- Legacy menus and copied Vue files coexist. Confirm imports/includes before editing a similarly named file.

## Run/setup reference

Use local environment values; never put secrets in documentation. For a new installation only, copy the environment example if `.env` is absent and generate an app key only for that new installation.

```bash
composer install
npm ci
mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/logs bootstrap/cache
```

Configure database and local app URL first. Schema/setup work must target a disposable or explicitly approved database; seeders below are destructive.

Separate terminals:

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev -- --host 127.0.0.1
```

App: `http://127.0.0.1:8000`; Vite normally `http://127.0.0.1:5173`. Production assets: `npm run build`; Laravel then needs the generated manifest. Avoid stale `public/hot` pointing to a stopped Vite server.

Only when the target feature needs them:

```bash
php artisan queue:work
php artisan schedule:work
cd socket
npm ci
node server.js
```

Socket has no start script; actual entry is `server.js`, despite package metadata. Its test script is a failing placeholder. Default port is 3000; it reads root environment configuration.

Earlier startup in this session required missing runtime directories and root npm dependencies. PHP 8.3.36 was sandbox-visible; Node 24.21.0/npm were available through NVM outside the sandbox. Port binding/npm access required tool escalation. These are workstation observations, not portable project requirements. Socket/workers/scheduler were not started or verified.

## Configuration/integrations

| Concern | Keys / code |
| --- | --- |
| Application | `APP_ENV`, `APP_DEBUG`, `APP_KEY`, `APP_URL`, `APP_TIMEZONE`; [app config](config/app.php) |
| DB | `DB_CONNECTION`, host/port/database/username/password; [database config](config/database.php) |
| State/background | `SESSION_DRIVER`, session cookie/domain/secure settings, `CACHE_STORE`, `QUEUE_CONNECTION` |
| Storage | `FILESYSTEM_DISK`, optional AWS keys; [filesystems config](config/filesystems.php), UploadTrait |
| Realtime | Laravel `SOCKET_URL`; socket `PORT`, `BROADCAST_DRIVER`, Redis/DB keys |
| Geo/analytics | `MAPBOX_API_KEY`, `LOCATIONIQ_API_KEY`, `OPENCAGE_API_KEY`, `GA4_PROPERTY_ID` |
| Social leads | `FB_VERIFY_TOKEN`, `FB_PAGE_ACCESS_TOKEN`, `WA_VERIFY_TOKEN`, `IG_VERIFY_TOKEN` |
| Messaging/backup | Mail settings, DB SMS settings, Firebase code/settings, `MYSQLDUMP_PATH` |

Environment example is not a complete integration specification. Local app/cookie configuration must match local origin. Socket uses MySQL directly, so SQLite alone cannot exercise it. No secret values are recorded here.

## Scheduler and realtime behavior

Active schedule: [routes/console.php](routes/console.php), using configured application timezone.

| Command | Timing |
| --- | --- |
| `attendance:check-hourly` | Every five minutes |
| `shomriddhi:accrue-daily-interest` | Every five minutes, 23:00–23:20, without overlapping |
| `shomriddhi:capitalize-monthly-interest` | Every five minutes, 23:20–23:40, last day of month, without overlapping |
| `shomriddhi-fdr:process-interest` | Every five minutes, 23:30–23:45, without overlapping |
| `shomriddhi-dps:check-missed-installments` | Every five minutes, 23:40–23:59, without overlapping |

Database backup and legacy MFI daily-cron schedules are commented out. Financial cron commands mutate data; inspect idempotency before manually running them.

[socket/server.js](socket/server.js) handles branch-room joins and direct location updates, broadcasts immediately and saves asynchronously through [locationService](socket/services/locationService.js). It also exposes `/broadcast-location`; Redis bridge runs only when `BROADCAST_DRIVER=redis`. Laravel monitoring APIs are a separate ingestion path. Current socket handlers trust incoming user/branch payloads and allow wildcard CORS; review auth/ownership/broadcast scope before external exposure.

## Known limitations and follow-up items

Source observations, not fixes performed in this task:

1. **Destructive seeding:** [InitialSetupSeeder](database/seeders/InitialSetupSeeder.php) truncates users, permissions, branches, warehouses, settings and a permission pivot. [DatabaseSeeder](database/seeders/DatabaseSeeder.php) calls it. Never casually run `db:seed` on an existing database. Default provisioning credentials remain in source and are not repeated here.
2. **Test DB isolation:** [Pest.php](tests/Pest.php) applies `RefreshDatabase` to Feature tests, while SQLite overrides in [phpunit.xml](phpunit.xml) are commented out. Configure an isolated test DB before running Feature tests.
3. **Starter-test drift:** tests target unprefixed `/login`, `/logout`, `/profile`; ERP is under `/admin`, and storefront paths may resolve to Vue shell. Existing tests do not establish ERP correctness.
4. **Documentation drift:** [advance docs](docs/ShomriddhiAdvance/shomriddhi_advance.md) mention `shomriddhi_advances`; source uses `ShomriddhiTransaction` / `shomriddhi_transactions`. Trust current models/migrations.
5. **Legacy MFI reachability:** direct microfinance web-route include is commented out. Existing module code/docs do not establish an active web route.
6. **Historical migration plan:** [global plan](global_architecture_migration_plan.md) is unchecked; current code already implements some transaction-derived/global behavior, while some dropdowns retain origin-branch filtering. Do not execute its checklist wholesale.
7. **Public maintenance/debug surfaces:** web routes expose `/clear` without auth; [phpinfo.php](public/phpinfo.php) exists. Review before deployment; neither was invoked/removed here.
8. **Distributed financial logic:** older large controllers and newer services/repositories coexist. Monetary migration columns mix double and decimal. Preserve semantics until deliberate migration/refactoring.
9. **Verification limits:** no full production build, Feature tests, schema migration, complete browser flow or external integration validation occurred in this documentation pass.

## Future development checklist

1. Read this file, inspect current Git changes, then open target route/controller/model/view/service/migration. Recheck observations that may have changed.
2. For restructuring follow [MODULE_REFACTORING_GUIDE.md](MODULE_REFACTORING_GUIDE.md): namespaces, routes, permissions, sidebar map, quick apps and dedicated module dashboard must stay aligned.
3. Put static routes before generic resource/dynamic routes; keep storefront catch-all last.
4. Preserve route-level permissions and method-level authorization. Local refactoring guide calls for inline checks rather than constructor middleware.
5. Keep correct auth guard, branch ownership, business date and status transitions explicit. Reuse target module services/enumerations.
6. Financial changes need balanced GL, stock/log alignment, rejection/reversal, duplicate-processing and cross-branch checks on an isolated database.
7. Storefront changes should inspect Vuex calls and API endpoints together; admin changes should inspect Blade scripts and route names.
8. Use relevant checks: targeted PHP syntax checks, `php artisan route:list`, `npm run build`, isolated targeted Pest tests. Some Artisan commands bootstrap DB services; inspect config first.
9. Update this document when architecture/setup/routes or verified limitations change. Clearly distinguish source observations from tested runtime results.

## Existing detailed documentation

- [Module refactoring guide](MODULE_REFACTORING_GUIDE.md)
- [Accounting tree](accounts.md)
- [Legacy microfinance](microfinance.md)
- [Global accounts/parties plan](global_architecture_migration_plan.md)
- [Mobile ecosystem plan](Mobile_App_Ecosystem_Architecture.md): includes future apps/endpoints beyond currently implemented APIs
- [Cards](docs/CardManagement/card_management.md)
- [Fixed assets](docs/FixedAssetManagement/fixed_asset_management.md)
- [Shomriddhi advance](docs/ShomriddhiAdvance/shomriddhi_advance.md)
- [Shomriddhi FDR](docs/ShomriddhiFdr/shomriddhi_fdr.md)
- [SMS](docs/SmsManagement/sms_management.md)

Module documentation folders also contain explanation documents. Compare those with current source before relying on table names or implementation details.
