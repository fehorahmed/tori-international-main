# Shomriddhi FDR Module Architecture

## 1. Overview
The **Shomriddhi FDR (Fixed Deposit Receipt) Module** manages fixed-term deposit accounts for customers. A customer deposits a lump sum for a fixed period (e.g., 6, 12, 24 months) and receives interest periodically (Monthly, Quarterly, Half-Yearly, Yearly) or at maturity — all automatically integrated with the General Ledger via double-entry accounting.

---

## 2. Design Patterns Applied

### Service Pattern (No Repository)
Unlike the Shomriddhi Advance module, FDR does not use the Repository pattern. The `FdrAccountService` acts as the sole business/accounting engine, delegating database interactions directly via Eloquent Models. Controllers remain thin, forwarding requests to the service.

---

## 3. Database Architecture

### `fdr_schemes`
Defines reusable FDR product configurations.
- `id`, `name`, `duration` (months), `interest_rate` (%), `min_amount`, `max_amount`, `status`

### `fdr_accounts`
Stores the complete lifecycle of each customer's FDR account.
- `id`, `fdr_no` (unique auto-generated code)
- `customer_id` → suppliers.id
- `scheme_id` → fdr_schemes.id
- `origin_sister_concern_id`
- `principal_amount` — the one-time lump sum deposit
- `opening_date`, `maturity_date`
- `payable_amount_at_maturity` — pre-calculated total (Principal + Interest)
- `interest_payout_frequency` (enum: Monthly / Quarterly / Half-Yearly / Yearly / At Maturity)
- `next_interest_date` — updated automatically after each payout
- `auto_renew` (boolean) — auto-renew on maturity
- `status` (enum: Pending / Active / Matured / Closed)
- `approved_by`, `approved_at`, `created_by`
- *Timestamps & SoftDeletes*

### `fdr_transactions`
Logs every financial event on an FDR account.
- `id`, `fdr_account_id`, `type` (Deposit / Interest Payout / Encashment / Premature Encashment)
- `amount`, `date`, `note`, `voucher_id`, `created_by`

### `fdr_nominees` (Pivot)
- `fdr_account_id`, `nominee_id`, `share_percentage`

---

## 4. Core System Components

### 4.1. Controllers (`App\Http\Controllers\ShomriddhiFdr\`)
| Controller | Responsibility |
|---|---|
| `FdrDashboardController` | High-level metrics and module overview |
| `FdrSchemeController` | CRUD for FDR product schemes |
| `FdrAccountController` | Account create/edit/approve/encash/delete + encashData endpoint |
| `FdrTransactionController` | View transaction history per account |
| `ReportController` | All reports (Active, Matured, Closed, Ledger, Transaction, Scheme Summary) |

### 4.2. `FdrAccountService` (Central Engine)
The business and accounting engine. All GL posting logic lives here, keeping controllers clean.

**Methods:**
- `approve(FdrAccount, paymentAccountHeadId)` — Activates account, posts initial deposit voucher
- `encash(FdrAccount, data[])` — Closes account, posts encashment/premature withdrawal voucher

### 4.3. `ProcessFdrInterestPayout` (Cron Job)
Console command: `php artisan shomriddhi-fdr:process-interest`
- Scheduled daily at **01:30 AM**
- Scans all Active FDR accounts where `next_interest_date <= today` and `frequency != 'At Maturity'`
- Calculates and transfers interest to the customer's linked Shomriddhi Savings Account
- Posts a Journal Voucher (DR: FDR Interest Expense / CR: Customer Savings Head)
- Handles **Maturity Detection**: sets status to `Matured` or triggers `Auto-Renew`

---

## 5. Account Head Strategy (GL Integration)

| Head Type | `head_type` | Group | Nature |
|---|---|---|---|
| Customer FDR Liability | `fdr` | CUSTOMER_FDR (Liabilities) | Credit |
| FDR Interest Expense | `primary` (is_system=1) | FINANCIAL_EXPENSES | Debit |
| Customer Savings | `savings` | CUSTOMER_SAVINGS | Credit |

- Customer FDR Head is created **on approval** via `FdrAccountService::postDepositVoucher()`
- Customer Savings Head is created **when Shomriddhi Advance account is opened**
- FDR Interest Expense Head is created **on first interest payout** by the cron job if missing

---

## 6. Complete Workflow

### Step 1: Scheme Setup
Admin creates FDR Schemes (name, duration, rate, min/max amount).

### Step 2: Account Creation (Pending)
- User fills in customer, scheme, amount, opening date, payout frequency, nominees
- System validates: customer must have an **Active Shomriddhi Advance (Savings) account**
- Status: **Pending** — no GL entries yet

### Step 3: Approval
- Admin selects payment mode (Cash/Bank) and clicks Approve
- `FdrAccountService::approve()` runs:
  - Status → **Active**
  - Logs `Deposit` transaction in `fdr_transactions`
  - Posts **Collection Voucher**: DR Cash/Bank → CR Customer FDR Liability Head

### Step 4: Automated Interest Payout (Cron Job)
- Runs daily at 01:30 AM
- For each due account (non-At-Maturity):
  - Calculates proportional interest
  - Transfers to customer's Shomriddhi Savings balance
  - Logs `Interest Payout` transaction
  - Posts **Journal Voucher**: DR FDR Interest Expense → CR Customer Savings Head
  - Updates `next_interest_date` to the following cycle
  - On Maturity: sets status to `Matured` or auto-renews

### Step 5: Encashment (Manual by Admin)
- Admin opens Encashment modal → system auto-calculates via `encashData` endpoint
  - **Maturity**: Full Principal + Full Interest
  - **Pre-Mature**: Principal + Reduced Interest - Penalty
- Admin selects payment mode, confirms
- `FdrAccountService::encash()` runs:
  - Status → **Closed**
  - Logs `Encashment` or `Premature Encashment` transaction
  - Posts **Payment Voucher**:
    - DR Customer FDR Head (Principal) + DR Interest Expense → CR Cash/Bank + CR Penalty Income

---

## 7. Prerequisite Validation
An FDR account **cannot be opened** unless the customer has an Active **Shomriddhi Advance (Savings)** account. This is enforced in `FdrAccountController::store()`. The savings account is required as the target for automated monthly interest transfers.

---

## 8. Routes & Permissions
Routing: `routes/shomriddhi_fdr.php`
Permissions (sample): `shomriddhi_fdr_dashboard`, `shomriddhi_fdr_account_create`, `shomriddhi_fdr_account_approve`, `shomriddhi_fdr_account_encash`
