<?php

namespace App\Http\Controllers\SmsManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SmsManagement\SmsTemplate;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $templates = SmsTemplate::all();
        return view('sms_management.templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_name'  => 'required|string|unique:sms_templates,event_name',
            'title'       => 'required|string',
            'body'        => 'required|string',
            'available_variables' => 'nullable|string',
        ]);

        SmsTemplate::create([
            'event_name'  => $request->event_name,
            'title'       => $request->title,
            'body'        => $request->body,
            'available_variables' => $request->available_variables,
            'is_active'   => true,
            'created_by'  => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'SMS Template Added Successfully');
    }

    public function update(Request $request, SmsTemplate $template)
    {
        $request->validate([
            'title'       => 'required|string',
            'body'        => 'required|string',
            'available_variables' => 'nullable|string',
        ]);

        $template->update([
            'title'       => $request->title,
            'body'        => $request->body,
            'available_variables' => $request->available_variables,
            'updated_by'  => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'SMS Template Updated Successfully');
    }

    public function toggleStatus(SmsTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return response()->json([
            'success' => true, 
            'message' => 'Status updated successfully', 
            'is_active' => $template->is_active
        ]);
    }
}
