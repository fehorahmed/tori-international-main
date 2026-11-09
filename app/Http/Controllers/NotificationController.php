<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function notification() {
        return view('notification.all');
    }
    public function datatable()
    {
        $notifications = auth()->user()->notifications;

        return DataTables::of($notifications)
            ->addColumn('title', function ($notification) {
                $badge = is_null($notification->read_at) ? '<span class="badge badge-danger mr-1">New</span>' : '';
                return $badge . ($notification->data['title'] ?? 'Notification');
            })
            ->addColumn('content', function ($notification) {
                $url = route('notification.read', $notification->id);
                $text = $notification->data['content'] ?? $notification->data['message'] ?? 'View Details';
                return '<a href="' . $url . '" style="color: #007bff;">' . $text . '</a>';
            })
            ->addColumn('created_at', function ($notification) {
                return $notification->created_at->diffForHumans();
            })
            ->setRowClass(function ($notification) {
                return is_null($notification->read_at) ? 'bg-light font-weight-bold' : '';
            })
            ->rawColumns(['title', 'content']) 
            ->make(true);
    }
    public function markRead() {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }

    public function readAndRedirect($id) {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            $url = $notification->data['url'] ?? '/';
            return redirect($url);
        }
        return redirect()->back();
    }

    public function scrollData(Request $request)
    {

        $notifications = auth()->user()->notifications->skip($request->skip)->take(10);
        $html = view('layouts.partial.notification_template',compact('notifications'))->render();
        return response()->json([
            'html'=>$html,
            'is_data'=>count($notifications) > 0 ? true : false,
        ]);
    }
}
