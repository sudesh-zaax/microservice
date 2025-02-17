<?php

namespace App\Http\Middleware;

use App\Models\PageAjax;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class CheckUserAuthorization
{
     public function handle($request, Closure $next)
    {
		 $guardName = $this->getGuardName();
        // Get the authenticated user from the 'api' guard
        $user = Auth::guard($guardName)->user();
        $routeName = $request->route()?->getName();
        $module = $request->route()?->parameter('module')??'';
        // Check if the user has the required role and permission for the route name
       
       
        if ($this->hasRolePermission($user, $routeName,$module)) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    protected function hasRolePermission($user, $routeName,$module)
    {
        // Ensure the route name exists
        if (!$routeName) {
            return false;
        }
		$guardName = $this->getGuardName();
	
        // Get the permission with the matching name
        $permission=$this->getPermissionByName($routeName,$guardName);
        if (!$permission) {
            return false;
        }
       
        if(!empty($module))
        {
            
            $pattern = '/^[a-z-]+$/';
            if (!preg_match($pattern, $module)) {
                return false;
            }
            $module=str_replace('-','_',strtoupper($module));
            
            $guardName = $this->getGuardName();
            $parentPermission=$this->getPermissionByCode($module,$guardName);
         
            if (!$parentPermission) {
                return false;
            }
           $pageAjax= $this->checkPageAjax($parentPermission->id,$permission->id);
           
           if(!$pageAjax){
                return false;
           }
        }
        
        // Check if the user's role has the required permission
        foreach ($user->roles as $role) {
            
            if ($role->hasPermissionTo($permission)) {
                 return true;
            }
        }
       
        return false;
    }
	public function checkPageAjax($parent_id,$permssion_id){
             return PageAjax::where('parent_permission_id', $parent_id)->where('permission_id',$permssion_id)->first();
    }

    public function getPermissionByName($routeName,$guardName){

        return Permission::where('name', $routeName)->where('guard_name',$guardName)->first();
    }

    public function getPermissionByCode($routeCode,$guardName){

        return Permission::where('code', $routeCode)->where('guard_name',$guardName)->first();
    }
	public function getGuardName(){
		 return auth()->getDefaultDriver();
	}
}
