<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function backupPath(): string
    {
        return storage_path('app/private/migration-backups/obsolete_transaction_balance_triggers.json');
    }

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || Schema::hasColumn('account_heads', 'current_balance')) {
            return;
        }
        $names = ['after_transaction_insert', 'after_transaction_update', 'after_transaction_delete'];
        $found = collect(DB::select('SHOW TRIGGERS'))->filter(fn ($trigger) => in_array($trigger->Trigger, $names, true) && $trigger->Table === 'transactions'
            && str_contains($trigger->Statement, 'current_balance'));
        if ($found->isEmpty()) {
            return;
        }
        // Preserve exact original bodies before removing triggers referencing a nonexistent column.
        File::ensureDirectoryExists(dirname($this->backupPath()));
        if (! File::exists($this->backupPath())) {
            File::put($this->backupPath(), json_encode($found->values()->all(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        }
        foreach ($found as $trigger) {
            DB::unprepared('DROP TRIGGER `'.$trigger->Trigger.'`');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! File::exists($this->backupPath())) {
            return;
        }
        foreach (json_decode(File::get($this->backupPath()), true, flags: JSON_THROW_ON_ERROR) as $trigger) {
            $exists = collect(DB::select('SHOW TRIGGERS'))->contains(fn ($row) => $row->Trigger === $trigger['Trigger']);
            if (! $exists) {
                DB::unprepared('CREATE TRIGGER `'.$trigger['Trigger'].'` '.$trigger['Timing'].' '.$trigger['Event'].' ON `transactions` FOR EACH ROW '.$trigger['Statement']);
            }
        }
    }
};
