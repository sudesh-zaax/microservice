<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait AuditableTrait
{
    public static function bootAuditableTrait()
    {
        static::creating(function (Model $model) {
            $model->logAudit('created', $model);
        });

        static::updating(function (Model $model) {
            $model->logAudit('updated', $model);
        });

        static::deleting(function (Model $model) {
            $model->logAudit('deleted', $model);
        });
    }

    public function logAudit($event, Model $model)
    {  
	     $user_type='register';
		 $user_id=0;
		if(Auth::check()){
			$user_type=get_class(Auth::user());
			$user_id=Auth::id();
		}
        $auditLog = new \App\Models\AuditLog();
        $auditLog->user_type = $user_type;
        $auditLog->user_id = $user_id;
        $auditLog->event = $event;
        $auditLog->auditable_type = get_class($model);
        $auditLog->auditable_id = $model->id ?? 0;
        $auditLog->old_values = json_encode($model->getOriginal());
        $auditLog->new_values = json_encode($model->getAttributes());
        $auditLog->save();
    }
}
?>