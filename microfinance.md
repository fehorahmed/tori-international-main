# Microfinance Module — MK-ERP

**Framework:** Laravel 11 | **Namespace:** `App\Http\Controllers\Microfinance` | **Route Prefix:** `/microfinance`

---

## 📁 File Structure

```
app/
├── Http/
│   ├── Controllers/Microfinance/
│   │   ├── SomitiController.php
│   │   ├── MemberController.php
│   │   ├── SavingsProductController.php
│   │   ├── SavingsAccountController.php
│   │   ├── LoanProductController.php
│   │   ├── LoanController.php
│   │   ├── CollectionController.php
│   │   └── ReportController.php
│   └── Requests/Microfinance/
│       └── StoreSavingsAccountRequest.php
├── Models/Microfinance/
│   ├── MfSomiti.php
│   ├── MfMember.php
│   ├── MfNominee.php
│   ├── MfSavingsProduct.php
│   ├── MfSavingsAccount.php
│   ├── MfSavingsTransaction.php
│   ├── MfLoanProduct.php
│   ├── MfLoan.php
│   ├── MfInstallment.php
│   └── ActivityLog.php
└── Services/Microfinance/
    └── SavingsAccountService.php

routes/microfinance.php
resources/views/microfinance/
├── somiti/         (index, create, edit, show)
├── member/         (index, create, edit, show, print_empty_form)
├── savings_product/(index, create, edit, show)
├── savings_account/(index, create, edit, show, partials/scripts)
├── loan_product/   (index, create)
├── loan/           (index, create, show)
├── collection/     (index, sheet, _sheet_data)
└── report/         (member_ledger)
```

---

## 🗄️ Database Tables

| Table | Purpose |
|---|---|
| `mf_somitis` | সমিতি (Group) |
| `mf_members` | সদস্য |
| `mf_nominees` | নমিনি |
| `mf_savings_products` | সঞ্চয় প্রকল্প (GS / DPS / FDR) |
| `mf_savings_accounts` | সঞ্চয় হিসাব |
| `mf_savings_transactions` | জমা / উত্তোলন / সুদ |
| `mf_savings_account_nominee` | Pivot: account ↔ nominee |
| `mf_loan_products` | ঋণ প্রকল্প |
| `mf_loans` | ঋণ আবেদন |
| `mf_installments` | কিস্তি সময়সূচি |

**Multi-tenancy:** প্রতিটি table-এ `sister_concern_id` আছে। সব query-তে এটা filter হয়।

---

## 🔄 Complete Workflow

### Step 1 — সমিতি তৈরি (Somiti)

```
POST /microfinance/somiti
```

- **Fields:** name, somiti_type (1=Male/2=Female/3=Mixed), max_member_limit, field_officer_id, meeting_day, opening_date, collection_start_date
- **Auto-generate:** `somiti_code` → format: `S-0001`
- **Rule:** Member থাকলে Edit/Delete করা যাবে না
- **Model:** `MfSomiti` (SoftDeletes, Auditable)

---

### Step 2 — সদস্য নিবন্ধন (Member Registration)

```
POST /microfinance/member
```

- **Fields:** somiti_id, member_category (1=General/2=Lead/3=Head), name, name_bn, mobile_no, gender, marital_status, dob, religion, admission_date, present & permanent address, fees
- **Auto-generate:** `member_no` → format: `M-000001`
- **File Uploads:** photo, signature_photo, nid_photo_front, nid_photo_back, document
- **Address:** Union/Pourashava select → `parseAddressFields()` দিয়ে union_id বা pourashava_id আলাদা করা হয়
- **Nominees:** একসাথে ১ বা একাধিক nominee যোগ করা যায় (নাম, সম্পর্ক, ছবি, NID)
- **Rule:** Account বা Loan থাকলে Edit করা যাবে না
- **Model:** `MfMember` (SoftDeletes, Auditable)

---

### Step 3 — সঞ্চয় প্রকল্প তৈরি (Savings Product)

```
POST /microfinance/savings-product
```

| Field | Description |
|---|---|
| `product_type` | 1=General Savings (GS), 2=DPS, 3=FDR |
| `interest_rate` | বার্ষিক সুদের হার (%) |
| `installment_frequency` | 1=Daily, 2=Weekly, 3=Monthly (DPS-এর জন্য) |
| `installment_amount` | কিস্তির পরিমাণ (DPS) |
| `duration_months` | মেয়াদ মাসে (DPS/FDR) |
| `opening_fee` | হিসাব খোলার ফি |
| `closing_fee` | হিসাব বন্ধের ফি |
| `installment_fine_type` | flat / percentage |
| `installment_fine_amount` | মিস কিস্তিতে জরিমানা |
| `early_closing_fine_type` | flat / percentage |
| `early_closing_fine_amount` | মেয়াদ পূর্বে বন্ধে জরিমানা |
| `interest_posting_type` | 1=Maturity, 2=Monthly, 3=Quarterly, 4=Yearly (FDR) |

