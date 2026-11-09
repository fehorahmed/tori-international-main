<?php

namespace App\Http\Controllers\SmsManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SmsManagement\SmsLog;
use Yajra\DataTables\Facades\DataTables;

class SmsLogController extends Controller
{
    public function index()
    {
        return view('sms_management.logs.index');
    }

    public function dataTable()
    {
        $logs = SmsLog::query()->orderBy('id', 'desc');

        return DataTables::of($logs)
            ->addIndexColumn()
            ->editColumn('sent_at', function ($row) {
                return $row->sent_at ? \Carbon\Carbon::parse($row->sent_at)->format('d M, Y h:i A') : 'N/A';
            })
            ->editColumn('status', function ($row) {
                $badgeClass = 'secondary';
                if ($row->status == 'Sent') $badgeClass = 'success';
                elseif ($row->status == 'Failed') $badgeClass = 'danger';
                elseif ($row->status == 'Pending') $badgeClass = 'warning';
                
                return '<span class="badge badge-' . $badgeClass . '">' . $row->status . '</span>';
            })
            ->editColumn('response_text', function ($row) {
                if (empty($row->response_text)) return '-';
                // Truncate for display
                return '<span title="'.htmlspecialchars($row->response_text).'">' . \Str::limit($row->response_text, 30) . '</span>';
            })
            ->rawColumns(['status', 'response_text'])
            ->make(true);
    }
}
