<?php

namespace App\Repositories;
use App\Models\PageAjax;
use App\Interfaces\PermissionRepositoryInterface;

use App\Models\Permission;
use App\Models\Role;
use Log;
use Illuminate\Support\Str;

class PermissionRepository implements PermissionRepositoryInterface
{
	/**
	 * Summary of getPermissionList
	 * @param mixed $request
	 * @param mixed $page
	 * @param mixed $limit
	 * @return array
	 */
	public function getPermissionList($request,$page,$limit):array
    {
       try{
		    $globalFilter=$request->globalFilter ?? '';
			$filters=$request->filters ?? '';
			$sorting=$request->sorting ?? '';
			
			 $permissionQry = Permission::whereGuardName('admin');

			 $totalFilterCount=0;
			 if (!empty($globalFilter)) {
				 $permissionQry->where(function($query) use ($globalFilter) {
					 $query->where('name', 'LIKE', '%' . $globalFilter . '%');
				 });
				 $totalFilterCount= $permissionQry->count();
			 }
		     
			 if (!empty($filters)) {
				 $filtersArray = json_decode($filters, true); 
				 foreach ($filtersArray as $filter) {
					 if (isset($filter['id']) && isset($filter['value'])) {
						 $permissionQry->where($filter['id'], 'LIKE', '%' . $filter['value'] . '%');
					 }
				 }
				 $totalFilterCount= $permissionQry->count();
			 }
		 
			
			 if (!empty($sorting)) {
				 $sortingArray = json_decode($sorting, true); 
				 foreach ($sortingArray as $sort) {
					 if (isset($sort['id']) && isset($sort['desc'])) {
						 $direction = $sort['desc'] ? 'desc' : 'asc';
						 $permissionQry->orderBy($sort['id'], $direction);
					 }
				 }
			 } else {
				 
				 $permissionQry->orderBy('name', 'asc');
			 }
		 
			
			 $count = $permissionQry->count();
		 
			
			 $permissionData = $permissionQry->skip(($page - 1) * $limit)
									 ->take($limit)
									 ->get();
		 
			 
			 $permission_data = [];
			 foreach ($permissionData as $data) {
				 $pdata = [
					 'id' => encode_id($data->id),
					 'name' => $data->name,
				 ];
				 $permission_data[] = $pdata;
			 }
		 
			return ['status' => 'success',
					'message' => 'Permission List.',
				'data' => [
					'data' => $permission_data,
					'total_count' => $count,
					'page' => $page,
					'total_filter_count' =>$totalFilterCount
				]
			];

		}catch (\Exception $e) {
			  Log::error('Get Permission List Error: ' . $e->getMessage());
			  return array('status'=>'failed','message'=>$e->getMessage());
		}
    }

	public function createPermission($request)
    {
       try{
		     $guard=auth()->getDefaultDriver()??'';
			 $permission = Permission::create(['name' => $request->name,'code'=>strtoupper(Str::snake($request->name)),'guard_name'=>$guard,'description'=>$request->description,'icon'=>$request->icon,'parent_id'=>$request->parent_id,'type'=>$request->type,'order'=>$request->order]);
			 return array('status'=>'success','message'=>'Permission created successfully.','data'=>$permission);
		}catch (\Exception $e) {
			  Log::error('create Permission Error: ' . $e->getMessage());
			  return array('status'=>'failed','message'=>$e->getMessage());
		}  
    }
	
