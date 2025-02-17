<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Interfaces\AuthenticationRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckLoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\OtpGenerateRequest;
use App\Http\Requests\VerifyOtpRequest;

class AuthenticationController extends Controller
{

	protected $authenticationRepository;

	/**
	 * Summary of __construct
	 * @param \App\Interfaces\AuthenticationRepositoryInterface $authenticationRepository
	 */
	public function __construct(AuthenticationRepositoryInterface $authenticationRepository)
	{
		$this->authenticationRepository = $authenticationRepository;
	}

	/**
	 * Admin of login
	 * @param \App\Http\Requests\CheckLoginRequest $request
	 * @return JsonResponse|mixed
	 */
	public function login(CheckLoginRequest $request)
	{
		try {
			$dataArr = $this->authenticationRepository->adminLogin($request);
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['data'], $dataArr['message'], 201);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 401);
			}
		} catch (\Exception $e) {
			Log::error('login error: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}

	/**
	 * Admin user of register
	 * @param \App\Http\Requests\RegisterRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function register(RegisterRequest $request): JsonResponse
	{
		try {
			$userData = $request->validated();
			$dataArr = $this->authenticationRepository->registerAdmin($userData);
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['data'], $dataArr['message'], 201);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 500);
			}
		} catch (\Exception $e) {
			Log::error('Registration error: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}

	public function logout(Request $request): JsonResponse
	{
		try {
			$dataArr = $this->authenticationRepository->logout();
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['data'], $dataArr['message'], 200);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 500);
			}
		} catch (\Exception $e) {
			Log::error('logout error: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}

	/**
	 * refresh token
	 *
	 * @return void
	 */
	public function callRefreshToken(Request $request): JsonResponse
	{
		try {
			$dataArr = $this->authenticationRepository->callRefreshToken($request);
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['data'], $dataArr['message'], 201);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 401);
			}
		} catch (\Exception $e) {
			Log::error('Refresh error: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}

	/**
	 * Summary of otpGenerate
	 * @param \App\Http\Requests\OtpGenerateRequest $request
	 * @return JsonResponse|mixed
	 */
	public function otpGenerate(OtpGenerateRequest $request)
	{
		try {
			$dataArr = $this->authenticationRepository->otpGenerate($request);
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['message'], 201);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 404);
			}
		} catch (\Exception $e) {
			Log::error('Otp Generate Error: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}

	/**
	 * Summary of otpVerify
	 * @param \App\Http\Requests\VerifyOtpRequest $request
	 * @return JsonResponse|mixed
	 */
	public function loginOtpVerify(VerifyOtpRequest $request)
	{
		try {
			$dataArr = $this->authenticationRepository->loginOtpVerify($request);
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['message'], $dataArr['data'], 201);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 404);
			}
		} catch (\Exception $e) {
			Log::error('Otp Verification Error: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}

	/**
	 * Summary of sendPasswordResetLink
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse|mixed
	 */
	public function sendPasswordResetLink(Request $request)
	{
		try {
			$dataArr = $this->authenticationRepository->sendPasswordResetLink($request);
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['message'], 201);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 404);
			}
		} catch (\Exception $e) {
			Log::error('Send Password Reset Link Error: ' . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}

	/**
	 * Summary of resetPassword
	 * @param \Illuminate\Http\Request $request
	 * @return JsonResponse|mixed
	 */
	public function resetPassword(Request $request)
	{
		try {
			$dataArr = $this->authenticationRepository->resetPassword($request);
			if ($dataArr['status'] == 'success') {
				return $this->returnResponse($dataArr['message'], 201);
			} else {
				return $this->returnExceptionResponse($dataArr['message'], 404);
			}
		} catch (\Exception $e) {
			Log::error("Reset Password Error : " . $e->getMessage());
			return $this->returnExceptionResponse($e->getMessage(), 500);
		}
	}
}
