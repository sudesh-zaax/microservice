<?php

namespace App\Http\Middleware;

use App\Models\PageAjax;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;

class CheckUserAuthorization
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $guardName = $this->getGuardName();
            $user = Auth::guard($guardName)->user();
            
            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $routeName = $request->route()?->getName();
            $module = $request->route()?->parameter('module') ?? '';

            if ($this->hasRolePermission($user, $routeName, $module)) {
                return $next($request);
            }

            return $this->unauthorizedResponse('You do not have permission to access this resource');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('An error occurred while checking authorization');
        }
    }

    /**
     * Check if user has the required role permission
     *
     * @param  mixed  $user
     * @param  string  $routeName
     * @param  string  $module
     * @return bool
     */
    protected function hasRolePermission($user, $routeName, $module)
    {
        if (!$routeName) {
            return false;
        }

        $guardName = $this->getGuardName();
        $permission = $this->getPermissionByName($routeName, $guardName);

        if (!$permission) {
            return false;
        }

        if (!empty($module) && !$this->validateModuleAccess($module, $permission, $guardName)) {
            return false;
        }

        return $this->checkUserRolePermission($user, $permission);
    }

    /**
     * Validate module access
     *
     * @param  string  $module
     * @param  Permission  $permission
     * @param  string  $guardName
     * @return bool
     */
    protected function validateModuleAccess($module, $permission, $guardName)
    {
        if (!$this->isValidModuleName($module)) {
            return false;
        }

        $module = str_replace('-', '_', strtoupper($module));
        $parentPermission = $this->getPermissionByCode($module, $guardName);

        if (!$parentPermission) {
            return false;
        }

        return $this->checkPageAjax($parentPermission->id, $permission->id);
    }

    /**
     * Validate module name format
     *
     * @param  string  $module
     * @return bool
     */
    protected function isValidModuleName($module)
    {
        $pattern = '/^[a-z-]+$/';
        return preg_match($pattern, $module);
    }

    /**
     * Check if user's role has the required permission
     *
     * @param  mixed  $user
     * @param  Permission  $permission
     * @return bool
     */
    protected function checkUserRolePermission($user, $permission)
    {
        $cacheKey = "user_permission_{$user->id}_{$permission->id}";
        
        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($user, $permission) {
            foreach ($user->roles as $role) {
                if ($role->hasPermissionTo($permission)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Check page ajax relationship
     *
     * @param  int  $parent_id
     * @param  int  $permission_id
     * @return bool
     */
    protected function checkPageAjax($parent_id, $permission_id)
    {
        $cacheKey = "page_ajax_{$parent_id}_{$permission_id}";
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($parent_id, $permission_id) {
            return PageAjax::where('parent_permission_id', $parent_id)
                          ->where('permission_id', $permission_id)
                          ->exists();
        });
    }

    /**
     * Get permission by name
     *
     * @param  string  $routeName
     * @param  string  $guardName
     * @return Permission|null
     */
    protected function getPermissionByName($routeName, $guardName)
    {
        $cacheKey = "permission_name_{$routeName}_{$guardName}";
        
        return Cache::remember($cacheKey, now()->addDay(), function () use ($routeName, $guardName) {
            return Permission::where('name', $routeName)
                           ->where('guard_name', $guardName)
                           ->first();
        });
    }

    /**
     * Get permission by code
     *
     * @param  string  $routeCode
     * @param  string  $guardName
     * @return Permission|null
     */
    protected function getPermissionByCode($routeCode, $guardName)
    {
        $cacheKey = "permission_code_{$routeCode}_{$guardName}";
        
        return Cache::remember($cacheKey, now()->addDay(), function () use ($routeCode, $guardName) {
            return Permission::where('code', $routeCode)
                           ->where('guard_name', $guardName)
                           ->first();
        });
    }

    /**
     * Get guard name
     *
     * @return string
     */
    protected function getGuardName()
    {
        return auth()->getDefaultDriver();
    }

    /**
     * Return unauthorized response
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedResponse($message = 'Unauthorized')
    {
        return response()->json([
            'message' => $message,
            'status' => 'error',
            'code' => 403
        ], 403);
    }
}
