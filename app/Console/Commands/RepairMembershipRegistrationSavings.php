<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use App\Services\Membership\RegistrationSavingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairMembershipRegistrationSavings extends Command
{
    protected $signature = 'membership:repair-registration-savings {--apply : Create missing empty savings accounts and ledgers}';
    protected $description = 'Audit or repair savings provisioning for completed app registrations only';

    public function handle(): int
    {
        $ids = DB::table('membership_registration_requests')->whereNotNull('customer_id')->distinct()->pluck('customer_id');
        $count = 0; $skipped = 0;
        foreach (Supplier::whereIn('id', $ids)->where('type', 'Customer')->where('status', 1)->get() as $member) {
            $hasAccount = DB::table('shomriddhi_accounts')->where('customer_id', $member->id)->exists();
            $hasHead = DB::table('account_heads')->where('supplier_id', $member->id)->where('head_type', 'savings')->exists();
            if ($hasAccount && $hasHead) continue;
            if (! $this->option('apply')) { $this->line('Missing savings provision: customer #'.$member->id); $count++; continue; }
            try {
                RegistrationSavingsService::ensure($member);
                $count++; $this->info('Repaired customer #'.$member->id);
            } catch (\Exception $e) {
                $skipped++; $this->warn('Skipped customer #'.$member->id.'; branch review required ('.class_basename($e).').');
            }
        }
        $this->info(($this->option('apply') ? 'Repaired: ' : 'Needs repair: ').$count.'; skipped: '.$skipped);
        return $skipped ? self::FAILURE : self::SUCCESS;
    }
}
