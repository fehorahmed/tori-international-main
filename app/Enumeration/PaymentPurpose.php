<?php
namespace App\Enumeration;

class PaymentPurpose
{
    // RECEIVABLES (Customer / Sales)
    public static $ORDER_DUE_COLLECTION = 1;
    public static $OPENING_DUE_COLLECTION = 2;
    public static $ADVANCE_COLLECTION = 3;
    public static $ADVANCE_COLLECTION_ADJUST = 4;
    public static $INSTANT_COLLECTION = 5; // Cash Sales
    public static $SALES_RETURN_ADJUST = 6; // Deducted from Customer Due
    public static $SALES_RETURN_REFUND = 7; // Cash refunded to Customer

    // PAYABLES (Supplier / Purchase)
    public static $ORDER_DUE_PAYMENT = 8;
    public static $OPENING_DUE_PAYMENT = 9;
    public static $ADVANCE_PAYMENT = 10;
    public static $ADVANCE_PAYMENT_ADJUST = 11;
    public static $INSTANT_PAYMENT = 12; // Cash Purchase
    public static $PURCHASE_RETURN_ADJUST = 13; // Deducted from Supplier Due
    public static $PURCHASE_RETURN_REFUND = 14; // Cash received back from Supplier
    public static $INTER_COMPANY_SETTLEMENT = 15; // Payment/Receipt between branches
    
    // Shomriddhi Advance specific payment purposes
    public static $SHOMRIDDHI_ADVANCE_DEPOSIT = 16;  // সাধারণ জমার জন্য
    public static $SHOMRIDDHI_ADVANCE_WITHDRAW = 17; // উত্তোলনের জন্য
    public static $SHOMRIDDHI_CARD_FEE = 18;          // কার্ড বিক্রয় ফি (যেমন: ১০০ টাকা)
    public static $SHOMRIDDHI_PASSBOOK_FEE = 19;     // ভবিষ্যতে পাসবুক ফি যোগ হলে তার জন্য
    public static $SHOMRIDDHI_CASHBACK = 20;         // ক্যাশব্যাক বা ওপেনিং বোনাস (যেমন: ২০ টাকা)

    // DPS (Deposit Pension Scheme) payment purposes
    public static $DPS_DEPOSIT = 21;                 // FDR প্রাথমিক আমানত অনুমোদন
    public static $DPS_INSTALLMENT = 22;             // মাসিক কিস্তি পরিশোধ
    public static $DPS_ENCASHMENT = 23;              // মেয়াদান্তে সম্পূর্ণ পেমেন্ট
    public static $DPS_PREMATURE_ENCASHMENT = 24;    // সময়ের আগে উত্তোলন
    public static $SUPPLIER_RECEIPT = 25;            // Supplier Receipt (Refund/Advance return)
    public static $MEMBERSHIP_CASHOUT = 26;
    public static $MEMBERSHIP_CASHOUT_CHARGE = 27;

    public static $MEMBERSHIP_TRANSFER = 28;
    public static $MEMBERSHIP_TRANSFER_CHARGE = 29;
    public static $MEMBERSHIP_REGISTRATION_FEE = 30;
    public static $MEMBERSHIP_ORDER_PAYMENT = 31;
    public static $MEMBERSHIP_ORDER_REFUND = 32;
    public static $ECOMMERCE_ORDER_CANCELLATION = 33;

    public static function names()
    {
        return [
            self::$ORDER_DUE_COLLECTION => 'Sales Due Collection',
            self::$OPENING_DUE_COLLECTION => 'Sales Opening Due Collection',
            self::$ADVANCE_COLLECTION => 'Sales Advance Collection',
            self::$ADVANCE_COLLECTION_ADJUST => 'Sales Advance Adjustment',
            self::$INSTANT_COLLECTION => 'Cash Sales (Instant)',
            self::$SALES_RETURN_ADJUST => 'Sales Return Adjustment',
            self::$SALES_RETURN_REFUND => 'Sales Return Refund',
            
            self::$ORDER_DUE_PAYMENT => 'Purchase Due Payment',
            self::$OPENING_DUE_PAYMENT => 'Purchase Opening Due Payment',
            self::$ADVANCE_PAYMENT => 'Purchase Advance Payment',
            self::$ADVANCE_PAYMENT_ADJUST => 'Purchase Advance Adjustment',
            self::$INSTANT_PAYMENT => 'Cash Purchase (Instant)',
            self::$PURCHASE_RETURN_ADJUST => 'Purchase Return Adjustment',
            self::$PURCHASE_RETURN_REFUND => 'Purchase Return Refund',
            self::$INTER_COMPANY_SETTLEMENT => 'Inter-Company Settlement',
            
            self::$SHOMRIDDHI_ADVANCE_DEPOSIT => 'Shomriddhi Advance Deposit',
            self::$SHOMRIDDHI_ADVANCE_WITHDRAW => 'Shomriddhi Advance Withdraw',
            self::$SHOMRIDDHI_CARD_FEE => 'Shomriddhi Card Fee',
            self::$SHOMRIDDHI_PASSBOOK_FEE => 'Shomriddhi Passbook Fee',
            self::$SHOMRIDDHI_CASHBACK => 'Shomriddhi Cashback',
            
            self::$DPS_DEPOSIT => 'DPS Initial Deposit',
            self::$DPS_INSTALLMENT => 'DPS Installment',
            self::$DPS_ENCASHMENT => 'DPS Encashment',
            self::$DPS_PREMATURE_ENCASHMENT => 'DPS Premature Encashment',
            self::$SUPPLIER_RECEIPT => 'Supplier Receipt',
            self::$MEMBERSHIP_TRANSFER => 'Membership Send Money',
            self::$MEMBERSHIP_TRANSFER_CHARGE => 'Membership Send Money Charge',
            self::$MEMBERSHIP_REGISTRATION_FEE => 'Membership Registration Fee',
            self::$MEMBERSHIP_ORDER_PAYMENT => 'Ecommerce Account Payment',
            self::$MEMBERSHIP_ORDER_REFUND => 'Ecommerce Account Refund',
            self::$ECOMMERCE_ORDER_CANCELLATION => 'Ecommerce Order Cancellation',
            self::$MEMBERSHIP_CASHOUT => 'Membership Cash-out',
            self::$MEMBERSHIP_CASHOUT_CHARGE => 'Membership Cash-out Charge',
            
            'other_receipts' => 'Other Receipts',
            'other_payments' => 'Other Payments',
        ];
    }
}
