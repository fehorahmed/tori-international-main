<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserManagement\Role;
use App\Models\UserManagement\Permission;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return view('user_management.role.index');
    }

    public function dataTable()
    {
        $query = Role::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('display_name', function (Role $role) {
                return str_replace('_', ' ', $role->name);
            })
            ->addColumn('is_system', function (Role $role) {
                if ($role->is_system) {
                    return '<span class="badge badge-danger">System Protected</span>';
                }
                return '<span class="badge badge-success">Custom</span>';
            })
            ->addColumn('action', function (Role $role) {
                $btn = '';
                if(auth()->user()->can('role_edit')) {
                    $btn .= '<a href="' . route('role.edit', ['role' => $role->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i> Edit</a>';
                }
                return $btn;
            })
            ->rawColumns(['is_system', 'action'])
            ->make(true);
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('role_create')) {
            abort(403, 'Unauthorized');
        }

        $permissions = Permission::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('sort');
            }, 'children.children' => function($q) {
                $q->orderBy('sort');
            }])
            ->orderBy('sort')
            ->get();

        return view('user_management.role.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('role_create')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permission' => 'nullable|array',
        ]);

        // Convert spaces to underscores for database key convention
        $formattedName = str_replace(' ', '_', $request->name);

        // Check uniqueness again after formatting
        if (Role::where('name', $formattedName)->exists()) {
            return redirect()->back()->withErrors(['name' => 'A role with this formatted name already exists.'])->withInput();
        }

        $role = Role::create([
            'name' => $formattedName,
            'guard_name' => 'web',
            'is_system' => false
        ]);

        if ($request->has('permission')) {
            $role->syncPermissions($request->permission);
        }

        return redirect()->route('role.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        if (!auth()->user()->hasPermissionTo('role_edit')) {
            abort(403, 'Unauthorized');
        }

        $permissions = Permission::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('sort');
            }, 'children.children' => function($q) {
                $q->orderBy('sort');
            }])
            ->orderBy('sort')
            ->get();

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('user_management.role.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if (!auth()->user()->hasPermissionTo('role_edit')) {
            abort(403, 'Unauthorized');
        }

        $rules = [
            'permission' => 'nullable|array',
        ];

        // If it's not a system role, allow renaming
        if (!$role->is_system) {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id),
            ];
        }

        $request->validate($rules);

        if (!$role->is_system) {
            $formattedName = str_replace(' ', '_', $request->name);
            
            // Check uniqueness ignoring self
            if (Role::where('name', $formattedName)->where('id', '!=', $role->id)->exists()) {
                return redirect()->back()->withErrors(['name' => 'A role with this formatted name already exists.'])->withInput();
            }

            $role->name = $formattedName;
        }

        // Special protection: Do not allow modifying Admin role permissions from this UI 
        // to prevent locking out the admin user.
        if ($role->name !== \App\Enumeration\RoleType::$ADMIN) {
            $permissions = $request->input('permission', []);
            $role->syncPermissions($permissions);
        }

        $role->save();

        return redirect()->route('role.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if (!auth()->user()->hasPermissionTo('role_delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        if ($role->is_system) {
            return response()->json(['success' => false, 'message' => 'System protected roles cannot be deleted.']);
        }

        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);
    }
}
