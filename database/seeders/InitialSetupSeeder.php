<?php

namespace Database\Seeders;

use App\Enumeration\RoleType;
use App\Models\Ecommerce\Setting;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Settings\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class InitialSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('permissions')->truncate();
        DB::table('sister_concerns')->truncate();
        DB::table('users')->truncate();
        DB::table('warehouses')->truncate();
        DB::table('settings')->truncate();
        DB::table('model_has_permissions')->truncate();

        // 1. Seed Permissions using PermissionSeeder
        $this->call(PermissionSeeder::class);


        // 2. Create Default Sister Concern
        $sisterConcern = SisterConcern::create([
            'name' => 'Head Office',
            'code' => '101',
            'contact' => '01700000000',
            'email' => 'admin@erp.com',
            'address' => 'Dhaka, Bangladesh',
            'is_head_office' => 1,
            'status' => 1
        ]);

        // 3. Create Admin
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@erp.com',
            'password' => Hash::make('12345678'),
            'role' => RoleType::$ADMIN,
            'sister_concern_id' => $sisterConcern->id,
            'status' => 1,
            'email_verified_at' => Carbon::now(),
        ]);

        // 5. Create Default Warehouse
        Warehouse::create([
            'sister_concern_id' => $sisterConcern->id,
            'name' => 'Main Warehouse',
            'type' => 'main',
            'address' => 'Dhaka',
            'status' => 1
        ]);

        Warehouse::create([
            'sister_concern_id' => $sisterConcern->id,
            'name' => 'Transit Warehouse',
            'type' => 'transit',
            'address' => 'Dhaka',
            'status' => 1
        ]);

        Warehouse::create([
            'sister_concern_id' => $sisterConcern->id,
            'name' => 'Damage Warehouse',
            'type' => 'damage',
            'address' => 'Dhaka',
            'status' => 1
        ]);

        // 6. Create Default Setting
        Setting::create([
            'primary_color' => '#70c332',
            'status' => 1
        ]);

        // 7. Create Spatie Roles and Assign Permissions
        $roles = [
            RoleType::$ADMIN,
            RoleType::$AREA_MANAGER,
            RoleType::$BRANCH_MANAGER,
            RoleType::$SR,
            RoleType::$TRAINEE,
            RoleType::$CUSTOMER,
            RoleType::$FIELD_OFFICER,
            RoleType::$EMPLOYEE,
            RoleType::$TECHNICIAN
        ];

        foreach ($roles as $roleName) {
            $role = Role::findOrCreate($roleName);
            $isSystem = true;
            $role->update(['is_system' => $isSystem]);
        }

        $adminRole = Role::findByName(RoleType::$ADMIN);
        $allPermissions = DB::table('permissions')->pluck('name')->toArray();
        $adminRole->syncPermissions($allPermissions);

        $admin->assignRole(RoleType::$ADMIN);

        Schema::enableForeignKeyConstraints();
    }
}
