<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_details';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_detail_id';


    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'otp',
        'otp_expires_at',
        'is_email_verified',
        'email_verified_at',
        'is_phone_verified',
        'phone_verified_at',
        'created_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'otp_expires_at' =>'datetime',
        'is_email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'is_phone_verified' => 'boolean',
        'phone_verified_at' => 'datetime'
    ];

    /**
     * Get the user that owns the user details.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
