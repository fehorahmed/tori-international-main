<?php

namespace App\Console\Commands\ShomriddhiFdr;

use App\Models\ShomriddhiFdr\FdrAccount;
use App\Services\ShomriddhiFdr\FdrAccountService;
use Illuminate\Console\Command;

class ProcessFdrInterestPayout extends Command
{
    protected $signature = 'shomriddhi-fdr:process-interest';

    protected $description = 'Recover FDR interest and maturity using the current Bangladesh date';

    public function handle(FdrAccountService $service): int
    {
        $failed = 0;
        FdrAccount::where('status', 'Active')->orderBy('id')->chunkById(100, function ($rows) use ($service, &$failed) {
            foreach ($rows as $account) {
                try {
                    $service->process($account->id);
                } catch (\Throwable $e) {
                    $failed++;
                    $this->error('FDR '.$account->fdr_no.': '.$e->getMessage());
                    report($e);
                }
            }
        });
        $this->info('FDR processing completed. Failed: '.$failed);

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