	/**
	 * Summary of assignPermissionToRole
	 * @param mixed $request
	 * @return array
	 */
	public function assignPermissionToRole($request):array
    {
       try{
			$role = Role::find(decode_id($request->role_id)); 
			$permissions = Permission::whereIn('id',$request->permission_name)->get(); 
			$role->syncPermissions($permissions);
			foreach($permissions as $permission)
			{
				$this->assignPagePermissionTORole($role,$permission);
			}
			return array('status'=>'success','message'=>'Permission assigned to role successfully.','data'=>[]);
		}catch (\Exception $e) {
			  Log::error('Assign Permission Error: ' . $e->getMessage());
			  return array('status'=>'failed','message'=>$e->getMessage());
		}
    }
	/**
	 * Summary of assignPagePermissionTORole
	 * @param mixed $role
	 * @param mixed $permission
	 * @return void
	 */
	public function assignPagePermissionTORole($role,$permission){
			$dataPageAjax=PageAjax::where('parent_permission_id',$permission->id)->get();
		
			foreach($dataPageAjax as $data){
				$permission = Permission::find($data->permission_id);
				$role->givePermissionTo($permission);
			}
	}
	/**
	 * Summary of revokePermissionToRole
	 * @param mixed $request
	 * @return array
	 */
	public function revokePermissionToRole($request):array
    {
       try{
			$role = Role::find(decode_id($request->role_id)); 
			$permissions = Permission::whereIn('id',$request->permission_name)->get(); 
			foreach($permissions as $permission)
			{
				$role->revokePermissionTo($permission);
				$this->revokePagePermissionTORole($role,$permission);
			}
			return array('status'=>'success','message'=>'Permission revoke from role successfully.','data'=>[]);
		}catch (\Exception $e) {
			  Log::error('Revoke Permission Error: ' . $e->getMessage());
			  return array('status'=>'failed','message'=>$e->getMessage());
		}
    }
	/**
	 * Summary of revokePagePermissionTORole
	 * @param mixed $role
	 * @param mixed $permission
	 * @return void
	 */
	public function revokePagePermissionTORole($role,$permission){
		$dataPageAjax=PageAjax::where('parent_permission_id',$permission->id)->get();
		foreach($dataPageAjax as $data){
			$page_permission = Permission::find($data->permission_id);
			$checkOtherParentPermission=PageAjax::where('permission_id',$data->permission_id)->where('parent_permission_id','!=',$permission->id)->pluck('parent_permission_id');
			$flag_for_check=[];
			foreach($checkOtherParentPermission as $parent_permission){
				$parent_permission_value = Permission::find($parent_permission);
				$isAssignedToPermission = $role->permissions->contains($parent_permission_value) ?? false;
				$flag_for_check[]=$isAssignedToPermission;
			}
			if(!in_array(true,$flag_for_check)){
				$role->revokePermissionTo($page_permission);
			}
		}
	}

	public function getGuradName(){
		return auth()->getDefaultDriver();
	}
	/**
	 * Summary of getAllWithAssignedPermissionList
	 * @param mixed $roleId
	 * @return array
	 */
    public function getAllWithAssignedPermissionList($roleId):array
	{
		try {
			$roleId=decode_id($roleId);
			$role = Role::find($roleId);
			if (!$role) {
				return array('status'=>'failed','message'=>'role not found');
			}

			$permissions = Permission::whereIn('type', [1,2,3,4,5])->where('is_active',1)->select('id','name','description','parent_id','type')->orderBy('order','asc')->get();
			
			$assignedPermissions = $role->permissions;
            
			$permissionData=$this->buildMenuHierarchy($permissions,$assignedPermissions);
			return array('status'=>'success','message'=>'Permission List.','data'=>$permissionData);

		} catch (\Exception $e) {
			Log::error('Permission List Error: ' . $e->getMessage());
			return array('status'=>'failed','message'=>$e->getMessage());
		}
	}

	protected function buildMenuHierarchy($permissionData,$assignedPermissions)
	{
		$menuById = [];
		$menuHierarchy = [];
		foreach ($permissionData as $menu) {
			$menuById[$menu->id] = $menu->toArray();
			$menuById[$menu->id]['assigned'] = $assignedPermissions->contains($menu);
			$menuById[$menu->id]['children'] = [];
		}
		foreach ($menuById as &$menu) {
			if (isset($menu['parent_id'])) {
				$menuById[$menu['parent_id']]['children'][] = &$menu;
			} else {
				$menuHierarchy[] = &$menu;
			}
		}

		return $menuHierarchy;
	}

}
