<?php

namespace App\Http\Controllers\UserManagement;
 
use App\Http\Controllers\Controller;
use App\Models\UserManagement\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\UserManagement\Permission;
use Yajra\DataTables\Facades\DataTables;
use App\Enumeration\RoleType;
use App\Models\Hrm\Employee;


class UserController extends Controller
{
    public function index()
    {
        return view('user_management.user.index');
   }
    public function dataTable()
    {
        $query = User::where('sister_concern_id',auth()->user()->activeConcernId());

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('tracking_status', function(User $user) {
                if ($user->is_tracking_enabled == 1)
                    return '<button class="btn btn-success btn-sm btn-tracking-status" data-id="'.$user->id.'">Enabled</button>';
                else
                    return '<button class="btn btn-danger btn-sm btn-tracking-status" data-id="'.$user->id.'">Disabled</button>';
            })
            ->editColumn('role', function(User $user) {
                return str_replace('_', ' ', $user->role);
            })
            ->addColumn('action', function(User $user) {
                $btn = '';
                // if(auth()->user()->can('user_edit')) {
                //     $btn .= ' <a role="button" data-id="' . $user->id . '" class="btn btn-info bg-gradient-info btn-sm btn-generate-otp">Generate OTP</a><br/>';
                // }
                if(auth()->user()->can('user_permission_manage')) {
                    $btn .= '<a href="' . route('user.permission', ['user' => $user->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-sync"></i> Permission</a><br/>';
                }
                if(auth()->user()->can('user_edit')) {
                    $btn .= ' <a href="' . route('user.edit', ['user' => $user->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if(auth()->user()->can('user_delete')) {
                    $btn .= ' <a role="button" data-id="' . $user->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('otp_expires_at',function(User $user){
                return $user->otp_expires_at ? date('Y-m-d h:i:s',strtotime($user->otp_expires_at)):'';
            })
            ->addColumn('status', function(User $user) {
              if ($user->status == 1)
                return '<span class="badge badge-success">Active</span>';
              else
                return '<span class="badge badge-danger">Inactive</span>';
            })
            ->rawColumns(['action','status','tracking_status'])
            ->toJson();
    }

    public function permission(User $user)
    {
        $permissionQuery = Permission::with('children')
            ->orderBy('sort')
            ->whereNull('parent_id');

        $permissions = $permissionQuery->get();

        return view('user_management.user.permission',compact('user','permissions'));
    }
    public function permissionUpdate(User $user,Request $request)
    {
        if (!auth()->user()->hasPermissionTo('user_permission_manage')) {
            abort(403, 'Unauthorized');
        }
        // Start a database transaction
        DB::beginTransaction();

        try {
            $user->syncPermissions($request->permission);
            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('user.permission',['user'=>$user->id])
                ->with('success', 'User permission updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('user.permission',['user'=>$user->id])
                ->with('error',$e->getMessage());
        }
    }
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('user_create')) {
            abort(403, 'Unauthorized');
        }

        $roles = Role::where('is_system', false)->pluck('name', 'name')->toArray();

        $employees = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->whereDoesntHave('user')
            ->orderBy('name')
            ->get();

        return view('user_management.user.create', compact('employees', 'roles'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('user_create')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
            ],
            'username' =>[
                'required','max:255',
                Rule::unique('users')
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('users')
            ],
            'mobile_no' =>[
                'nullable','max:255',
                Rule::unique('users')
            ],
            'password' => ['required', 'confirmed',Password::defaults()],
            'status' => 'required|boolean',
            'role' => ['required', Rule::in(Role::pluck('name')->toArray())],
            'roles' => ['nullable', 'array'],
            'roles.*' => [Rule::in(Role::pluck('name')->toArray())],
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->where(function($query) {
                    $query->where('sister_concern_id', auth()->user()->activeConcernId());
                })
            ],
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            //Validated data
            $validatedData['sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['email_verified_at'] = Carbon::now();
            $validatedData['password'] = bcrypt($request->password);
            
            // Set role and employee_id
            $validatedData['role'] = $request->role;
            $validatedData['employee_id'] = $request->employee_id;

            // Create a new User record in the database
            $user = User::create($validatedData);

            // Sync Spatie roles (primary + additional)
            $allRoles = array_unique(array_filter(array_merge([$request->role], $request->input('roles', []))));
            $user->syncRoles($allRoles);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('user.index')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('user.create')->with('error', 'An error occurred while creating the user : '.$e->getMessage());
        }
   }

    public function edit(User $user)
    {
        if (!auth()->user()->hasPermissionTo('user_edit')) {
            abort(403, 'Unauthorized');
        }

        $roles = Role::where('is_system', false)->pluck('name', 'name')->toArray();
        $assignedRoles = $user->roles->pluck('name')->toArray();
        $secondaryRoles = array_diff($assignedRoles, [$user->role]);
        $isSystemUser = Role::where('name', $user->role)->where('is_system', true)->exists();

        $employees = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where(function($query) use ($user) {
                $query->whereDoesntHave('user')
                      ->orWhere('id', $user->employee_id);
            })
            ->orderBy('name')
            ->get();

        return view('user_management.user.edit', compact('user', 'employees', 'roles', 'secondaryRoles', 'isSystemUser'));
    }

    public function update(User $user,Request $request)
    {
        if (!auth()->user()->hasPermissionTo('user_edit')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
            ],
            'username' =>[
                'required','max:255',
                Rule::unique('users')
                ->ignore($user)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('users')
                    ->ignore($user)
            ],
            'mobile_no' =>[
                'nullable','max:255',
                Rule::unique('users')
                    ->ignore($user)
            ],
            'password' => ['nullable', 'confirmed',Password::defaults()],
            'status' => 'required|boolean',
            'role' => ['required', Rule::in(Role::pluck('name')->toArray())],
            'roles' => ['nullable', 'array'],
            'roles.*' => [Rule::in(Role::pluck('name')->toArray())],
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->where(function($query) {
                    $query->where('sister_concern_id', auth()->user()->activeConcernId());
                })
            ],
        ]);
        // Start a database transaction
        DB::beginTransaction();

        try {
            if ($request->password != ''){
                $validatedData['password'] = bcrypt($request->password);
            }else{
                unset($validatedData['password']);
            }
            
            // If the user already holds a system-managed role, preserve it server-side
            $isSystemUser = Role::where('name', $user->role)->where('is_system', true)->exists();
            if ($isSystemUser) {
                $validatedData['role'] = $user->role;
            } else {
                $validatedData['role'] = $request->role;
            }
            $validatedData['employee_id'] = $request->employee_id;

            // Create a new User record in the database
            $user->update($validatedData);

            // Sync Spatie roles (primary + additional)
            $allRoles = array_unique(array_filter(array_merge([$validatedData['role']], $request->input('roles', []))));
            $user->syncRoles($allRoles);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('user.index')->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('user.edit',['user'=>$user->id])->with('error', 'An error occurred while updating the user : '.$e->getMessage());
        }
   }

    public function destroy(User $user)
    {
        try {
            if (!auth()->user()->hasPermissionTo('user_delete')) {
                abort(403, 'Unauthorized');
            }
            // Delete the User record
            $user->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'User deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'User not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
    public function generateOtp(User $user){
        try{
            if (!auth()->user()->hasPermissionTo('user_edit')) {
                abort(403, 'Unauthorized');
            }
            // Update the User record
            $otp = rand(100000, 999999);

            $user->otp = $otp;
            $user->otp_expires_at = Carbon::now()->addMinutes(10);
            $user->save();
            
            return response()->json(['success'=>true,'message' => 'OTP generated successfully'], Response::HTTP_OK);
        }catch(\Exception $e){
            return response()->json(['success'=>false,'message' => 'User not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
    public function trackingStatusUpdate(User $user)
    {
        try {
            if (!auth()->user()->hasPermissionTo('user_edit')) {
                abort(403, 'Unauthorized');
            }
            $user->is_tracking_enabled = $user->is_tracking_enabled == 1 ? 0 : 1;
            $user->save();

            return response()->json(['success' => true, 'message' => 'Tracking status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


}
