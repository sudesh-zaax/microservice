<?php

namespace App\Repositories;
use App\Http\Requests\CheckRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Interfaces\RoleRepositoryInterface;
use App\Models\Role;
use App\Models\User;
use Log;


class RoleRepository implements RoleRepositoryInterface
{

	/**
	 * Summary of getRoleList
	 * @param mixed $request
	 * @param mixed $page
	 * @param mixed $limit
	 * @return array
	 */
	public function getRoleList($request, $page, $limit): array
	{
		try {
			$filtersArray = json_decode($request->columnFilters, true) ?? [];
			$sortingArray = json_decode($request->sorting, true) ?? '';

			$roleDataQry = Role::where('guard_name', 'admin')->where('is_active', 1);

			$totalFilterCount = 0;

			if (!empty($filtersArray)) {
				foreach ($filtersArray as $filter) {
					if (isset($filter['id']) && isset($filter['value'])) {
						$roleDataQry->where($filter['id'], 'LIKE', '%' . $filter['value'] . '%');
					}
				}
				$totalFilterCount = $roleDataQry->count();
			}

			$totalPage = ceil($totalFilterCount / $limit);

			if (!empty($sortingArray)) {
				foreach ($sortingArray as $sort) {
					if (isset($sort['id']) && isset($sort['desc'])) {
						$direction = $sort['desc'] ? 'desc' : 'asc';
						$roleDataQry->orderBy($sort['id'], $direction);
					}
				}
			} else {

				$roleDataQry->orderBy('id', 'asc');
			}


			$count = $roleDataQry->count();
			$totalPage = ceil($count / $limit);

			$roleData = $roleDataQry->skip(($page - 1) * $limit)
				->take($limit)
				->get();


			$role_data = [];
			foreach ($roleData as $data) {
				$rdata = [
					'id' => encode_id($data->id),
					'name' => $data->name,
				];
				$role_data[] = $rdata;
			}

			return [
				'status' => 'success',
				'message' => 'Role List.',
				'data' => [
					'data' => $role_data,
					'total_count' => $count,
					'page' => $page,
					'total_filter_count' => $totalFilterCount,
					'total_page' => $totalPage,
				]
			];

		} catch (\Exception $e) {
			Log::error($e);
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}
	/**
	 * Summary of createRole
	 * @param CheckRoleRequest $request
	 * @return array
	 */
	public function createRole(CheckRoleRequest $request): array
	{
		try {
			$role = Role::create(['name' => $request->name, 'guard_name' => 'admin', 'is_active' => 1]);
			return array('status' => 'success', 'message' => 'Role created successfully.', 'data' => $role);
		} catch (\Exception $e) {
			Log::error('create Role Error: ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}


	public function editRole(UpdateRoleRequest $request): array
	{
		try {
			$role = Role::where('id', decode_id($request->id))->Update(['name' => $request->name]);
			return array('status' => 'success', 'message' => 'Role Update successfully.', 'data' => $role);
		} catch (\Exception $e) {
			Log::error('create Role Error: ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}
	/**
	 * Summary of assignRoleToUser
	 * @param mixed $request
	 * @return array
	 */
	public function assignRoleToUser($request)
	{
		try {
			$role = Role::where('name', $request->role_name)->where('guard_name', 'admin')->first();
			$user = User::find($request->user_id);
			$user->assignRole($role);
			return array('status' => 'success', 'message' => 'Role assigned to user successfully.', 'data' => []);
		} catch (\Exception $e) {
			Log::error('create Role Error: ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}
}
