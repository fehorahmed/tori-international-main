<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('account_groups')->truncate();
        DB::table('account_heads')->truncate();

        $groupsFile = database_path('seeders/data/reserved_account_groups.json');
        $headsFile = database_path('seeders/data/reserved_account_heads.json');

        if (file_exists($groupsFile) && file_exists($headsFile)) {
            $reservedGroups = json_decode(file_get_contents($groupsFile), true);
            $reservedHeads = json_decode(file_get_contents($headsFile), true);
            $groupIdMap = [];

            // Step 1: Create Global Groups
            foreach ($reservedGroups as $group) {
                $oldId = $group['id'];
                $groupData = $group;
                $groupData['reserved_id'] = $group['reserved_id'] ?? null;
                unset($groupData['id']);
                $groupData['is_editable'] = 0;
                $groupData['status'] = 1;
                $groupData['account_group_id'] = null; // Temporary

                $groupData['is_leaf'] = $group['is_leaf'] ?? 1;
                $newId = DB::table('account_groups')->insertGetId($groupData);
                $groupIdMap[$oldId] = $newId;
            }

            // Step 2: Update Parent Group Relationships
            foreach ($reservedGroups as $group) {
                if (!empty($group['account_group_id'])) {
                    if (isset($groupIdMap[$group['account_group_id']])) {
                        DB::table('account_groups')
                            ->where('id', $groupIdMap[$group['id']])
                            ->update(['account_group_id' => $groupIdMap[$group['account_group_id']]]);
                    }
                }
            }

            // Step 3: Create Global Account Heads
            foreach ($reservedHeads as $head) {
                $headData = $head;
                $headData['reserved_id'] = $head['reserved_id'] ?? null;
                unset($headData['id']);
                $headData['sister_concern_id'] = null; // Global
                $headData['is_system'] = $head['is_system'] ?? 0; 
                $headData['status'] = 1;
                $headData['is_editable'] = !empty($head['payment_mode']) ? 1 : 0;
                $headData['is_control_account'] = $head['is_control_account'] ?? 0;
                $headData['nature'] = $head['nature'] ?? 'Debit';
                $headData['payment_mode'] = $head['payment_mode'] ?? 0;
                
                if (!empty($headData['account_group_id'])) {
                    $headData['account_group_id'] = $groupIdMap[$headData['account_group_id']] ?? null;
                }
                
                DB::table('account_heads')->insert($headData);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
