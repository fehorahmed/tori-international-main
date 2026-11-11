<?php

use App\Enumeration\SupplierType;
use App\Enumeration\VoucherType;
use App\Enumeration\ReservedAccountGroup;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Crm\Lead;
use App\Models\Crm\Opportunity;
use App\Models\Crm\Prospect;
use App\Models\Account\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function shortName($name){

    $firstName = Str::of($name)->before(' ');
    $lastName = Str::of($name)->after(' ');

    if ($lastName) {
        $shortName = strtoupper($firstName[0] . $lastName[0]);
    } else {
        $shortName = strtoupper($firstName[0]);
    }

    return $shortName;
}
function numberToMonthName($monthNumber)
{
    if ($monthNumber != '')
        return Carbon::create(null, $monthNumber, 1)->format('F');
    else
        return '';
}

function ordinalSuffix($number)
{
    if ($number % 10 == 1 && $number % 100 != 11) {
        return 'st';
    } elseif ($number % 10 == 2 && $number % 100 != 12) {
        return 'nd';
    } elseif ($number % 10 == 3 && $number % 100 != 13) {
        return 'rd';
    } else {
        return 'th';
    }
}

function getFiscalYear($date)
{

    $currentMonth = Carbon::parse($date)->format('m');
    if ($currentMonth < 7) {
        $firstYear = Carbon::parse($date)->format('Y') - 1;
        $secondYear = Carbon::parse($date)->format('Y');
        $fiscalYear = $firstYear . '-' . $secondYear;
    } else {
        $firstYear = Carbon::parse($date)->format('Y');
        $secondYear = $firstYear + 1;
        $fiscalYear = $firstYear . '-' . $secondYear;
    }


    return $fiscalYear;
}

function friendlyFieldName($fieldName)
{

    $fieldName = preg_replace('/\.\d+/', '', $fieldName); // Remove dot and number
    $fieldName = str_replace('_', ' ', $fieldName); // Replace underscores with spaces
    return ($fieldName);
}

function accountGroupBalance($parentId, $startDate, $endDate, $type, $sisterConcernId = null)
{
    if ($type == 1) {
        //Current Fiscal year
        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');
        // dd($start, $end);
    } else {
        //Previous Fiscal year
        $start = date('Y-m-d', strtotime('-1 year', strtotime($startDate)));
        $end = date('Y-m-d', strtotime('-1 day', strtotime($startDate)));

        // dd($start, $end);
    }

    // Get the parent AccountHeadType by ID
    $parentType = AccountGroup::find($parentId);
    // Get all child IDs recursively
    $childIds = $parentType->getAllChildIds();
    $childIds[] = $parentId;
    // Retrieve AccountHead records for the child types
    $accountHeadsIdQuery = AccountHead::whereIn('account_group_id', $childIds);
    $accountHeadsIdQuery->where(function($q) use ($sisterConcernId) {
        if ($sisterConcernId) {
            $q->where('sister_concern_id', $sisterConcernId)
              ->orWhereNull('sister_concern_id');
        } else {
            // No filter means "All Branches" or active branch?
            // Actually in reports, if sister_concern is empty, it's "All Branches"
        }
    });
    $accountHeadsId = $accountHeadsIdQuery->select('id')
        ->pluck('id')
        ->toArray();

    $transactionLogQuery = Transaction::query();
    if ($sisterConcernId) {
        $transactionLogQuery->where('sister_concern_id', $sisterConcernId);
    }
    $transactionLogQuery->select(
        DB::raw('SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) as debitTotal'),
        DB::raw('SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) as creditTotal')
    );

    $transactionLogs = $transactionLogQuery->whereIn('account_head_id', $accountHeadsId)
        ->where('date', '<=', $end)
        ->first();

    $debitOpening = 0;
    $creditOpening = 0;
    return ($transactionLogs->debitTotal ?? 0) - ($transactionLogs->creditTotal ?? 0);
}

