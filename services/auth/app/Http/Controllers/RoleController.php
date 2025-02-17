<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateRoleRequest;
use App\Interfaces\RoleRepositoryInterface;
use App\Http\Requests\CheckRoleRequest;
use App\Http\Requests\AssignRoleToUserRequest;
use Illuminate\Http\Request;
use Log;
class RoleController extends Controller
{
	
	protected $roleRepository;
	
	/**
	 * Summary of __construct
	 * @param \App\Interfaces\RoleRepositoryInterface $roleRepository
	 */
	public function __construct(RoleRepositoryInterface $roleRepository)
	{
		$this->roleRepository = $roleRepository;
	}
    
	/**
     * Display a listing of the resource.
     */
	public function index(Request $request,$page,$limit)
	{
		try{
			$dataArr= $this->roleRepository->getRoleList($request,(int)$page,(int)$limit);
			if($dataArr['status']=='success'){
				return $this->returnResponse($dataArr['data'],$dataArr['message'],200);
			}else{
				 return $this->returnExceptionResponse($dataArr['message'],404);
			}
		}catch (\Exception $e) {
			 Log::error('role listing error: ' . $e->getMessage());
			 return $this->returnExceptionResponse($e->getMessage(),500);
		}
	}
	
	/**
	 * Summary of createRole
	 * @param \App\Http\Requests\CheckRoleRequest $request
	 * @return JsonResponse|mixed
	 */
    public function createRole(CheckRoleRequest $request)
    {
		try{
			$dataArr= $this->roleRepository->createRole($request);
			if($dataArr['status']=='success'){
				return $this->returnResponse($dataArr['data'],$dataArr['message'],201);
			}else{
				 return $this->returnExceptionResponse($dataArr['message'],404);
			}
		}catch (\Exception $e) {
			 Log::error('Role create error: ' . $e->getMessage());
			 return $this->returnExceptionResponse($e->getMessage(),500);
		}
    }
	/**
	 * Summary of createRole
	 * @param \App\Http\Requests\UpdateRoleRequest $request
	 * @return JsonResponse|mixed
	 */
    public function editRole(UpdateRoleRequest $request)
    {
		try{
			$dataArr= $this->roleRepository->editRole($request);
			if($dataArr['status']=='success'){
				return $this->returnResponse($dataArr['data'],$dataArr['message'],201);
			}else{
				 return $this->returnExceptionResponse($dataArr['message'],404);
			}
		}catch (\Exception $e) {
			 Log::error('Role create error: ' . $e->getMessage());
			 return $this->returnExceptionResponse($e->getMessage(),500);
		}
    }
	/**
	 * Summary of assign Role To User
	 * @param \App\Http\Requests\AssignRoleToUserRequest $request
	 * @return JsonResponse|mixed
	 */
    public function assignRoleToUser(AssignRoleToUserRequest $request)
    {
		try{
			$dataArr= $this->roleRepository->assignRoleToUser($request);
			if($dataArr['status']=='success'){
				return $this->returnResponse($dataArr['data'],$dataArr['message'],200);
			}else{
				 return $this->returnExceptionResponse($dataArr['message'],404);
			}
		}catch (\Exception $e) {
			 Log::error('Role create error: ' . $e->getMessage());
			 return $this->returnExceptionResponse($e->getMessage(),500);
		}
       
    }
}
