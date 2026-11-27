<?php

namespace App\Support;

use App\Models\BranchManagement\SisterConcern;
use Illuminate\Validation\ValidationException;

/** Read-only report scope; never changes the user's active workspace branch. */
class ReportBranch
{
    public const PERMISSION = 'all_branch_permission';

    public static function allowed(): bool
    {
        return auth()->check() && auth()->user()->can(self::PERMISSION);
    }

    public static function id(?\Illuminate\Http\Request $request = null): ?int
    {
        $active = auth()->user()?->activeConcernId();
        if (! self::allowed()) {
            abort_unless($active, 422, 'Select an active branch before viewing reports.');
            return (int) $active;
        }
        $value = ($request ?? request())->input('report_branch_id');
        if ($value === 'all') return null;
        if ($value === null || $value === '') {
            return $active ? (int) $active : null;
        }
        if (! is_scalar($value) || ! ctype_digit((string) $value) || ! SisterConcern::whereKey($value)->exists()) {
            throw ValidationException::withMessages(['report_branch_id' => 'Select a valid branch or All Branches.']);
        }
        return (int) $value;
    }

    public static function label(): string
    {
        $id = self::id();
        if ($id === null) return 'All Branches';
        $branch = SisterConcern::findOrFail($id);
        return $branch->code.' — '.$branch->name;
    }
}
