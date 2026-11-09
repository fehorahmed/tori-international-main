# Shomriddhi Advance Module Architecture

## 1. Overview
The **Shomriddhi Advance Module** manages financial advances (deposits and withdrawals) for customers (managed as Suppliers with savings accounts). It implements a multi-step approval workflow, ensuring strict financial controls and seamless integration with the core General Ledger through double-entry accounting upon approval.

## 2. Design Patterns Applied

### Service-Repository Pattern
- **Service Layer (`ShomriddhiAdvanceService`)**: Encapsulates complex business logic, specifically the generation of double-entry accounting transactions during the approval process. This ensures controllers remain thin and focused on HTTP requests.
- **Repository Pattern (`EloquentShomriddhiAdvanceRepository`)**: Abstracts database interactions and status updates, providing a clean interface for data retrieval and manipulation.

---

## 3. Database Architecture

### `shomriddhi_advances`
Stores the lifecycle of all deposit and withdrawal requests.
- `id` (PK)
- `customer_id` (foreign_key -> suppliers) - Links to the customer making the advance transaction.
- `type` (enum: 'deposit', 'withdraw') - Determines the direction of cash flow.
- `amount` (double, 10,2) - The transaction amount.
- `date` (date) - Transaction date.
- `note` (text, nullable) - Optional remarks.
- `status` (enum: 'Pending', 'Approved', 'Rejected') - Tracks the approval workflow. Default is 'Pending'.
- `created_by` (foreign_key -> users) - The field officer/user who initiated the request.
- `approved_by` (foreign_key -> users, nullable) - The administrator who approved the request.
- `approved_at` (timestamp, nullable) - Timestamp of approval.
- *Timestamps & SoftDeletes*

---

## 4. Core System Components

### 4.1. `AdvanceController`
Handles HTTP requests for transaction entry, datatable rendering, and triggering approval/rejection workflows. Validates incoming requests and delegates business logic to the service layer.

### 4.2. `ShomriddhiAdvanceService` (Central Engine)
The central service responsible for the approval workflow and General Ledger integration.
- Method: `approve(ShomriddhiAdvance $advance, int $paymentAccountHeadId)`
- **Accounting Logic (Double-Entry):**
  - **Deposit (Collection Voucher):**
    - **Debit:** Cash/Bank Account (Selected Payment Mode)
    - **Credit:** Customer's Savings Account Head (Liability increases)
  - **Withdrawal (Payment Voucher):**
    - **Debit:** Customer's Savings Account Head (Liability decreases)
    - **Credit:** Cash/Bank Account (Selected Payment Mode)
- **Safety Checks:** Verifies the existence of both the customer's savings account head and the selected payment account head before proceeding. Wraps the entire operation in a database transaction (`DB::beginTransaction()`).

### 4.3. Repositories
- `ShomriddhiAdvanceRepositoryInterface` & `EloquentShomriddhiAdvanceRepository`: Handles direct database updates, specifically updating the status and audit fields (`approved_by`, `approved_at`).

---

## 5. Complete Workflow

### Step 1: Entry Creation
A user initiates a `deposit` or `withdraw` transaction for a specific customer.
- Status is set to **Pending**.
- No accounting entries are generated at this stage.

### Step 2: Approval
An authorized user reviews the pending transaction from the dashboard/datatable.
- The user selects the `payment_mode` (the Cash/Bank account to use).
- The system calls `ShomriddhiAdvanceService::approve()`.
- A Voucher and two paired Transactions (Debit/Credit) are generated in the core accounts module.
- Status is updated to **Approved**.

### Step 3: Rejection
If rejected, the status changes to **Rejected**. No financial records are affected.

---

## 6. UI & Navigation Integration
Controllers located in `App\Http\Controllers\ShomriddhiAdvance\`:
1. **`AdvanceController`**: Manages the creation, listing (Datatable), approval, and rejection of advances.
2. **`DashboardController`**: Provides high-level metrics and module overview.
3. **`ReportController`**: Handles detailed reporting (e.g., `ledger` and `summary` reports) specific to Shomriddhi Advances.

Modular routing is encapsulated in `routes/shomriddhi_advance.php` and gated by specific permissions (e.g., `shomriddhi_advance_dashboard`, `shomriddhi_advance_transaction_create`).
