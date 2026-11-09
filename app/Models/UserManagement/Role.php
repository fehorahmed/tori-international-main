<?php

namespace App\Models\UserManagement;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $casts = [
        'is_system' => 'boolean',
    ];
}
