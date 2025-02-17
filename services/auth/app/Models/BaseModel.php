<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;

class BaseModel extends Model
{

    protected static function boot()
    {
        parent::boot();

        // create a event to happen on creating
        static::creating(function ($table) {
            $table->created_by = Auth::check() ? Auth::id() : NULL;
            $table->created_at = Carbon::now();
        });

        // create a event to happen on updating
        static::updating(function ($table) {
            $table->updated_by = Auth::check() ? Auth::id() : NULL;
        });

        // create a event to happen on saving
        // static::saving(function ($table) {
        // $table->updated_by = Auth::check()? Auth::id():NULL;
        // });

        // create a event to happen on deleting
        static::deleting(function ($table) {
            $table->updated_by = Auth::id();
            $table->save();
        });
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
