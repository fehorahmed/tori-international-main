<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class EmiSaleDetail extends Model
{
    protected $fillable = [
        'sale_order_id',
        'customer_nid_attachment',
        'customer_photo_attachment',
        'guarantor_name',
        'guarantor_phone',
        'guarantor_nid',
        'guarantor_nid_attachment',
        'security_cheque_attachment',
        'agreement_attachment',
        'late_fee_type',
        'late_fee_rate',
    ];

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'sale_order_id');
    }
}
