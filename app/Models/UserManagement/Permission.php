<?php

namespace App\Models\UserManagement;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Permission::class, 'parent_id', 'id');
    }
}
