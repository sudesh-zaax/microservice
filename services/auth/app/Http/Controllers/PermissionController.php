<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckPermissionRequest;
use App\Http\Requests\AssignPermissionToRoleRequest;
use App\Interfaces\PermissionRepositoryInterface;
use Log;

class PermissionController extends Controller
{
	protected $permissionRepository;
	
	/**
	 * Summary of __construct
	 * @param \App\Interfaces\PermissionRepositoryInterface $permissionRepository
	 */
	public function __construct(PermissionRepositoryInterface $permissionRepository)
	{
		$this->permissionRepository = $permissionRepository;
	}
	
	/**
	 * get permission list
	 * @return JsonResponse|mixed
	 */
	public function index(Request $request,$page,$limit)
	{
		try{
			$dataArr= $this->permissionRepository->getPermissionList($request,$page,$limit);
			if($dataArr['status']=='success'){
				return $this->returnResponse($dataArr['data'],$dataArr['message'],200);
			}else{
				 return $this->returnExceptionResponse($dataArr['message'],404);
			}
		}catch (\Exception $e) {
			 Log::error('permission listing error: ' . $e->getMessage());
			 return $this->returnExceptionResponse($e->getMessage(),500);
		}
	}
	
	/**
	 * Summary of Create Permission
	 * @param \App\Http\Requests\CheckPermissionRequest $request
	 * @return JsonResponse|mixed
	 */
    public function createPermission(CheckPermissionRequest $request)
    {
		try{
			$dataArr= $this->permissionRepository->createPermission($request);
			if($dataArr['status']=='success'){
				return $this->returnResponse($dataArr['data'],$dataArr['message'],201);
			}else{
				 return $this->returnExceptionResponse($dataArr['message'],404);
			}
		}catch (\Exception $e) {
			 Log::error('permission create error: ' . $e->getMessage());
			 return $this->returnExceptionResponse($e->getMessage(),500);
		}
    }
	
	
	/**
	 * Summary of Assign Permission To Role
	 * @param \App\Http\Requests\AssignPermissionToRoleRequest $request
	 * @return JsonResponse|mixed
	 */
    public function assignPermissionToRole(AssignPermissionToRoleRequest $request)
    { 
       try{
			$dataArr= $this->permissionRepository->assignPermissionToRole($request);
			if($dataArr['status']=='success'){
				return $this->returnResponse($dataArr['data'],$dataArr['message'],200);
			}else{
				 return $this->returnExceptionResponse($dataArr['message'],404);
			}
		}catch (\Exception $e) {
			 Log::error('permission Assign error: ' . $e->getMessage());
			 return $this->returnExceptionResponse($e->getMessage(),500);
		}
    }
	
	/**
	 * Summary of getAllWithAssignedPermissionList
	 * @param mixed $roleId
	 * @return JsonResponse|mixed
	 */
	public function getAllWithAssignedPermissionList($roleId):JsonResponse
    { 
       try{
			$dataArr= $this->permissionRepository->getAllWithAssignedPermissionList($roleId);
			if($dataArr['status']=='success'){
				return $this->returnResponse($dataArr['data'],$dataArr['message'],200);
			}else{
				 return $this->returnExceptionResponse($dataArr['message'],404);
			}
		}catch (\Exception $e) {
			 Log::error('permission create error: ' . $e->getMessage());
			 return $this->returnExceptionResponse($e->getMessage(),500);
		}
    }
    public function revokePermissionToRole(AssignPermissionToRoleRequest $request)
    { 
       try{
			$dataArr= $this->permissionRepository->revokePermissionToRole($request);
			if($dataArr['status']=='success'){
				return $this->returnResponse($dataArr['data'],$dataArr['message'],200);
			}else{
				 return $this->returnExceptionResponse($dataArr['message'],404);
			}
		}catch (\Exception $e) {
			 Log::error('permission revoke error: ' . $e->getMessage());
			 return $this->returnExceptionResponse($e->getMessage(),500);
		}
    }
	
}