- **Auto-generate code:** GS-001, DPS-001, FDR-001
- **Rule:** Account থাকলে Edit করা যাবে না

---

### Step 4 — সঞ্চয় হিসাব খোলা (Open Savings Account)

```
POST /microfinance/savings-account  →  SavingsAccountService::openAccount()
```

**Flow:**
1. Member select → AJAX তে member info load
2. Product select → AJAX তে product info load
3. Form submit → `StoreSavingsAccountRequest` validation
4. `SavingsAccountService::openAccount()` call
   - Account number generate: `{ProductCode}-{MemberNo}-{Serial}` → e.g. `GS-001-M-000001-1`
   - DPS/FDR হলে maturity_date auto-calculate
   - Nominee sync (pivot table)
   - Status = **0 (Pending)**

**Approve:**
```
POST /microfinance/savings-account/{id}/approve  →  SavingsAccountService::approveAccount()
```
- Status → **1 (Active)**
- opening_balance → current_balance-এ set
- Opening balance-এর জন্য `MfSavingsTransaction` (type=1 Deposit) তৈরি
- Opening fee-এর জন্য `MfSavingsTransaction` (type=2) তৈরি ⚠️ **Bug:** Balance থেকে deduct হয় না

**Status codes:**
- `0` = Pending
- `1` = Active
- `2` = Matured
- `3` = Closed

**Transaction types (`MfSavingsTransaction`):**
- `1` = Deposit
- `2` = Withdraw ⚠️ **Missing:** Withdraw UI নেই
- `3` = Interest ⚠️ **Missing:** Auto interest posting নেই
- `4` = Penalty

---

### Step 5 — ঋণ প্রকল্প তৈরি (Loan Product)

```
POST /microfinance/loan-product
```

| Field | Description |
|---|---|
| `interest_rate` | সুদের হার (%) |
| `interest_type` | 1=Flat, 2=Reducing Balance ⚠️ Reducing implement নেই |
| `installment_frequency` | 1=Daily, 2=Weekly, 3=Monthly |
| `insurance_fee_percentage` | বীমা ফি (%) |
| `processing_fee_percentage` | প্রক্রিয়াকরণ ফি (%) |
| `grace_period_days` | গ্রেস পিরিয়ড (দিন) |

---

### Step 6 — ঋণ আবেদন (Loan Application)

```
POST /microfinance/loan
```

- Member ও Product select করে আবেদন করতে হয়
- **Auto-generate loan_no:** `{ProductCode}-LN-{MemberId}-{Count}`
- Fields: applied_amount, total_installments, grace_period, processing_fee, insurance_amount, guarantor info
- Status → **1 (Pending)**

**Loan Status codes:**
- `1` = Pending
- `2` = Approved ⚠️ এই step skip হচ্ছে (bug)
- `3` = Active (Disbursed)
- `4` = Closed
- `5` = Rejected

---

### Step 7 — ঋণ অনুমোদন ও বিতরণ (Approve & Disburse)

```
POST /microfinance/loan/{id}/approve
```

- `approved_amount`, `disbursement_date`, `first_installment_date` দিতে হয়
- Status → **3 (Active)** ⚠️ **Bug:** Status 1→3 হয়, 2 (Approved) skip হয়
- `generateInstallments()` call হয় → `MfInstallment` records তৈরি হয়

**Installment Calculation (Flat Interest):**
```
total_interest    = (amount × interest_rate) / 100
total_payable     = amount + total_interest
installment_total = total_payable / num_installments
```
⚠️ **Bug:** `net_disbursed_amount` কখনো set হয় না
⚠️ **Missing:** Reducing Balance calculation নেই

**Installment Status:**
- `1` = Pending
- `2` = Paid
- `3` = Partial ⚠️ Partial payment logic নেই

---

### Step 8 — কিস্তি আদায় (Collection)

**Two ways:**

#### A) Individual Collection
```
GET  /microfinance/collection          → due installments list
POST /microfinance/collection/collect  → single installment collect
```

#### B) Master Collection Sheet (সমিতি-ভিত্তিক)
```
GET  /microfinance/collection-sheet        → sheet form
GET  /microfinance/collection-sheet/fetch  → AJAX: load members + dues
POST /microfinance/collection-sheet/save   → bulk save
```

**Sheet save flow:**
1. Somiti ও Date select করো
2. AJAX-এ সব member-এর due installments ও savings accounts load হয়
3. প্রতিটি member-এর জন্য loan installment amount ও savings deposit amount দাও
4. Save → একসাথে সব `MfInstallment.status=2` এবং `MfSavingsTransaction` create হয়

