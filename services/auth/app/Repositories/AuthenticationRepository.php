<?php

namespace App\Repositories;

use App\Models\PasswordReset;
use App\Interfaces\AuthenticationRepositoryInterface;
use App\Services\AuthServiceClient;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\UserDetail;
use Illuminate\Support\Carbon;
use Modules\Customer\Jobs\SendMailToGenerateOtp;
use Modules\Customer\Jobs\SendResetPasswordNotification;
use Str;

class AuthenticationRepository implements AuthenticationRepositoryInterface
{
	protected $authClient = null;
	public function __construct(AuthServiceClient $authServiceClient)
	{
		$this->authClient = $authServiceClient;
	}

	/**
	 * User Login
	 */
	public function adminLogin($request)
	{
		try {
			if (config('app.env') != 'local') {
				$captchaToken = $request->header('X-recaptcha-token');
				$responseCaptcha = $this->varifyRecaptch($captchaToken);

				if (!($responseCaptcha->json()["success"] ?? false)) {
					return array('status' => 'failed', 'message' => 'The google recaptcha is required. ');
				}
			}
			$user_name = $request->user_name;
			$password = $request->password;
			$response = $this->authClient->login('/oauth/token', [
				'grant_type' => 'password',
				'client_id' => config('passport.password_access_client.id'),
				'client_secret' => config('passport.password_access_client.secret'),
				'username' => $user_name,
				'password' => $password,
				'scope' => '',
			]);

			if ($response['success']) {
				return array('status' => 'success', 'message' => 'User has been login successfully.', 'data' => $response['data']);
			}


			return array('status' => 'failed', 'message' => $response['data']['message'] ?? 'Unauthorized access');
		} catch (\Exception $e) {
			Log::error('Registration error: ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}

	public function varifyRecaptch($captchaToken)
	{
		return Http::get("https://www.google.com/recaptcha/api/siteverify", [
			'secret' => config('master.google_recaptcha_secret'),
			'response' => $captchaToken
		]);
	}
	/**
	 * User registration
	 */
	public function registerAdmin($userData)
	{
		try {

			$userData['email_verified_at'] = now();
			$userData['user_type'] = 1;
			$userData['phone'] = $userData['mobile'];
			$nameData = explode(' ', $userData['name']);
			unset($userData['mobile']);
			$firstname = $nameData[0] ?? '';
			$lastname = $nameData[1] ?? '';
			$userData['name'] = $firstname . ' ' . $lastname;
			$user = User::create($userData);

			return array('status' => 'success', 'message' => 'User has been registered successfully.', 'data' => $user);
		} catch (\Exception $e) {
			Log::error('Registration error: ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}
	/**
	 * Summary of logout
	 * @param mixed $request
	 * @return array
	 */
	public function logout(): array
	{
		try {

			$token = auth('admin')->user()->token();

			// Revoke the token
			$token->revoke();
			return array('status' => 'success', 'message' => 'Logged out successfully.', 'data' => '');
		} catch (\Exception $e) {
			Log::error('Registration error: ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}
	/**
	 * Summary of callRefreshToken
	 * @param mixed $request
	 * @return array
	 */
	public function callRefreshToken($request)
	{
		try {

			$response = $this->authClient->login( '/oauth/token', [
				'grant_type' => 'refresh_token',
				'refresh_token' => $request->refresh_token,
				'client_id' => config('passport.password_access_client.id'),
				'client_secret' => config('passport.password_access_client.secret'),
				'scope' => '',
			]);

			if (!$response['success']) {
				return array('status' => 'failed', 'message' => $response['data']);
			}

			return array('status' => 'success', 'message' => 'Access Token genrated successfully.', 'data' => $response['data']);
		} catch (\Exception $e) {
			Log::error('RefreshToken error: ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}

	public function otpGenerate($request): array
	{
		try {
			$user = User::where('email', $request->email)->first();
			$otp = rand(100000, 999999);
			$otp_expires_at = now()->addMinutes(10);
			if (!$user) {
				return array('status' => 'failed', 'message' => 'Invalid User');
			}
			UserDetail::updateOrCreate(['user_id' => $user->id], [
				'otp' => $otp,
				'otp_expires_at' => $otp_expires_at,
				'created_at' => now(),
			]);
			// SendMailToGenerateOtp::dispatch($user, $otp);
			return array('status' => 'success', 'message' => 'OTP sent successfully.');
		} catch (\Exception $e) {
			Log::error('Registration error: ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}

	public function loginOtpVerify($request): array
	{
		try {
			$user = User::where('email', $request->email)->where('user_type', 1)->first();
			$user_detail = UserDetail::where('user_id', $user->id)->latest()->first();

			if (!$user_detail) {
				return array('status' => 'failed', 'message' => 'Invalid User');
			}

			if ($user_detail->otp !== (int) $request->otp || $user_detail->otp_expires_at < now()) {
				return ['status' => 'failed', 'message' => 'Invalid or expired OTP.'];
			}

			// OTP is valid, generate access token
			$tokenobj = $user->createToken('Laravel Personal Access Client');
			$accessToken = $tokenobj->accessToken;

			// Clear OTP
			$user_detail->otp = null;
			$user_detail->otp_expires_at = null;
			$user_detail->save();
			return array('status' => 'success', 'message' => 'OTP verified successfully.', 'data' => $accessToken);
		} catch (\Exception $e) {
			dd(vars: $e->getMessage());
			Log::error('Error Otp Verification : ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}
	/**
	 * Summary of sendPasswordResetLink
	 * @param mixed $request
	 * @return array
	 */
	public function sendPasswordResetLink($request): array
	{
		try {
			$token = Str::random(60);
			// Store token in password_resets table
			PasswordReset::updateOrInsert(
				['email' => $request->email],
				[
					'email' => $request->email,
					//'token' => Hash::make($token),
					'token' => $token,
					'created_at' => Carbon::now()
				]
			);

			$resetLink = url("/password/reset/{$token}?email={$request->email}");

			// Find the user by email
			$user = User::where('email', $request->email)->first();

			// Dispatch the job to send the reset link notification
			// SendResetPasswordNotification::dispatch($user, $resetLink);

			return array('status' => 'success', 'message' => 'Password reset link sent successfully');
		} catch (\Exception $e) {
			Log::error('(Error) Send Password Reset Link: ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}
	/**
	 * Summary of resetPassword
	 * @param mixed $request
	 * @return array
	 */
	public function resetPassword($request): array
	{
		try {
			$password = $request->password;
			$tokenData = PasswordReset::where('token', $request->token)->where('email', $request->email)->first();
			if (!$tokenData) {
				return array(
					'status' => 'Failed',
					'message' => 'Invalid Token Code',
					401
				);
			}
			$user = User::where('email', $tokenData->email)->first();
			if (!$user) {
				return response()->json([
					'error' => 'Invalid Request',
					'message' => 'User Email Not found'
				], 401);
			}
			$user->password = \Hash::make($password);
			$user->update();

			PasswordReset::where('email', $user->email)
				->delete();

			$response = Http::asForm()->post(config('app.admin_url') . '/oauth/token', [
				'grant_type' => 'password',
				'client_id' => config('passport.password_access_client.id'),
				'client_secret' => config('passport.password_access_client.secret'),
				'username' => $request->email,
				'password' => $password,
				'scope' => '',
			]);

			// Check if the request was successful
			if ($response->successful()) {
				return array('status' => 'success', 'message' => 'Reset Password Successfull');
			}

			return array(
				[
					'status' => 'failed',
					'message' => $response->json()['message'] ?? 'An error occurred'
				]
			);

		} catch (\Exception $e) {
			Log::error('(Error) Reset Password : ' . $e->getMessage());
			return array('status' => 'failed', 'message' => $e->getMessage());
		}
	}

}
