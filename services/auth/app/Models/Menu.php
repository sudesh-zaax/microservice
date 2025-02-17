<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AuditableTrait; 
use Illuminate\Database\Eloquent\SoftDeletes;
class Menu extends Model
{
    use HasFactory,AuditableTrait,SoftDeletes;
	protected $dates = ['deleted_at'];
	protected $fillable = [
        'name', 'route','description', 'icon', 'parent_id', 'order', 'is_active'
    ];
	protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
