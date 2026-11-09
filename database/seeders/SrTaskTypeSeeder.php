<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SrTaskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Sales Target', 'slug' => 'sales_target', 'unit' => 'Amount'],
            ['name' => 'Visit Count', 'slug' => 'visit_count', 'unit' => 'Count'],
            ['name' => 'Due Payment Collection', 'slug' => 'payment_collection', 'unit' => 'Amount'],
            ['name' => 'Sales Order Count', 'slug' => 'order_count', 'unit' => 'Count'],
            ['name' => 'YouTube Subscriber', 'slug' => 'youtube_subscriber', 'unit' => 'Count'],
            ['name' => 'Facebook Follower', 'slug' => 'facebook_follower', 'unit' => 'Count'],
        ];


        foreach ($types as $type) {
            DB::table('task_types')->updateOrInsert(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
