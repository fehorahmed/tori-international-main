<?php
namespace App\Enumeration;

class  Status
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const CANCELED = 'canceled';
    const FINALIZED = 'finalized';
    const PARTIAL_PURCHASE = 'partial_purchase';
    const PARTIAL_RECEIVED = 'partial_receive';
    const RECEIVED = 'received';
    const PURCHASED = 'purchased';
    const DISMISSED = 'dismissed';
    const ESCALATED = 'escalated';
    const ON_HOLD = 'on_hold';
    const RESUBMITTED = 'resubmitted';
}
