<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Interfaces\MenuRepositoryInterface;
use Log;
class MenuController extends Controller
{
	protected $menuRepository;
	/**
	 * Summary of __construct
	 * @param \App\Interfaces\MenuRepositoryInterface $menuRepository
	 */
	public function __construct(MenuRepositoryInterface $menuRepository)
	{
		$this->menuRepository = $menuRepository;
	}

	/**
	 * Get the menu list assigned to a specific role.
	 */
	public function getRoleWiseMenu(): JsonResponse
	{
		try {
			$dataArr = $this->menuRepository->getRoleWiseMenu();
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['data'], $dataArr['message'], 200);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 404);
			}
		} catch (\Exception $e) {
			Log::error('get Role Wise Menu: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}
	/**
	 * Summary of getPageContent
	 * @param int $permission_id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getPageContent(int $permission_id): JsonResponse
	{
		try {
			$dataArr = $this->menuRepository->getPageContent($permission_id);
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['data'], $dataArr['message'], 200);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 404);
			}
		} catch (\Exception $e) {
			Log::error('get page action: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}

	public function syncUri($service = ''): JsonResponse
	{
		try {
			$dataArr = $this->menuRepository->syncUri($service);
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['data'], $dataArr['message'], 200);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 404);
			}
		} catch (\Exception $e) {
			Log::error('get page action: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}

}
