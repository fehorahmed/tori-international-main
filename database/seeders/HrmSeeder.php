<?php

namespace Database\Seeders;

use App\Enumeration\ReservedDesignation;
use App\Models\Hrm\EmployeeStatus;
use App\Models\Hrm\Shift;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HrmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Default Shift
        Shift::updateOrCreate(
            ['name' => '9 to 5'],
            [
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'create_user_id' => 1
            ]
        );

        // Create Default Employee Status
        EmployeeStatus::updateOrCreate(
            ['name' => 'Permanent'],
            [
                'sort' => 1,
                'is_salary_apply' => 1
            ]
        );

        // Create Default Department & Designation for Sales
        $dept = Department::updateOrCreate(
            ['name' => 'Sales & Marketing'],
            ['create_user_id' => 1]
        );

        // Create Reserved Designations
        $designations = [
            ['name' => 'Area Manager', 'reserved_id' => ReservedDesignation::$AREA_MANAGER],
            ['name' => 'Branch Manager', 'reserved_id' => ReservedDesignation::$BRANCH_MANAGER],
            ['name' => 'Sales Representative (SR)', 'reserved_id' => ReservedDesignation::$SR],
            ['name' => 'Field Officer', 'reserved_id' => ReservedDesignation::$FIELD_OFFICER],
            ['name' => 'Technician', 'reserved_id' => ReservedDesignation::$TECHNICIAN],
            ['name' => 'Trainee', 'reserved_id' => ReservedDesignation::$TRAINEE],
        ];

        foreach ($designations as $desig) {
            Designation::updateOrCreate(
                ['reserved_id' => $desig['reserved_id']],
                [
                    'name' => $desig['name'],
                    'department_id' => $dept->id,
                    'create_user_id' => 1
                ]
            );
        }

        // Insert Default Salary Structure
        DB::table('salary_structures')->insertOrIgnore([
            'id' => 1,
            'name' => 'Management Structure',
            'basic_percent' => 60,
            'house_rent_percent' => 30,
            'medical_percent' => 10,
            'ta_da_fixed' => 0,
            'pf_percent' => 0,
            'tax_percent' => 0,
            'status' => 1,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