function accountGroupBalanceIncomeStatement($parentId, $startDate, $endDate, $type, $sisterConcernId = null)
{
    if ($type == 1) {
        //Current Fiscal year
        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');
    } else {
        //Previous Fiscal year
        $start = date('Y-m-d', strtotime('-1 year', strtotime($startDate)));
        $end = date('Y-m-d', strtotime('-1 day', strtotime($startDate)));
    }

    // Get the parent AccountHeadType by ID
    $parentType = AccountGroup::find($parentId);
    // Get all child IDs recursively
    $childIds = $parentType->getAllChildIds();
    $childIds[] = $parentId;
    // Retrieve AccountHead records for the child types
    $accountHeadsIdQuery = AccountHead::whereIn('account_group_id', $childIds);
    $accountHeadsIdQuery->where(function($q) use ($sisterConcernId) {
        if ($sisterConcernId) {
            $q->where('sister_concern_id', $sisterConcernId)
              ->orWhereNull('sister_concern_id');
        }
    });
    $accountHeadsId = $accountHeadsIdQuery->select('id')
        ->pluck('id')
        ->toArray();

    $transactionLogQuery = Transaction::query();
    if ($sisterConcernId) {
        $transactionLogQuery->where('sister_concern_id', $sisterConcernId);
    }
    $transactionLogQuery->select(
        DB::raw('SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) as debitTotal'),
        DB::raw('SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) as creditTotal')
    );

    $transactionLogs = $transactionLogQuery->whereIn('account_head_id', $accountHeadsId)
        ->whereBetween('date', [$start, $end])
        ->first();

    return $transactionLogs->debitTotal - $transactionLogs->creditTotal;
}

function accountHeadBalanceIncomeStatement($accountHeadId, $startDate, $endDate, $type, $sisterConcernId = null)
{
    if ($type == 1) {
        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');
    } else {
        $start = date('Y-m-d', strtotime('-1 year', strtotime($startDate)));
        $end = date('Y-m-d', strtotime('-1 day', strtotime($startDate)));
    }

    $query = Transaction::query();
    if ($sisterConcernId) {
        $query->where('sister_concern_id', $sisterConcernId);
    }

    $result = $query
        ->where('account_head_id', $accountHeadId)
        ->whereBetween('date', [$start, $end])
        ->select(
            DB::raw('SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) as debitTotal'),
            DB::raw('SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) as creditTotal')
        )
        ->first();

    return ($result->debitTotal ?? 0) - ($result->creditTotal ?? 0);
}

function dropdownMenuContainer($elements)
{
    $html = '<div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v"></i></button>
            <div class="dropdown-menu" role="menu" style="text-align: center !important;">';
    $html .= $elements;
    $html .= '</div></div>';
    return $html;
}

function getParentBaseAmount($transaction)
{

    return Transaction::where('voucher_id', $transaction->voucher_id)
        ->where('parent_account_head_id', $transaction->account_head_id)
        ->where('other_deduct', 1)
        ->sum('amount');

}

function overtimeCalcHours($overtimeInSeconds)
{
    $hours = floor($overtimeInSeconds / 3600);
    $minutes = floor(($overtimeInSeconds % 3600) / 60);
    $seconds = $overtimeInSeconds % 60;

    $result = '';

    // Only append hours if greater than 0
    if ($hours > 0) {
        $result .= "{$hours}h ";
    }

    // Only append minutes if greater than 0
    if ($minutes > 0) {
        $result .= "{$minutes}m ";
    }

    // Only append seconds if greater than 0
    if ($seconds > 0) {
        $result .= "{$seconds}s";
    }

    // Trim any extra spaces from the result
    return trim($result);
}

function monthDayCount($year, $month)
{
    return Carbon::createFromDate($year, $month, 1)->daysInMonth;
}

function hasQuestionMark($link)
{
    return strpos($link, '?') !== false;
}

function readNotification($notification_id)
{
    $notification = auth()->user()->Notifications->find($notification_id);
    if ($notification) {
        $notification->markAsRead();
    }
}

