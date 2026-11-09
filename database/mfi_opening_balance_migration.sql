-- ============================================================
-- MFI Opening Balance - Database ALTER Statements
-- Run these on your existing live database
-- ============================================================

-- 1. mf_savings_accounts: Opening Balance Migration Fields
ALTER TABLE `mf_savings_accounts`
    ADD COLUMN `is_opening_entry`          TINYINT(1)     NOT NULL DEFAULT 0
        COMMENT '1=Migrated from old software as Opening Balance'
        AFTER `close_reason`,
    ADD COLUMN `migration_date`            DATE           NULL
        COMMENT 'Business date when opening entry was done (Go-Live Date)'
        AFTER `is_opening_entry`,
    ADD COLUMN `installments_paid`         INT UNSIGNED   NOT NULL DEFAULT 0
        COMMENT 'For DPS: installments already paid in old software'
        AFTER `migration_date`,
    ADD COLUMN `opening_accrued_interest`  DECIMAL(12,2)  NOT NULL DEFAULT 0
        COMMENT 'For FDR: interest accrued up to migration_date in old software'
        AFTER `installments_paid`;

-- 2. mf_savings_transactions: Update transaction_type comment (type 5=Opening added)
ALTER TABLE `mf_savings_transactions`
    MODIFY COLUMN `transaction_type` TINYINT NOT NULL
        COMMENT '1=Deposit, 2=Withdraw, 3=Interest, 4=Penalty, 5=Opening';

-- 3. mf_loans: Opening Balance Migration Fields
ALTER TABLE `mf_loans`
    ADD COLUMN `is_opening_entry`      TINYINT(1)    NOT NULL DEFAULT 0
        COMMENT '1=Migrated from old software as Opening Balance'
        AFTER `collateral_description`,
    ADD COLUMN `migration_date`        DATE          NULL
        COMMENT 'Business date when opening entry was done (Go-Live Date)'
        AFTER `is_opening_entry`,
    ADD COLUMN `installments_paid`     INT UNSIGNED  NOT NULL DEFAULT 0
        COMMENT 'Number of installments already paid in old software'
        AFTER `migration_date`,
    ADD COLUMN `outstanding_principal` DECIMAL(12,2) NOT NULL DEFAULT 0
        COMMENT 'Remaining principal at migration_date from old software'
        AFTER `installments_paid`;

-- 4. mf_installments: Mark opening (already paid) installments
ALTER TABLE `mf_installments`
    ADD COLUMN `is_opening_paid` TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '1=Paid in old software before migration'
        AFTER `status`;
