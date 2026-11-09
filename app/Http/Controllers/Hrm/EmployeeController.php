<?php

namespace App\Http\Controllers\Hrm;

use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedDesignation;
use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hrm\StoreEmployeeRequest;
use App\Http\Requests\Hrm\UpdateEmployeeRequest;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\AttendanceReminderLog;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployeeStatus;
use App\Models\Hrm\Shift;
use App\Models\Payroll\SalaryStructure;
use App\Models\User;
use App\Services\GeocodingService;
use App\Traits\UploadTrait;
use Carbon\Carbon;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    use UploadTrait;

    // =========================================================================
    // CRUD — Read
    // =========================================================================

    public function index()
    {
        return view('hrm.employees.index');
    }

    public function show(Employee $employee)
    {
        return view('hrm.employees.profile', compact('employee'));
    }

    public function card(Employee $employee)
    {
        $employee->load(
            'designation', 'department', 'sisterConcern',
            'currentDistrict', 'currentUpazila', 'currentUnion',
            'currentPourashava', 'currentVillage'
        );
        return view('hrm.employees.card', compact('employee'));
    }

    public function dataTable()
    {
        $query = Employee::with('department', 'designation', 'employeeStatus', 'shift')
            ->where('sister_concern_id', auth()->user()->activeConcernId());

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Employee $employee) {
                $btn  = '<a href="' . route('employees.show', $employee->id) . '" target="_blank" class="btn btn-primary btn-sm mb-1"><i class="fa fa-eye"></i></a> ';
                if (auth()->user()->hasPermissionTo('employee_edit')) {
                    $btn .= '<a href="' . route('employees.edit', $employee->id) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }
                if (auth()->user()->hasPermissionTo('employee_delete')) {
                    $btn .= '<a role="button" data-id="' . $employee->id . '" class="btn btn-danger btn-sm btn-delete mb-1"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('address', fn (Employee $employee) =>
                '<button type="button" class="btn btn-primary bg-gradient-primary btn-sm btn-address" data-id="' . $employee->id . '"><i class="fa fa-map-marker-alt"></i> Address</button>'
            )
            ->addColumn('department_name',       fn (Employee $e) => $e->department?->name ?? '')
            ->addColumn('designation_name',      fn (Employee $e) => $e->designation?->name ?? '')
            ->addColumn('employee_status_name',  fn (Employee $e) => $e->employeeStatus?->name ?? '')
            ->rawColumns(['action', 'address'])
            ->toJson();
    }

    public function getEmployeeAddressDetails(Request $request)
    {
        $employee = Employee::with(
            'currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion', 'currentPourashava', 'currentVillage',
            'permanentDivision', 'permanentDistrict', 'permanentUpazila', 'permanentUnion', 'permanentPourashava', 'permanentVillage'
        )->find($request->id);

        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found']);
        }

        return response()->json([
            'success' => true,
            'html'    => view('hrm.employees.address_details', compact('employee'))->render(),
        ]);
    }

    // =========================================================================
    // CRUD — Create
    // =========================================================================

    public function create()
    {
        abort_if(! auth()->user()->hasPermissionTo('employee_create'), 403, 'Unauthorized');

        $departments     = Department::get();
        $shifts          = Shift::get();
        $employeeStatuses = EmployeeStatus::orderBy('sort')->get();
        $divisions       = Division::all();
        $salaryStructures = SalaryStructure::where('status', 1)->get();
        $nextId          = Employee::generateEmployeeId(auth()->user()->activeConcernId());

        return view('hrm.employees.create', compact('departments', 'shifts', 'employeeStatuses', 'divisions', 'salaryStructures', 'nextId'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        // ✅ Authorization handled by StoreEmployeeRequest::authorize()
        // ✅ Validation handled by StoreEmployeeRequest::rules()

        DB::beginTransaction();
        try {
            $branchId = auth()->user()->activeConcernId();

            // Ensure id_no uniqueness (race condition safety)
            $idNo = Employee::where('id_no', $request->id_no)->exists()
                ? Employee::generateEmployeeId($branchId)
                : $request->id_no;

            // Handle file uploads
            $files = $this->handleEmployeeFileUploads($request);

            $dob = null;
            if ($request->date_of_birth) {
                try {
                    $dob = Carbon::createFromFormat('d-m-Y', $request->date_of_birth)->format('Y-m-d');
                } catch (\Exception $e) {
                    $dob = Carbon::parse($request->date_of_birth)->format('Y-m-d');
                }
            }

            // Build employee data
            $employeeData = array_merge(
                $request->only(['name', 'mobile_no', 'email', 'gender', 'blood_group', 'marital_status', 'gross_salary', 'bank_name', 'bank_account_no', 'salary_structure_id']),
                $this->resolveAddressData($request),
                [
                    'date_of_birth'       => $dob,
                    'id_no'               => $idNo,
                    'sister_concern_id'   => $branchId,
                    'create_user_id'      => auth()->id(),
                    'department_id'       => $request->department,
                    'designation_id'      => $request->designation,
                    'employee_status_id'  => $request->employee_status,
                    'shift_id'            => $request->shift,
                    'photo'               => $files['photo'],
                    'signature_photo'     => $files['signature_photo'],
                    'nid'                 => $files['nid'],
                    'cv'                  => $files['cv'],
                ]
            );

            $employee = Employee::create($employeeData);

            // Create linked User account
            $this->createUserAccount($employee, $idNo, $request);

            // Create linked Accounting Head
            $this->createAccountHead($employee, $idNo, $request->name);

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // CRUD — Update
    // =========================================================================

    public function edit(Employee $employee)
    {
        abort_if(! auth()->user()->hasPermissionTo('employee_edit'), 403, 'Unauthorized');
        abort_if($employee->sister_concern_id !== auth()->user()->activeConcernId(), 403, 'Unauthorized');

        $departments      = Department::get();
        $shifts           = Shift::get();
        $employeeStatuses = EmployeeStatus::orderBy('sort')->get();
        $divisions        = Division::all();
        $salaryStructures = SalaryStructure::where('status', 1)->get();

        return view('hrm.employees.edit', compact('employee', 'departments', 'shifts', 'employeeStatuses', 'divisions', 'salaryStructures'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        // ✅ Authorization handled by UpdateEmployeeRequest::authorize()
        // ✅ Validation handled by UpdateEmployeeRequest::rules()

        abort_if($employee->sister_concern_id !== auth()->user()->activeConcernId(), 403, 'Unauthorized');

        DB::beginTransaction();
        try {
            // Handle file uploads (keep old if no new file uploaded)
            $files = $this->handleEmployeeFileUploads($request, $employee);

            $dob = null;
            if ($request->date_of_birth) {
                try {
                    $dob = Carbon::createFromFormat('d-m-Y', $request->date_of_birth)->format('Y-m-d');
                } catch (\Exception $e) {
                    $dob = Carbon::parse($request->date_of_birth)->format('Y-m-d');
                }
            }

            $employeeData = array_merge(
                $request->only(['name', 'mobile_no', 'email', 'gender', 'blood_group', 'marital_status', 'gross_salary', 'bank_name', 'bank_account_no', 'salary_structure_id']),
                $this->resolveAddressData($request),
                [
                    'date_of_birth'       => $dob,
                    'alter_user_id'       => auth()->id(),
                    'employee_status_id'  => $request->employee_status,
                    'shift_id'            => $request->shift,
                    'photo'               => $files['photo'],
                    'signature_photo'     => $files['signature_photo'],
                    'nid'                 => $files['nid'],
                    'cv'                  => $files['cv'],
                ]
            );

            $employee->update($employeeData);

            // Sync linked User
            $user = User::where('employee_id', $employee->id)->first();
            if ($user) {
                $user->update([
                    'name'      => $request->name,
                    'username'  => $request->id_no,
                    'mobile_no' => $request->mobile_no,
                    'email'     => $request->email,
                ]);
            }

            // Sync linked Account Head
            $accountHead = AccountHead::where('employee_id', $employee->id)->first();
            if ($accountHead) {
                $accountHead->update(['name' => $request->name . ' ID No. ' . $request->id_no]);
            }

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update employee: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // CRUD — Delete
    // =========================================================================

    public function destroy(Employee $employee)
    {
        abort_if(! auth()->user()->hasPermissionTo('employee_delete'), 403, 'Unauthorized');

        try {
            $employee->delete();
            return response()->json(['success' => true, 'message' => 'Employee deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete employee: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // Attendance Methods
    // =========================================================================

    public function employeeAttendance()
    {
        $user = auth()->user();
        if ($user) {
            $user->unreadNotifications
                ->where('type', 'App\Notifications\Hrm\EmployeeAttendanceReminder')
                ->markAsRead();
        }
        return view('hrm.employee_attendance.create');
    }

    public function employeeAttendanceSuccess()
    {
        return view('hrm.employee_attendance.success');
    }

    public function employeeAttendancePost(Request $request)
    {
        if (! auth()->user()->employee_id) {
            return back()->with('error', 'You are not authorized to give attendance because you are not an employee.');
        }

        $request->validate([
            'employee_photo_src' => 'required',
            'latitude'           => 'required',
            'longitude'          => 'required',
        ], [
            'employee_photo_src.required' => 'Photo is required for attendance.',
            'latitude.required'           => 'Location is required. Please allow location access.',
            'longitude.required'          => 'Location is required. Please allow location access.',
        ]);

        $latestReminder = AttendanceReminderLog::where('employee_id', auth()->user()->employee_id)
            ->whereDate('notified_at', Carbon::today())
            ->latest('notified_at')
            ->first();

        if ($latestReminder) {
            if ($latestReminder->status === 'responded') {
                return redirect()->back()->withErrors(['error' => 'আপনি ইতিমধ্যে এই নোটিফিকেশনের জন্য অ্যাটেন্ডেন্স দিয়েছেন! পরবর্তী নোটিফিকেশনের জন্য অপেক্ষা করুন।']);
            }
            $isMissed = $latestReminder->status === 'missed' ||
                ($latestReminder->status === 'pending' && $latestReminder->window_closes_at < Carbon::now());
            if ($isMissed) {
                return redirect()->back()->withErrors(['error' => 'আপনার অ্যাটেন্ডেন্স দেওয়ার সময় শেষ! পরবর্তী নোটিফিকেশনের জন্য অপেক্ষা করুন।']);
            }
        }

        try {
            $locationAddress = $this->getLocationName($request->latitude, $request->longitude);
            $imageName       = $request->employee_photo_src
                ? $this->uploadBase64Image($request->employee_photo_src, 'attendance', 800)
                : null;

            $attendance = Attendance::create([
                'sister_concern_id' => auth()->user()->activeConcernId(),
                'employee_id'       => auth()->user()->employee_id,
                'punch_time'        => Carbon::now(),
                'location_address'  => $locationAddress,
                'attendance_image'  => $imageName,
                'note'              => $request->note,
            ]);

            // Mark pending reminder as responded
            AttendanceReminderLog::where('employee_id', auth()->user()->employee_id)
                ->where('status', 'pending')
                ->where('window_closes_at', '>=', Carbon::now())
                ->latest('notified_at')
                ->first()
                ?->update(['status' => 'responded', 'responded_at' => Carbon::now()]);

            return redirect()->route('employees.attendance.success')->with('message', 'Attendance successfully submitted.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to give attendance: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Private Helper Methods
    // =========================================================================

    /**
     * Handle all 4 employee file uploads (photo, signature, nid, cv).
     * For update operations, pass the existing $employee to retain old files.
     */
    private function handleEmployeeFileUploads(Request $request, ?Employee $employee = null): array
    {
        $files = [
            'photo'           => $employee?->photo,
            'signature_photo' => $employee?->signature_photo,
            'nid'             => $employee?->nid,
            'cv'              => $employee?->cv,
        ];

        $uploadMap = [
            'photo'           => 'uploads/employee/photo',
            'signature_photo' => 'uploads/employee/signature',
            'nid'             => 'uploads/employee/nid',
            'cv'              => 'uploads/employee/cv',
        ];

        foreach ($uploadMap as $field => $destinationPath) {
            if ($request->hasFile($field)) {
                // Delete old file if it exists (update only)
                if ($employee && $employee->{$field} && file_exists(public_path($employee->{$field}))) {
                    @unlink(public_path($employee->{$field}));
                }
                $file     = $request->file($field);
                $filename = Uuid::uuid1()->toString() . '.' . $file->extension();
                $file->move(public_path($destinationPath), $filename);
                $files[$field] = $destinationPath . '/' . $filename;
            }
        }

        return $files;
    }

    /**
     * Resolve Union/Pourashava address fields from the raw request
     * and return a flat array ready to merge into Employee data.
     */
    private function resolveAddressData(Request $request): array
    {
        $data = [
            'current_division_id'  => $request->current_division,
            'current_district_id'  => $request->current_district,
            'current_upazila_id'   => $request->current_upazila,
            'current_union_id'     => null,
            'current_pourashava_id' => null,
            'current_village_id'   => $request->current_village_id,
        ];

        // Resolve current union/pourashava
        if ($request->current_union) {
            if (str_starts_with($request->current_union, 'union_')) {
                $data['current_union_id'] = str_replace('union_', '', $request->current_union);
            } elseif (str_starts_with($request->current_union, 'pourashava_')) {
                $data['current_pourashava_id'] = str_replace('pourashava_', '', $request->current_union);
            }
        }

        // Permanent address: same as current OR separate
        $sameAsCurrent = $request->has('same_as_current')
            || $request->same_as_current == 'on'
            || $request->same_as_current == 1;

        if ($sameAsCurrent) {
            $data['permanent_division_id']   = $data['current_division_id'];
            $data['permanent_district_id']   = $data['current_district_id'];
            $data['permanent_upazila_id']    = $data['current_upazila_id'];
            $data['permanent_union_id']      = $data['current_union_id'];
            $data['permanent_pourashava_id'] = $data['current_pourashava_id'];
            $data['permanent_village_id']    = $data['current_village_id'];
        } else {
            $data['permanent_division_id']   = $request->permanent_division;
            $data['permanent_district_id']   = $request->permanent_district;
            $data['permanent_upazila_id']    = $request->permanent_upazila;
            $data['permanent_union_id']      = null;
            $data['permanent_pourashava_id'] = null;
            $data['permanent_village_id']    = $request->permanent_village_id;

            if ($request->permanent_union) {
                if (str_starts_with($request->permanent_union, 'union_')) {
                    $data['permanent_union_id'] = str_replace('union_', '', $request->permanent_union);
                } elseif (str_starts_with($request->permanent_union, 'pourashava_')) {
                    $data['permanent_pourashava_id'] = str_replace('pourashava_', '', $request->permanent_union);
                }
            }
        }

        return $data;
    }

    /**
     * Designation-এর reserved_id দেখে সঠিক RoleType নির্ধারণ করো।
     * ReservedDesignation → RoleType mapping।
     */
    private function resolveRoleFromDesignation(int $designationId): string
    {
        $designation = Designation::find($designationId);
        $reservedId  = $designation?->reserved_id;

        return match ((int) $reservedId) {
            ReservedDesignation::$AREA_MANAGER    => RoleType::$AREA_MANAGER,
            ReservedDesignation::$BRANCH_MANAGER  => RoleType::$BRANCH_MANAGER,
            ReservedDesignation::$SR              => RoleType::$SR,
            ReservedDesignation::$FIELD_OFFICER   => RoleType::$FIELD_OFFICER,
            ReservedDesignation::$TECHNICIAN      => RoleType::$TECHNICIAN,
            ReservedDesignation::$TRAINEE         => RoleType::$TRAINEE,
            default                               => RoleType::$EMPLOYEE,
        };
    }

    /**
     * Create the system User account linked to the new Employee.
     * Role is auto-determined from the Employee's Designation.
     */
    private function createUserAccount(Employee $employee, string $idNo, Request $request): void
    {
        $role = $this->resolveRoleFromDesignation((int) $request->designation);

        $user = User::create([
            'sister_concern_id' => auth()->user()->activeConcernId(),
            'name'              => $request->name,
            'username'          => $idNo,
            'mobile_no'         => $request->mobile_no,
            'email'             => $request->email,
            'password'          => bcrypt($idNo),
            'employee_id'       => $employee->id,
            'role'              => $role,
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);
    }

    /**
     * Create the Accounting Head entry linked to the new Employee.
     */
    private function createAccountHead(Employee $employee, string $idNo, string $name): void
    {
        $accountGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCRUED_EXPENSES)->first();

        AccountHead::create([
            'sister_concern_id' => null,
            'code'              => generateAccountCode($accountGroup->top_reserved_id),
            'account_group_id'  => $accountGroup->id,
            'name'              => $name . ' ID No. ' . $idNo,
            'employee_id'       => $employee->id,
            'is_system'         => 1,
            'is_editable'       => 0,
        ]);
    }

    /**
     * Geocoding with cache — round coordinates for better cache hit rate.
     */
    public function getLocationName(float $latitude, float $longitude): string
    {
        $latRound = round($latitude, 4);
        $lonRound = round($longitude, 4);
        $cacheKey = "location_{$latRound}_{$lonRound}";

        // ✅ শুধু successful API response cache করা হয়।
        // API fail হলে (null) cache করা হয় না — পরের request-এ আবার try করতে পারবে।
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $locationName = app(GeocodingService::class)->getAddress($latitude, $longitude);

        if ($locationName) {
            Cache::put($cacheKey, $locationName, 3600); // 1 ঘণ্টা cache
            return $locationName;
        }

        // সব API fail — raw coordinate return করো, কিন্তু cache করো না
        return "Lat: {$latitude}, Lon: {$longitude}";
    }
}