⚠️ **Bug:** Already paid installment double collect করা যাচ্ছে (paid check নেই)

**Loan auto-close:** সব installment paid হলে `MfLoan.status=4 (Closed)` হয়

---

### Step 9 — Reports

```
GET /microfinance/report/member-ledger     → Member-wise savings + loans
GET /microfinance/report/collection-sheet  → Placeholder (incomplete)
```

---

## ⚠️ Completed Bugs & Audit Fixes (Fully Resolved)

| # | Bug | Fixed in | Status |
|---|---|---|---|
| 1 | Double payment — paid installment check | `CollectionController@saveSheet` & `@collect` | ✅ Fixed (Check status, paid skip) |
| 2 | Loan status 1→3 (Approved step bypass) | `LoanController` & `LoanService` | ✅ Refactored |
| 3 | `net_disbursed_amount` set logic | `LoanService@approveAndDisburse` | ✅ Fixed |
| 4 | Sister concern isolation on actions | `LoanController` & `CollectionController` | ✅ Fixed (Strict abort check) |
| 5 | Opening fee balance from account deduction | `SavingsAccountService@approveAccount` | ✅ Fixed (Balance auto-deduct) |
| 6 | Reducing Balance interest calculation | `LoanService@generateInstallments` | ✅ Fixed (EMI formula added) |
| 7 | `installment_start_date` leakage | `LoanController@store` | ✅ Fixed (Formatted in data) |

---

## 🔧 Implemented Features (Completed)

| Feature | Description | File / Method |
|---|---|---|
| **Savings Withdrawal UI** | Debit amount with date and notes directly from ledger | `SavingsAccountController@withdraw` & UI modal |
| **Savings Manual/Auto Interest Posting** | Post periodic interest manually or daily via scheduler | `SavingsAccountController@postInterest` & Cron |
| **Savings Account Close Flow** | Deduct product closing fee and close account with reason | `SavingsAccountController@closeAccount` |
| **Loan Repayment Schedule Print** | Print-friendly repayment schedule for loan agreements | `LoanController@repaymentSchedule` & blade |
| **Loan Partial Payment Support** | Collect partial amount and mark installment status = 3 | `CollectionController@collect` & `@saveSheet` |
| **Daily Cron Scheduler** | Apply overdue penalties, accumulate daily General Savings interest, and auto-post savings interest annually | `App\Console\Commands\MicrofinanceDailyCron` |
| **Member Status Change** | Toggle member Active/Inactive with strict safety checks | `MemberController@toggleStatus` |
| **Member Somiti Transfer** | Move member to another active Somiti of same concern | `MemberController@transfer` |
| **Collection Sheet Report** | View due installments and savings balance by Somiti | `ReportController@collectionSheet` |
| **Loan Portfolio Report** | Total loans summary and detailed listing | `ReportController@loanPortfolio` |
| **Savings Portfolio Report** | Total savings balance and product ledger listing | `ReportController@savingsPortfolio` |

---

## 🏗️ Architecture Notes

### Service Layer
`SavingsAccountController` → `SavingsAccountService` ✅
`LoanController` → Fat Controller (Service নেই) ❌ → Refactor দরকার

### Form Requests
`StoreSavingsAccountRequest` ✅ (Savings Account)
Loan, Member, Somiti → Inline `$request->validate()` ❌ → Form Request class বানাতে হবে

### Models
- সব গুরুত্বপূর্ণ model-এ `SoftDeletes` ও `Auditable` trait আছে ✅
- সব model-এ `$guarded = []` → mass assignment unprotected ⚠️ `$fillable` দিয়ে replace করা ভালো
- `MfSavingsProduct`-এ constants আছে: `TYPE_GENERAL=1`, `TYPE_DPS=2`, `TYPE_FDR=3` ✅
- `MfSavingsTransaction`-এ constants: `TYPE_DEPOSIT=1`, `TYPE_WITHDRAW=2`, `TYPE_INTEREST=3`, `TYPE_PENALTY=4` ✅

### Multi-tenancy
```php
// সব query-তে এই pattern follow করতে হবে
->where('sister_concern_id', auth()->user()->sister_concern_id)
```

### Date Format
- Input (UI): `dd-mm-Y` (Bangla date picker)
- Storage (DB): `Y-m-d`
- Convert: `Carbon::createFromFormat('d-m-Y', $input)->format('Y-m-d')`

### File Uploads (UploadTrait)
```
microfinance/member/photos/
microfinance/member/signatures/
microfinance/member/nid/
microfinance/member/documents/
microfinance/nominee/photos/
microfinance/nominee/signatures/
microfinance/nominee/nid/
microfinance/loan/guarantors/
```

---

## 🔗 Model Relationships

