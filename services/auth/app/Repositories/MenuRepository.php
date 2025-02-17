<?php

namespace App\Repositories;
use App\Models\PageAjax;
use App\Models\Permission;
use DB;
use App\Interfaces\MenuRepositoryInterface ;
use  App\Models\Role;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
class MenuRepository implements MenuRepositoryInterface
{
        
		
		/**
		* assign Menu To Role
		*/
		public function getRoleWiseMenu():array
		{
			try{
				$user = auth('admin')->user();

				// Assuming the user has one role, you can get the role_id like this:
				$roleId = $user->roles()->first()->id;
			
				$role = Role::findOrFail($roleId);
				if(!$role){
					return array('status'=>'failed','message'=>'Role not found');
				}
				// Get the menus associated with the role
				$menus = $role->permissions()->whereIn('permissions.type',[1,2])->where('permissions.is_active',1)->select('permissions.id','permissions.code','permissions.parent_id','permissions.description','permissions.type','permissions.icon')->orderBy('permissions.order')->get();
				
				 $menuHierarchy = $this->buildMenuHierarchy($menus);
				return array('status'=>'success','message'=>'Role Wise Menu List.','data'=>$menuHierarchy);
			}catch (\Exception $e) {
				\Log::error('Menu  Assigned error: ' . $e->getMessage());
				return array('status'=>'failed','message'=>$e->getMessage());
			}
		}
		
		
		
		/**
		* Build a hierarchical menu.
		*/
		 protected function buildMenuHierarchy($menus)
		{
			$menuById = [];
			$menuHierarchy = [];

			
			foreach ($menus as $menu) {
				$menuById[$menu->id] = $menu->toArray();
				$menuById[$menu->id]['children'] = [];
			}

			
			foreach ($menuById as $menuId => &$menu) {
				if (isset($menu['parent_id'])) {
					$menuById[$menu['parent_id']]['children'][] = &$menu;
				} else {
					$menuHierarchy[] = &$menu;
				}
			}

			return $menuHierarchy;
		} 

		/**
		 * Summary of getPageContent
		 * @param int $page_id
		 * @return array
		 */
		public function getPageContent(int $page_id) :array
		{
			try{
				$user = auth('admin')->user();

				// Assuming the user has one role, you can get the role_id like this:
				$roleId = $user->roles()->first()->id;
			
				$role = Role::findOrFail($roleId);
				if(!$role){
					return array('status'=>'failed','message'=>'Role not found');
				}
				$actions = $role->permissions()->whereIn('permissions.type',[3,5])->where('permissions.parent_id',$page_id)->where('permissions.is_active',1)->select('permissions.name','permissions.description','permissions.id','permissions.code','permissions.type')->orderBy('permissions.order')->get();
				
			    $data_arr=[];
				foreach($actions as $permission){
					$_arr[$permission->code]['description']=$permission->description;
			
					if($permission->type==5){
						$_arr[$permission->code]['id'] = $permission->id;
						$_arr[$permission->code]['type'] = config('admin.permission_names')[5];
						$data_arr=$_arr;
						continue;
					}
					
					$_arr[$permission->code]['route']= $this->getUri($permission->name);
					$_arr[$permission->code]['ajax']= 
					PageAjax::where('parent_permission_id', $permission->id)
					->get()
					->mapWithKeys(function ($ajax) {
						if (!$ajax->permission) {
							return [];
						}
						
						return [
							$ajax->permission->code => [
								"route" => $this->getUri($ajax->permission->name), // Generate the route
							],
						];
					});

					
					$data_arr=$_arr;
					
				}
				return array('status'=>'success','message'=>'Page Content.','data'=>$data_arr);
			}catch (\Exception $e) {
				\Log::error('Menu  Assigned error: ' . $e->getMessage());
				return array('status'=>'failed','message'=>$e->getMessage());
			}
		}

		public function getUri($permission_name){
			return  Route::getRoutes()->getByName($permission_name)->uri();
		}
		public function syncUri($service):array
		{
			try{
				$permissions= Permission::get();
				$service=config('admin.auth_service_name');

				foreach($permissions as $permission){

				if (Route::has($permission->name)) {
					$route = Route::getRoutes()->getByName($permission->name);
					$uri = $route->uri(); // Get the URI of the route
					$methods = $route->methods();
					
					$permission->uri=$uri;
					$permission->method=$methods[0];
					$permission->service=$service;
					$permission->save();
				}
				}
				
				$policyUrl=config('admin.policy_url').'/api/v1/syncPolicyUri';
				$response = Http::send('get', $policyUrl);

				$masterUrl=config('admin.policy_url').'/api/v1/syncMasterUri';
				$response = Http::send('get', $masterUrl);

				$filePath = storage_path('framework/route/dynamic_routes.php');
				if (file_exists($filePath)) {
					unlink($filePath);
				}
				return array('status'=>'success','message'=>'Sync Content.','data'=>$response->json());
				}catch(\Exception $e){
					\Log::error('Menu  Assigned error: ' . $e->getMessage());
					return array('status'=>'failed','message'=>$e->getMessage());
				}
		}
}
