<?php

namespace App\Interfaces;
use App\Http\Requests\CheckRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;

interface RoleRepositoryInterface
{
   /**
    * Summary of getRoleList
    * @param array $request
    * @param int $page
    * @param int $perPage
    * @return void
    */
   public function getRoleList(Request $request,int $page,int $perPage) :array;

   /**
    * Summary of createRole
    * @param CheckRoleRequest $data
    * @return array
    */
   public function createRole(CheckRoleRequest $data) :array;

   /**
    * Summary of editRole
    * @param UpdateRoleRequest $data
    * @return array
    */
   public function editRole(UpdateRoleRequest $data) :array;
}
