# Fixed Asset Management Architecture

## 1. Overview
The Fixed Asset Management module handles the complete lifecycle of a company's fixed assets—from acquisition, tracking, and monthly depreciation (SLM/WDV) to final disposal (Sell or Scrap). It is deeply integrated with the core Accounting module to ensure all financial implications (Asset Value, Depreciation Expense, Accumulated Depreciation, Gain/Loss) are accurately recorded through automated Journal Vouchers (JVs).

## 2. Core Entities & Database Schema

### 2.1 Fixed Asset Category (`fixed_asset_categories`)
- **Purpose**: Groups assets (e.g., IT Equipment, Furniture) and defines default depreciation rules.
- **Key Columns**: `name`, `depreciation_method` (SLM, WDV, none), `depreciation_rate`, `useful_life_years`, `account_head_id`.
- **Accounting**: Dynamically generates an `account_heads` entry under the "Fixed Assets" group when created.

### 2.2 Fixed Asset (`fixed_assets`)
- **Purpose**: Represents an individual asset unit.
- **Key Columns**: `asset_code`, `category_id`, `purchase_cost`, `salvage_value`, `book_value`, `accumulated_depreciation`, `status` (active, sold, scrapped).
- **Accounting**: Creation auto-generates a JV debiting the specific Category Account Head and crediting the Payment Account.

### 2.3 Depreciation Log (`fixed_asset_depreciation_logs`)
- **Purpose**: Tracks monthly depreciation applied to each asset.
- **Key Columns**: `period` (YYYY-MM), `depreciation_amount`, `book_value_before`, `book_value_after`, `voucher_id`.

### 2.4 Disposal (`fixed_asset_disposals`)
- **Purpose**: Records the exit of an asset from the system.
- **Key Columns**: `disposal_type` (sold/scrapped), `sale_amount`, `book_value_at_disposal`, `gain_loss_amount`, `voucher_id`.

## 3. Financial Workflows (Accounting Integration)

The module uses specific `ReservedAccountHead` constants to standardize entries:
- `ACCUMULATED_DEPRECIATION` (Contra-Asset)
- `DEPRECIATION_EXPENSE` (Expense)
- `GAIN_ON_ASSET_SALE` (Income)
- `LOSS_ON_ASSET_SALE` / `LOSS_ON_SCRAP` (Expense)

### 3.1 Acquisition
- **Dr.** Fixed Asset Category Ledger (e.g., IT Equipment)
- **Cr.** Payment Account (Cash/Bank)

### 3.2 Monthly Depreciation (`DepreciationService.php`)
- **Dr.** Depreciation Expense (Total amount)
- **Cr.** Accumulated Depreciation (Total amount)

### 3.3 Disposal - Sell (Gain Scenario)
- **Dr.** Cash/Bank (Sale Amount)
- **Dr.** Accumulated Depreciation (Cleared)
- **Cr.** Fixed Asset Category Ledger (Original Cost)
- **Cr.** Gain on Asset Sale

### 3.4 Disposal - Scrap
- **Dr.** Accumulated Depreciation (Cleared)
- **Dr.** Loss on Scrap (Remaining Book Value)
- **Cr.** Fixed Asset Category Ledger (Original Cost)

## 4. Key Services & Controllers

- `FixedAssetCategoryController`: Manages categories and auto-generates chart of accounts.
- `FixedAssetController`: Manages individual asset acquisition and lifecycle tracking.
- `DepreciationController` & `DepreciationService`: Centralized logic for calculating and applying batch depreciation preventing double-entries per period.
- `DisposalController`: Handles the complex accounting of writing off or selling an asset, automatically computing gains or losses.
- `FixedAssetReportController`: Generates printable register and depreciation summaries.