function generateOrder($type)
{
    // Determine the prefix based on the type ('raw' or 'finished')
    if ($type === 1) {
        $prefix = 'LD-';
        $maxNumber = Lead::where('order_no', 'LIKE', "{$prefix}%")
            ->select(DB::raw("MAX(CAST(SUBSTRING(order_no, LENGTH('{$prefix}') + 1) AS UNSIGNED)) as max_number"))
            ->value('max_number');
    } elseif ($type === 2) {
        $prefix = 'PROS-';
        $maxNumber = Prospect::where('order_no', 'LIKE', "{$prefix}%")
            ->select(DB::raw("MAX(CAST(SUBSTRING(order_no, LENGTH('{$prefix}') + 1) AS UNSIGNED)) as max_number"))
            ->value('max_number');
    } elseif ($type === 3) {
        $prefix = 'OPP-';
        $maxNumber = Opportunity::where('order_no', 'LIKE', "{$prefix}%")
            ->select(DB::raw("MAX(CAST(SUBSTRING(order_no, LENGTH('{$prefix}') + 1) AS UNSIGNED)) as max_number"))
            ->value('max_number');
    } else {
        throw new Exception('Invalid type');
    }


    // If there is no existing barcode, start from 1
    $newNumber = $maxNumber ? $maxNumber + 1 : 1;

    // Pad the number to 4 digits if it's less than 10000, otherwise let it grow naturally
    if ($newNumber < 10000) {
        $newBarcode = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    } else {
        $newBarcode = $prefix . $newNumber; // No padding once number exceeds 9999
    }

    return $newBarcode;
}

function generateAccountCode($topReservedId = null)
{
    if ($topReservedId) {
        $targetLength = strlen((string)$topReservedId) + 3;

        $maxCodeInTopParent = AccountHead::where('code', 'like', $topReservedId . '%')
            ->whereRaw('LENGTH(code) = ?', [$targetLength])
            ->max('code');
        
        return $maxCodeInTopParent ? $maxCodeInTopParent + 1 : $topReservedId . '001';
    }

    return AccountHead::max('code') ? AccountHead::max('code') + 1 : 1001;
}

function reservedAccountHeadId($reservedId) 
{
    return AccountHead::where('reserved_id', $reservedId)->first()->id ?? null;
}

function reservedAccountGroupId($reservedId) 
{
    return AccountGroup::where('reserved_id', $reservedId)->first()->id ?? null;
}

function generateOrderNo($table, $column = null, $prefix = null)
{
    if (!$column) {
        $column = 'order_no';
    }

    // Modify query to include prefix conditionally
    $maxNumberQuery = DB::table($table)->where('sister_concern_id',auth()->user()->activeConcernId());
    if ($prefix) {
        $maxNumberQuery->where($column, 'LIKE', "{$prefix}%");
    }

    $maxNumber = $maxNumberQuery->select(DB::raw("MAX(CAST(SUBSTRING($column, LENGTH('{$prefix}') + 1) AS UNSIGNED)) as max_number"))
        ->value('max_number');

    $newNumber = $maxNumber ? $maxNumber + 1 : 1;

    // Format new order number based on the new number
    if ($newNumber < 10000) {
        $newOrderNo = ($prefix ?? '') . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    } else {
        $newOrderNo = ($prefix ?? '') . $newNumber;
    }

    return $newOrderNo;
}

function generatePartyMemberNo($branchId, $type)
{
    $branch = SisterConcern::find($branchId);
    $prefix = $branch && $branch->code ? $branch->code : 'BR';

    $maxNumberQuery = DB::table('suppliers')->where('member_no', 'LIKE', "{$prefix}%");

    if ($type == SupplierType::$SUPPLIER) {
        $maxNumberQuery->where('type', SupplierType::$SUPPLIER);
    } else {
        $maxNumberQuery->whereIn('type', [
            SupplierType::$CUSTOMER,
            SupplierType::$DEALER,
            SupplierType::$CORPORATE_CUSTOMER
        ]);
    }

    $maxNumber = $maxNumberQuery->select(DB::raw("MAX(CAST(SUBSTRING(member_no, LENGTH('{$prefix}') + 1) AS UNSIGNED)) as max_number"))
        ->value('max_number');

    $newNumber = $maxNumber ? $maxNumber + 1 : 1;

    return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
}

