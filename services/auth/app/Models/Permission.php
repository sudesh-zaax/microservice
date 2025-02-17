<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Traits\AuditableTrait;

class Permission extends SpatiePermission
{
    use AuditableTrait;

    public function pageAjax()
    {
        return $this->hasMany(PageAjax::class, 'parent_permission_id');
    }
}
