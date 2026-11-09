<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountGroup extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = [];
    public function children()
    {
        return $this->hasMany(AccountGroup::class, 'account_group_id', 'id');
    }
    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class,'account_group_id','id');
    }
    public function accountGroups()
    {
        return $this->hasMany(AccountGroup::class,'account_group_id','id');
    }

    public function getAllChildIds()
    {
        $childIds = [];
        $this->getRecursiveChildIds($this, $childIds);
        return $childIds;
    }

    protected function getRecursiveChildIds($type, &$childIds)
    {

        foreach ($type->children as $child) {
            $childIds[] = $child->id;
            $this->getRecursiveChildIds($child, $childIds);
        }
    }
    public function topParent()
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id','id');
    }
    public function getTopReservedIdAttribute()
    {
        $current = $this;

        while ($current && $current->account_group_id != null) {
            $current = $current->topParent;
        }

        return $current->reserved_id;
    }
    public function getTopParentIdAttribute()
    {
        $current = $this;

        while ($current && $current->account_group_id != null) {
            $current = $current->topParent;
        }

        return $current->id;
    }
}
