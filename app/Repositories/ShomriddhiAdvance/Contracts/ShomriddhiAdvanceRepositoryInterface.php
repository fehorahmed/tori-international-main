<?php

namespace App\Repositories\ShomriddhiAdvance\Contracts;

use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use Illuminate\Database\Eloquent\Collection;

interface ShomriddhiAdvanceRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?ShomriddhiTransaction;

    public function getPending(): Collection;

    public function getApproved(): Collection;

    public function create(array $data): ShomriddhiTransaction;

    public function updateStatus(ShomriddhiTransaction $advance, string $status, array $extra = []): bool;
}
