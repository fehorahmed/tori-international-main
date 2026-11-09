# Card Management Module Documentation

## Overview
The Card Management module is designed to handle the issuance, tracking, and financial lifecycle of physical identification/membership cards for both **Members (Customers/Dealers)** and **Employees**. The system ensures robust multi-tenancy, preventing data leakage across sister concerns, and automates complex accounting entries during card fee collection.

---

## Key Features
1. **Polymorphic Architecture:** Cards are issued via a polymorphic relation (`cardable_type` and `cardable_id`), allowing a single `cards` table to manage both `Supplier` (Members) and `Employee` entities.
2. **Strict Multi-Tenancy:** The `origin_sister_concern_id` is explicitly stored on both `cards` and `card_print_logs` tables. All dataTables, creation forms, collections, and reports strictly filter by the authenticated user's active branch.
3. **Automated Accounting:** Collecting fees for Member Cards automatically triggers a collection voucher and double-entry accounting (Cash/Bank, Income, Employee Commission, and Customer Cashback).
4. **Print Lifecycle & Security:** 
    - Member Cards **cannot** be printed (UI disabled and backend aborted via 403 Forbidden) until the card fee is collected (`payment_status = 'Received'`).
    - Employee Cards are complimentary and bypass the collection flow (auto-marked as `Received` with `amount = 0`).
    - All print actions (First Issue, Lost Replacement, etc.) are recorded in `card_print_logs`.
5. **Public Verification:** Both Member and Employee cards can be publicly verified via a unique URL/QR code.

---

## Core Components

### 1. Controllers
- **`MemberCardController`**: 
  - Manages card issuance for Customers.
  - Upon creation, automatically opens a "Customer Savings Account" head (under Liabilities) and provisions a `ShomriddhiAccount` providing an initial cashback bonus of 20 BDT.
- **`EmployeeCardController`**: 
  - Manages card issuance for Employees.
  - Skips the payment flow entirely. Cards are created with `payment_status = 'Received'` and can be printed immediately.
- **`CardCollectionController`**: 
  - Fetches all `Pending` cards specifically for `Supplier::class` within the active branch.
  - Provides a bulk "Receive Cash" button per user.
  - Updates card status to `Received` and executes `generateAccountingEntries()`.
- **`ReportController`**: 
  - `collectionReport`: Displays all historical payments (Strictly filters for `Supplier::class` and `payment_status = 'Received'`).
  - Status Summaries, Print Histories, and Lost/Damaged reports for both Members and Employees.

### 2. Database Structure
- **`cards` table:**
  - `origin_sister_concern_id` (Foreign Key -> sister_concerns)
  - `card_number` (Unique, auto-generated sequentially e.g., TORI-C-10001)
  - `cardable_type`, `cardable_id` (Polymorphic parent)
  - `status` (Active, Suspended, Lost, Damaged, Expired, Revoked)
  - `amount`, `payment_status` (Pending/Received), `received_by`, `received_at`
- **`card_print_logs` table:**
  - `origin_sister_concern_id` (Foreign Key -> sister_concerns)
  - `card_id`, `printed_by`, `printed_at`, `print_reason`, `notes`

---

## Financial Workflow (Member Cards)
When a Member Card is issued, the system charges a default fee (e.g., 100 BDT). The payment is collected via the Collection module, generating the following accounting workflow:

1. **Voucher Creation:** A Collection Voucher is generated.
2. **Debit Entry:** `Cash/Bank` account is Debited for the full amount (100 BDT).
3. **Credit Entries:**
   - **Company Income:** Remaining amount is Credited to `Other Income`.
   - **Employee Commission:** The issuing user (Employee) receives a commission (e.g., 20 BDT) Credited to `Card Commission Payable`.
   - **Customer Cashback:** The customer receives a cashback bonus (e.g., 20 BDT) Credited to their personal `Savings Account`.
4. **Shomriddhi Advance Log:** The customer's cashback is also mirrored in the `ShomriddhiAdvance` table (`type = 'deposit'`) so it appears on their Shomriddhi Passbook/Dashboard.

---

## Security & Validations
- **Duplicate Prevention:** A user/member cannot have multiple `Active` cards. The previous card must be marked as Revoked/Lost/Damaged before a new one is issued.
- **Print Lock:** Print actions (`printPreview`, `bulkPrintPreview`, `logPrint`) utilize strict middleware and `abort_if` conditions to prevent bypassing the payment gateway for Member Cards.
- **Data Isolation:** Every single DB query is scoped using `->where('origin_sister_concern_id', auth()->user()->activeConcernId())`.

---

## Future Extensibility
- **Dynamic Fees/Commissions:** The 100/20/20 fee structure in `generateAccountingEntries` is currently hardcoded but structured cleanly enough to be migrated to a `Settings` or `SystemConfiguration` table if business rules change.
- **Batch Processing:** Bulk print logic is implemented securely and can be extended to support batch physical printer integrations if necessary.
