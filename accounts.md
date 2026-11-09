# MK-ERP — Chart of Accounts
> ???????? Account Group ? Account Head ?? ???????????

**?????? ??????:**
- `[G]` = Account Group
- `[H]` = Account Head
- ??  = System account — ???????? ??? ???? ??
- ?? = Control account — sub-ledger ?? parent
- ?? = Payment/Cash account
- ?? = Reserved ID ??? — ??????? ??? ????? ????
- ?? Debit / ?? Credit = Normal balance side

---

## 1??  ASSETS `[G]` ?? reserved:1

### ? Current Assets `[G]`

#### ?? Cash and Bank `[G]`
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Cash in Hand | 1001 | ?? Debit | ?? ?? ?? |
| *(Bank accounts)* | Dynamic | ?? Debit | ?? User-added |

#### ?? Accounts Receivable `[G]` ?? ?? reserved:7
> Customer sub-ledger ?? parent
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Inter-Branch Receivable | 1002 | ?? Debit | ?? ?? reserved:4 |
| *(Customer Name — Client)* | Dynamic | ?? Debit | ?? Auto-created |

#### ?? Loan Portfolio (MFI) `[G]`
| Head | Code | Nature | Flag |
|------|------|--------|------|
| General Loan Portfolio | 1003 | ?? Debit | ?? |

#### ?? Inventories `[G]`
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Raw Material Stock | 1004 | ?? Debit | ?? reserved:1 |
| Work-in-Progress (WIP) | 1005 | ?? Debit | ?? ?? reserved:3 |
| Finished Goods Stock | 1006 | ?? Debit | ?? reserved:2 |

#### ?? Advances and Prepayments `[G]`
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Advance to Supplier | 1007 | ?? Debit | ?? reserved:14 |

### ? Fixed Assets `[G]`
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Office Equipment | 1008 | ?? Debit | — |
| Furniture & Fixture | 1009 | ?? Debit | — |
| Accumulated Depreciation | 1010 | ?? Credit | ?? *(contra asset)* |

---

## 2??  LIABILITIES `[G]` ?? reserved:2

### ? Current Liabilities `[G]`

#### ?? Accounts Payable `[G]` ?? ?? reserved:6
> Supplier sub-ledger ?? parent
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Inter-Branch Payable | 2001 | ?? Credit | ?? ?? reserved:5 |
| *(Supplier Name — Supplier)* | Dynamic | ?? Credit | ?? Auto-created |

#### ?? Member Deposits (MFI) `[G]`
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Member Savings Account | 2002 | ?? Credit | ?? |

#### ?? Statutory Liabilities `[G]`
| Head | Code | Nature | Flag |
|------|------|--------|------|
| VAT Payable | 2004 | ?? Credit | ?? ?? reserved:12 |
| TDS Payable | 2005 | ?? Credit | ?? ?? reserved:13 |

> *?????? Current Liabilities ?? ????:*
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Customer Advance Received | 2003 | ?? Credit | ?? ?? reserved:15 |

### ? Long-term Liabilities `[G]`
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Long Term Loan | 2006 | ?? Credit | — |

---

## 3??  EQUITY `[G]` ?? reserved:3

| Head | Code | Nature | Flag |
|------|------|--------|------|
| Owner's Capital | 3001 | ?? Credit | ?? |
| Retained Earnings | 3002 | ?? Credit | ?? |
| Opening Balance Equity | 3003 | ?? Credit | ?? |

---

## 4??  REVENUE `[G]` ?? reserved:4

| Head | Code | Nature | Flag |
|------|------|--------|------|
| Sales Revenue | 4001 | ?? Credit | ?? reserved:9 |
| MFI Service Charge Income | 4002 | ?? Credit | — |
| Sales Return | 4003 | ?? Debit | ?? ?? reserved:18 *(contra)* |
| Absent Late Other Deduction | 4005 | ?? Credit | ?? ?? reserved:6 |
| Sales Discount | 4006 | ?? Debit | ?? ?? reserved:10 *(contra)* |

### ? Other Income `[G]` ?? reserved:12
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Other Income | 4004 | ?? Credit | — |

---

## 5??  EXPENSES `[G]` ?? reserved:5

### ? Cost of Goods Sold — COGS `[G]` ?? reserved:11
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Cost of Goods Sold | 5001 | ?? Debit | ?? ?? reserved:11 |
| Purchase Return | 5002 | ?? Credit | ?? ?? reserved:19 *(contra)* |
| Purchase Discount | 5003 | ?? Credit | ?? ?? reserved:17 *(contra)* |
| Inventory Adjustment | 5011 | ?? Debit | ?? |

### ? Operating Expenses `[G]`

#### ?? Administrative Expenses `[G]` ?? reserved:8
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Salary Expense | 5004 | ?? Debit | ?? reserved:7 |
| Office Rent | 5005 | ?? Debit | — |
| VAT Expense | 5009 | ?? Debit | ?? ?? reserved:16 |
| Depreciation Expense | 5010 | ?? Debit | ?? |

#### ?? Selling and Distribution `[G]` ?? reserved:9
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Marketing Expense | 5006 | ?? Debit | — |
| Sales Commission Expense | 5008 | ?? Debit | ?? ?? reserved:8 |

### ? Financial Expenses `[G]` ?? reserved:10
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Bank Charges | 5012 | ?? Debit | ?? |

#### ?? MFI Interest Expense `[G]`
| Head | Code | Nature | Flag |
|------|------|--------|------|
| Interest Paid on Savings | 5007 | ?? Debit | — |

### ? Income Tax Expense `[G]` ?? reserved:13
*(???? ???? head ??? — ????????? manually add ???? ???)*

---

## ?? ??????????

| ??? | ??? |
|-----|-----|
| Root Account Groups | 5 |
| Sub Account Groups | 20 |
| System Account Heads | 37 |
| Reserved Groups (??) | 13 |
| Reserved Heads (??) | 19 |
| Dynamic Heads | ?? Auto-created |

---

## ?? Auto-Created Heads (Dynamic Sub-Ledgers)

| ??? ???? ??? | ??? Group ?? ???? | ?????? |
|---|---|---|
| ???? Supplier ??? | **Accounts Payable** `[G]` | `M/S Hasan Traders (Supplier)` |
| ???? Customer ??? | **Accounts Receivable** `[G]` | `Md Karim (Client)` |
