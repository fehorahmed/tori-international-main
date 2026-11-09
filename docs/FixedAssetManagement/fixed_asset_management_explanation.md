# Fixed Asset Management: How It Works (Explanation)

## Introduction
The Fixed Asset Management module is designed to track company assets and automate the complex accounting involved in their lifecycle. Unlike simple inventory, fixed assets lose value over time (Depreciation) and can eventually be sold or thrown away (Disposed). This module automates all the mathematical and financial heavy-lifting.

---

## 1. Creating a Category (The Foundation)
Before you can buy an asset, you need a Category (e.g., "Vehicles" or "Furniture").
- When you create a category, you define how it loses value: 
  - **SLM (Straight Line)**: Loses a fixed amount based on its lifespan (e.g., 5 years).
  - **WDV (Written Down Value)**: Loses a percentage of its current value every year (e.g., 10%).
- **Behind the Scenes**: The system automatically talks to the Accounting module and creates a new Ledger (Account Head) under the "Fixed Assets" group.

---

## 2. Acquiring an Asset (Purchasing)
When you register a new asset (e.g., a "Toyota Corolla" under the "Vehicles" category):
- You input the Purchase Cost, Salvage Value (what it will be worth at the end of its life), and the Cash/Bank account used to pay for it.
- **Behind the Scenes**: The system automatically generates a **Journal Voucher (JV)**. It debits the Vehicle ledger and credits your Cash/Bank.

---

## 3. Running Depreciation (Monthly Process)
At the end of every month, the accountant clicks "Run Depreciation".
- The system finds all active assets.
- It calculates exactly how much value each asset lost in that specific month based on its category rules.
- It updates the `book_value` (Current Worth) of each asset.
- **Behind the Scenes**: Instead of making 100 separate accounting entries, the system intelligently groups them into **One Master JV**. It debits "Depreciation Expense" and credits the master "Accumulated Depreciation" account.

---

## 4. Selling or Scrapping an Asset (Disposal)
When an asset is no longer needed, it must be disposed of.
- **Sell**: You input the Sale Amount. The system compares this to the Current Book Value.
  - If Sale Amount > Book Value = **Gain** (Profit).
  - If Sale Amount < Book Value = **Loss**.
- **Scrap**: The asset is completely destroyed or thrown away. The remaining Book Value becomes an absolute **Loss**.
- **Behind the Scenes**: This is the most complex accounting step. The system automatically creates a JV that:
  1. Receives the cash (if sold).
  2. Clears out the Accumulated Depreciation for that specific asset.
  3. Removes the Original Cost from the Fixed Asset ledger.
  4. Records the exact Gain or Loss to the correct reserved account heads.
- Finally, the asset is marked as `sold` or `scrapped` and will no longer be depreciated.

---

## 5. Reports
The module provides comprehensive reporting:
- **Asset Register**: A complete list of all assets, their original cost, total depreciation, and current book value.
- **Depreciation Summary**: A breakdown of depreciation applied to assets, filterable by category and status.
