<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MenuController;
use App\Http\Middleware\CheckUserAuthorization;


Route::prefix('v1')->group(function () {
	Route::post('/register', [AuthenticationController::class, 'register'])->name('register');
	Route::post('/login', [AuthenticationController::class, 'login'])->name('login');
	Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
	Route::post('/login/otpGenerate', [AuthenticationController::class, 'otpGenerate'])->name('otpGenerate');
	Route::post('/login/otpVerify', [AuthenticationController::class, 'loginOtpVerify'])->name('otpVerify');
	Route::post('/sendPasswordResetLink', [AuthenticationController::class, 'sendPasswordResetLink'])->name('sendPasswordResetLink');
	Route::post('/resetPassword', [AuthenticationController::class, 'resetPassword'])->name('resetPassword');
	Route::post('/callRefreshToken', [AuthenticationController::class, 'callRefreshToken'])->name('callRefreshToken');


	// Route::group(['middleware' => ['auth:admin', 'can:admin', CheckUserAuthorization::class, 'throttle:api']], function () {
		//role route
		Route::post('/roleList/{page}/{perPage}', [RoleController::class, 'index'])->name('getRoleList')->where(['page' => '[0-9]+', 'perPage' => '[0-9]+']);
		Route::post('/createRole', [RoleController::class, 'createRole'])->name('createRole');
		Route::post('/assignRoleToUser', [RoleController::class, 'assignRoleToUser'])->name('assignRoleToUser');
		Route::post('/editRole', [RoleController::class, 'editRole'])->name('editRole');
		//permission route
		Route::post('/permissionList/{page}/{perPage}', [PermissionController::class, 'index'])->name('getPermissionList');
		Route::post('/createPermission', [PermissionController::class, 'createPermission'])->name('createPermission');
		Route::post('/{module}/assignPermissionToRole', [PermissionController::class, 'assignPermissionToRole'])->name('assignPermissionToRole');
		Route::get('/getAllWithAssignedPermissionList/{role_id}', [PermissionController::class, 'getAllWithAssignedPermissionList'])->name('getAllWithAssignedPermissionList');
		Route::post('/{module}/revokePermissionToRole', [PermissionController::class, 'revokePermissionToRole'])->name('revokePermissionToRole');
		//menus route
		Route::get('/getUserMenu', [MenuController::class, 'getRoleWiseMenu'])->name('getUserMenu');
		Route::get('/pageContent/{page}', [MenuController::class, 'getPageContent'])->name('pageContent');
		include('service.php');
	

	Route::get('/syncUri/{service?}', [MenuController::class, 'syncUri'])->name('syncUri');
	Route::get('/syncRedisMaster', function () {
		$policyUrl = config('admin.policy_url') . '/api/v1/syncRedisMaster';
		$response = Http::send(request()->method(), $policyUrl, [
			'timeout' => 2000,
		]);
		return response()->json(['status' => 'sucess', 'data' => $response->json()]);
	})->name('syncRedisMaster');
	// });
});


