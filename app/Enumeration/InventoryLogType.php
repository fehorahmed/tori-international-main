<?php

namespace App\Enumeration;

class InventoryLogType
{
    public static $PURCHASE_IN = 1;
    public static $SALE_OUT = 2;
    public static $FINISHED_GOODS_MANUFACTURED = 3;
    public static $PRODUCT_UTILIZED = 4;
    public static $STOCK_TRANSFER_OUT = 5;
    public static $STOCK_TRANSFER_IN = 6;
    public static $SALES_RETURN_IN = 7;
    public static $STOCK_CONVERSION_OUT = 8;
    public static $STOCK_CONVERSION_IN = 9;
    public static $STOCK_TRANSFER_RETURN_OUT = 10; // Receiver returns goods back (shortage)
    public static $STOCK_TRANSFER_RETURN_IN  = 11; // Sender receives returned goods
    public static $OPENING_STOCK             = 12; // Opening Stock Entry
    public static $SALES_REPLACEMENT_OUT     = 13;
    public static $PURCHASE_RETURN_OUT       = 14;
    public static $PURCHASE_REPLACEMENT_IN   = 15;

    public static function inTypes()
    {
        return [
            self::$PURCHASE_IN,
            self::$FINISHED_GOODS_MANUFACTURED,
            self::$STOCK_TRANSFER_IN,
            self::$SALES_RETURN_IN,
            self::$STOCK_CONVERSION_IN,
            self::$STOCK_TRANSFER_RETURN_IN,
            self::$OPENING_STOCK,
            self::$PURCHASE_REPLACEMENT_IN,
        ];
    }

    public static function outTypes()
    {
        return [
            self::$SALE_OUT,
            self::$PRODUCT_UTILIZED,
            self::$STOCK_TRANSFER_OUT,
            self::$STOCK_CONVERSION_OUT,
            self::$STOCK_TRANSFER_RETURN_OUT,
            self::$SALES_REPLACEMENT_OUT,
            self::$PURCHASE_RETURN_OUT,
        ];
    }
}
