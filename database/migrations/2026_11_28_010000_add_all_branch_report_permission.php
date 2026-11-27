<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration {
    public function up(): void
    {
        // Existing Admin role has full permissions; other roles opt in explicitly.
        $permission = Permission::firstOrCreate(['name' => 'all_branch_permission', 'guard_name' => 'web'], ['sort' => 99]);
        if ($admin = \Spatie\Permission\Models\Role::where('name', 'Admin')->where('guard_name', 'web')->first()) $admin->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void
    {
        Permission::where('name', 'all_branch_permission')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
