<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\AuditableTrait;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, AuditableTrait, SoftDeletes;

    public const TYPE_UNIONE_USER = 1;
    public const TYPE_AGENT = 2;
    public const TYPE_CUSTOMER = 3;
    public const TYPE_POSP_USER = 4;
    public const ACTIVE = 1;
    public const IN_ACTIVE = 0;

    protected $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'display_name',
        'user_name',
        'email',
        'password',
        'user_type',
        'phone',
        'is_active',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_by',
        'updated_by',
        'created_at',
        'deleted_at',
        'updated_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public static function getUserTypes(): array
    {
        return [
            self::TYPE_UNIONE_USER => 'Unione User',
            self::TYPE_AGENT => 'Agent',
            self::TYPE_CUSTOMER => 'Customer',
            self::TYPE_POSP_USER => 'Posp User'
        ];
    }

    public function findForPassport(string $username): User
    {
        return $this->where('user_name', $username)->first();
    }


    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }
}