function generateVoucherNo($voucherType, $table = null, $sisterConcernId = null)
{

    if ($voucherType == VoucherType::$JOURNAL_VOUCHER) {
        $prefix = 'JV-';
    } elseif ($voucherType == VoucherType::$PAYMENT_VOUCHER) {
        $prefix = 'PV-';
    } elseif ($voucherType == VoucherType::$COLLECTION_VOUCHER) {
        $prefix = 'MRV-';
    } elseif ($voucherType == VoucherType::$CONTRA_VOUCHER) {
        $prefix = 'CNV-';
    } else {
        throw new Exception('Invalid voucher type');
    }
    if (!$table) {
        $table = 'vouchers';
    }

    $scId = $sisterConcernId ?? auth()->user()->activeConcernId();

    if (!$scId) {
        throw new \Exception('Sister concern (Branch) is required to generate a voucher.');
    }

    $query = DB::table($table)->where('sister_concern_id', $scId);

    $maxNumber = $query->where('voucher_no', 'LIKE', "{$prefix}%")
        ->select(DB::raw("MAX(CAST(SUBSTRING(voucher_no, LENGTH('{$prefix}') + 1) AS UNSIGNED)) as max_number"))
        ->value('max_number');
    $newNumber = $maxNumber ? $maxNumber + 1 : 1;
    if ($newNumber < 10000) {
        $newVoucherNo = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    } else {
        $newVoucherNo = $prefix . $newNumber;
    }
    return $newVoucherNo;
}

function get_status_badge_class($status)
{
    switch ($status) {
        case 'pending':
            return 'badge bg-warning text-dark'; // Yellow badge for pending
        case 'approved':
            return 'badge bg-success'; // Green badge for approved
        case 'rejected':
            return 'badge bg-danger'; // Red badge for rejected
        case 'canceled':
            return 'badge bg-danger'; // Grey badge for canceled
        case 'finalized':
            return 'badge bg-info'; // Light blue badge for finalized
        case 'partial_purchase':
            return 'badge bg-warning text-dark'; // Yellow badge for partial purchase
        case 'purchased':
            return 'badge bg-primary'; // Blue badge for purchased
        case 'partial_receive':
            return 'badge bg-info'; // Yellow badge for partial purchase
        case 'received':
            return 'badge bg-success'; // Blue badge for purchased
        case 'dismissed':
            return 'badge bg-danger'; // Black badge for dismissed
        case 'escalated':
            return 'badge bg-danger'; // Red badge for escalated
        case 'on_hold':
            return 'badge bg-secondary'; // Grey badge for on hold
        case 'resubmitted':
            return 'badge bg-info'; // Light blue badge for resubmitted
        default:
            return 'badge bg-light text-dark'; // Default badge for unknown status
    }
}

if (!function_exists('wrapNegative')) {
    function wrapNegative($amount)
    {
        return $amount < 0 ? '(' . number_format(abs($amount), 2) . ')' : number_format($amount, 2);
    }
}
if (!function_exists('wrapPositive')) {
    function wrapPositive($amount)
    {
        return $amount > 0 ? '(' . number_format($amount, 2) . ')' : number_format(abs($amount), 2);
    }
}

if (!function_exists('renderZoomGalleryImage')) {
    function renderZoomGalleryImage($imagePath, $title = '')
    {
        if (empty($imagePath)) {
            $imagePath = 'img/no-image.webp';
        }
        $preview_html = '<div class="zoom-gallery">';
        $preview_html .= '<a class="image_preview" title="'. htmlspecialchars($title) .'" data-source="'. asset($imagePath) .'" href="'. asset($imagePath) .'"><img height="50px" loading="lazy" src="'.asset($imagePath).'"></a>';
        $preview_html .= '</div>';
        return $preview_html;
    }
}

/** Read a typed value, e.g. membership_transaction_setting('cashout.maximum_amount'). */
function membership_transaction_setting(string $key, mixed $default = null): mixed
{
    return \App\Models\MembershipTransactionSetting::read($key, $default);
}

/** Read a complete group in one query, keeping financial quote snapshots consistent. */
function membership_transaction_settings(string $group): object
{
    return \App\Models\MembershipTransactionSetting::group($group);
}
