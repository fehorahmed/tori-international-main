<?php

namespace App\Services\Reports;

/** Keeps opening/carry-forward/closing balances intact across printed pages. */
class LedgerStatementPages
{
    public function make(array $statements): array
    {
        $pages = [];
        foreach ($statements as $statement) {
            $chunks = []; $rows = []; $lines = 0;
            foreach ($statement['report']['rows'] as $row) {
                $weight = max(1, array_sum(array_map(fn ($line) => max(1, (int) ceil(mb_strlen($line) / 65)), preg_split('/\R/u', $row['narration']))), (int) ceil(mb_strlen($row['reference']) / 20), (int) ceil(mb_strlen($row['account'] ?? '') / 32));
                if ($rows && $lines + $weight > 18) { $chunks[] = $rows; $rows = []; $lines = 0; }
                $rows[] = $row; $lines += $weight;
            }
            if ($rows || ! $chunks) $chunks[] = $rows;
            $opening = $statement['report']['opening'];
            foreach ($chunks as $index => $chunk) {
                $closing = $chunk ? $chunk[array_key_last($chunk)]['balance'] : $opening;
                $pages[] = $statement + ['rows' => $chunk, 'opening' => $opening, 'closing' => $closing, 'continued' => $index > 0, 'last' => $index === count($chunks) - 1];
                $opening = $closing;
            }
        }
        return $pages;
    }
}
