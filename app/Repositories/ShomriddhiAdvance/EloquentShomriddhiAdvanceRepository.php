<?php

namespace App\Repositories\ShomriddhiAdvance;

use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Repositories\ShomriddhiAdvance\Contracts\ShomriddhiAdvanceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentShomriddhiAdvanceRepository implements ShomriddhiAdvanceRepositoryInterface
{
    public function all(): Collection
    {
        return ShomriddhiTransaction::with(['customer', 'creator'])->orderBy('id', 'desc')->get();
    }

    public function findById(int $id): ?ShomriddhiTransaction
    {
        return ShomriddhiTransaction::with(['customer', 'creator'])->find($id);
    }

    public function getPending(): Collection
    {
        return ShomriddhiTransaction::with(['customer', 'creator'])
            ->where('status', 'Pending')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getApproved(): Collection
    {
        return ShomriddhiTransaction::with(['customer', 'creator'])
            ->where('status', 'Approved')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function create(array $data): ShomriddhiTransaction
    {
        return ShomriddhiTransaction::create($data);
    }

    public function updateStatus(ShomriddhiTransaction $advance, string $status, array $extra = []): bool
    {
        return $advance->update(array_merge(['status' => $status], $extra));
    }
}
