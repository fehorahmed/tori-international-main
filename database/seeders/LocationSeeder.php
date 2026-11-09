<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Pourashava;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Division::truncate();
        District::truncate();
        Upazila::truncate();
        Union::truncate();
        Pourashava::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Seed Divisions
        $divisionsJson = file_get_contents(database_path('seeders/data/divisions.json'));
        $divisions = json_decode($divisionsJson, true);
        foreach ($divisions as $div) {
            Division::create([
                'id' => $div['id'],
                'name' => $div['name'],
                'bn_name' => $div['bn_name'],
            ]);
        }

        // 2. Seed Districts
        $districtsJson = file_get_contents(database_path('seeders/data/districts.json'));
        $districts = json_decode($districtsJson, true);
        foreach ($districts as $dist) {
            District::create([
                'id' => $dist['id'],
                'division_id' => $dist['division_id'],
                'name' => $dist['name'],
                'bn_name' => $dist['bn_name'],
            ]);
        }

        // 3. Seed Upazilas
        $upazilasJson = file_get_contents(database_path('seeders/data/upazilas.json'));
        $upazilas = json_decode($upazilasJson, true);
        foreach ($upazilas as $up) {
            Upazila::create([
                'id' => $up['id'],
                'district_id' => $up['district_id'],
                'name' => $up['name'],
                'bn_name' => $up['bn_name'],
            ]);
        }

        // 5. Seed Unions
        $unionsJson = file_get_contents(database_path('seeders/data/unions.json'));
        $unions = json_decode($unionsJson, true);
        $unionData = [];
        foreach ($unions as $un) {
            $unionData[] = [
                'id' => $un['id'],
                'upazila_id' => $un['upazilla_id'] ?? $un['upazila_id'],
                'name' => $un['name'],
                'bn_name' => $un['bn_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in chunks for performance
        foreach (array_chunk($unionData, 500) as $chunk) {
            DB::table('unions')->insert($chunk);
        }

        // 6. Seed Villages 
        $villagesPath = database_path('seeders/data/villages.json');
        if (file_exists($villagesPath)) {
            $villagesJson = file_get_contents($villagesPath);
            $villages = json_decode($villagesJson, true);
            foreach ($villages as $village) {
                \App\Models\Village::create([
                    'id' => $village['id'],
                    'upazila_id' => $village['upazila_id'],
                    'union_id' => $village['union_id'],
                    'name' => $village['name'],
                    'bn_name' => $village['bn_name'],
                    'status' => $village['status'] ?? 1,
                ]);
            }
        }

        // 5. Seed Pourashavas
        $pourashavasJson = file_get_contents(database_path('seeders/data/pourashavas.json'));
        $pourashavas = json_decode($pourashavasJson, true);
        foreach ($pourashavas as $pour) {
            Pourashava::create([
                'id' => $pour['id'],
                'district_id' => $pour['district_id'],
                'name' => $pour['name'],
                'bn_name' => $pour['bn_name'],
            ]);
        }
    }
}
