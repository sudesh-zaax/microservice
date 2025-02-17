<?php

namespace App\Interfaces;
use App\Http\Requests\CheckPermissionRequest;
use Illuminate\Http\Request;
use App\Http\Requests\AssignPermissionToRoleRequest;
interface PermissionRepositoryInterface
{
   /**
    * Summary of getPermissionList
    * @return void
    */
   public function getPermissionList(Request $request,$page,$limit):array;
   /**
    * Summary of createPermission
    * @param CheckPermissionRequest $data
    * @return array
    */
   public function createPermission(CheckPermissionRequest $data);
   /**
    * Summary of assignPermissionToRole
    * @param AssignPermissionToRoleRequest $data
    * @return array
    */
   public function assignPermissionToRole(AssignPermissionToRoleRequest $data);
   /**
    * Summary of getAllWithAssignedPermissionList
    * @param int $role_id
    * @return void
    */
   public function getAllWithAssignedPermissionList(string $roleId):array;

   /**
    * Summary of revokePermissionToRole
    * @param array $data
    * @return void
    */
   public function revokePermissionToRole(AssignPermissionToRoleRequest $data):array;
   
}