```
MfSomiti
  └── hasMany → MfMember

MfMember
  ├── belongsTo → MfSomiti
  ├── hasMany   → MfNominee
  ├── hasMany   → MfSavingsAccount
  └── hasMany   → MfLoan

MfSavingsProduct
  └── hasMany → MfSavingsAccount

MfSavingsAccount
  ├── belongsTo      → MfMember
  ├── belongsTo      → MfSavingsProduct
  ├── belongsTo      → MfSomiti
  ├── belongsTo      → MfMember (introducer)
  ├── hasMany        → MfSavingsTransaction
  └── belongsToMany → MfNominee (pivot: mf_savings_account_nominee)

MfLoanProduct
  └── hasMany → MfLoan

MfLoan
  ├── belongsTo → MfMember
  ├── belongsTo → MfLoanProduct
  ├── belongsTo → MfSomiti
  └── hasMany   → MfInstallment

MfInstallment
  ├── belongsTo → MfLoan
  └── belongsTo → MfSomiti
```

---

## 📋 Quick Reference — Status Codes

| Model | Status | Meaning |
|---|---|---|
| `MfSomiti` | 1/0 | Active / Inactive |
| `MfMember` | 1/0 | Active / Inactive |
| `MfSavingsAccount` | 0/1/2/3 | Pending / Active / Matured / Closed |
| `MfLoan` | 1/2/3/4/5 | Pending / Approved / Active / Closed / Rejected |
| `MfInstallment` | 1/2/3 | Pending / Paid / Partial |
| `MfSavingsTransaction` | type 1/2/3/4 | Deposit / Withdraw / Interest / Penalty |
| `MfSavingsProduct` type | 1/2/3 | General Savings / DPS / FDR |
| `MfLoanProduct` interest_type | 1/2 | Flat / Reducing Balance |
| `MfLoanProduct` frequency | 1/2/3 | Daily / Weekly / Monthly |

---

## 🔒 MFI Professional Constraints & Business Rules

To ensure banking-level operations and strict compliance with standard microfinance practices (e.g., MRA rules), the module enforces the following strict logical constraints:

1. **One Active General Savings (GS) Account:** A member is strictly allowed to have only **one active General Savings** account.
2. **General Savings Prerequisite for DPS/FDR:** A member is strictly prohibited from opening a DPS or FDR account unless they have an active General Savings account.
3. **General Savings Prerequisite for Loans:** A member is strictly prohibited from applying for any Loan product unless they have an active General Savings account.
4. **General Savings Interest Posting (Daily Product Method - Option B):**
   - **Daily Accrual:** Interest is calculated daily on the end-of-day balance using the formula: `Daily Interest = round((Balance * Rate) / 100 / 365, 4)`.
   - **Running Accumulation:** Calculated daily interest is accrued and accumulated inside the `accrued_interest` database column to prevent loss of fractional values. No ledger transaction or balance credit occurs daily.
   - **Annual Posting Date:** Accumulated interest is officially posted (credited to `current_balance`) once a year on **June 30th** (the end of the financial year). Only one balanced double-entry transaction record is added to the ledger annually.
   - **Early Closing Interest Forfeiture:** If an account is closed before the annual posting day (June 30th), any accumulated `accrued_interest` in the column is completely forfeited (reset to `0.0000`) and no interest is paid to the client.

---

## 🏛️ General Ledger (GL) Double Entry Integration (EOD Day Closing)

To prevent main ledger bloating with thousands of individual micro-transactions, the module implements an **End of Day (EOD) closing** system. It aggregates daily activities and posts them as a single balanced Journal Voucher:

### 1. Savings Deposits
- **Debit:** `Cash/Bank Account` (Selected Asset Account)
- **Credit:** `MFI Member Savings Account` (ID 10 - Savings Liability)

### 2. Savings Withdrawals
- **Debit:** `MFI Member Savings Account` (ID 10 - Savings Liability)
- **Credit:** `Cash/Bank Account` (Selected Asset Account)

### 3. Savings Interest Posted
- **Debit:** `MFI Savings Interest Paid` (ID 34 - Expense Account)
- **Credit:** `MFI Member Savings Account` (ID 10 - Savings Liability)

### 4. Loans Disbursed
- **Debit:** `MFI Loan Portfolio` (ID 2 - Loan Asset) [Full Approved Amount]
- **Credit:** `Cash/Bank Account` (Selected Asset Account) [Net Disbursed Amount]
- **Credit:** `MFI Service Charge Income` (ID 19 - Revenue Account) [Processing & Insurance Fees]

### 5. Loan Collections
- **Debit:** `Cash/Bank Account` (Selected Asset Account) [Total Paid Amount]
- **Credit:** `MFI Loan Portfolio` (ID 2 - Loan Asset) [Collected Principal]
- **Credit:** `MFI Service Charge Income` (ID 19 - Revenue Account) [Collected Interest]
