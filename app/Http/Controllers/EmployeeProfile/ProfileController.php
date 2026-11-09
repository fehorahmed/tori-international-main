<?php

namespace App\Http\Controllers\EmployeeProfile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hrm\Employee;
use App\Models\EmployeeProfile\EmployeeEducation;
use App\Models\EmployeeProfile\EmployeeExperience;
use App\Models\EmployeeProfile\EmployeeDocument;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $pageTitle = 'My Profile';
        $user = auth()->user();
        $employee = $user->employee_id ? Employee::with(['educations', 'experiences', 'documents'])->find($user->employee_id) : null;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'You do not have an associated Employee Profile.');
        }

        return view('employee_profile.profile.index', compact('pageTitle', 'employee'));
    }

    public function storeEducation(Request $request)
    {
        $request->validate([
            'degree_name' => 'required|string|max:255',
            'institute' => 'required|string|max:255',
            'passing_year' => 'nullable|string|max:4',
            'result' => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail(auth()->user()->employee_id);
        
        $employee->educations()->create($request->only('degree_name', 'institute', 'passing_year', 'result'));

        return redirect()->back()->with('success', 'Education record added successfully.');
    }

    public function destroyEducation($id)
    {
        $edu = EmployeeEducation::where('employee_id', auth()->user()->employee_id)->findOrFail($id);
        $edu->delete();
        return redirect()->back()->with('success', 'Education record deleted.');
    }

    public function storeExperience(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $employee = Employee::findOrFail(auth()->user()->employee_id);
        
        $data = $request->only('company_name', 'designation', 'start_date', 'end_date');
        $data['is_current'] = $request->has('is_current') ? 1 : 0;

        $employee->experiences()->create($data);

        return redirect()->back()->with('success', 'Experience record added successfully.');
    }

    public function destroyExperience($id)
    {
        $exp = EmployeeExperience::where('employee_id', auth()->user()->employee_id)->findOrFail($id);
        $exp->delete();
        return redirect()->back()->with('success', 'Experience record deleted.');
    }

    public function storeDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|max:255',
            'file' => 'required|file|max:5120', // Max 5MB
        ]);

        $employee = Employee::findOrFail(auth()->user()->employee_id);
        
        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/employee_documents', $filename, 'public');

        $employee->documents()->create([
            'document_type' => $request->document_type,
            'file_path' => 'storage/' . $path,
            'original_file_name' => $file->getClientOriginalName(),
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function destroyDocument($id)
    {
        $doc = EmployeeDocument::where('employee_id', auth()->user()->employee_id)->findOrFail($id);
        if (Storage::disk('public')->exists(str_replace('storage/', '', $doc->file_path))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $doc->file_path));
        }
        $doc->delete();
        return redirect()->back()->with('success', 'Document deleted.');
    }
}
