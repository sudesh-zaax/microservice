<?php

namespace App\Interfaces;

use App\Http\Requests\CheckLoginRequest;
use App\Http\Requests\OtpGenerateRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\Request;

interface AuthenticationRepositoryInterface
{
    /**
     * Summary of adminLogin
     * @param CheckLoginRequest $data
     * @return array
     */
    public function adminLogin(CheckLoginRequest $data);
    
    /**
     * Summary of registerAdmin
     * @param RegisterRequest $data
     * @return array
     */
    public function registerAdmin(RegisterRequest $data);

    /**
     * Summary of logout
     * @return array
     */
    public function logout();

    /**
     * Summary of callRefreshToken
     * @param Request $data
     * @return array
     */
    public function callRefreshToken(Request $data);

    /**
     * Summary of otpGenerate
     * @param OtpGenerateRequest $request
     * @return array
     */
    public function otpGenerate(OtpGenerateRequest $request);

    /**
     * Summary of otpVerify
     * @param VerifyOtpRequest $request
     * @return array
     */
    public function loginOtpVerify(VerifyOtpRequest $request);

    /**
     * Summary of sendPasswordResetLink
     * @param Request $request
     * @return array
     */
    public function sendPasswordResetLink(Request $request);
    
    /**
     * Summary of resetPassword
     * @param Request $request
     * @return array
     */
    public function resetPassword(Request $request);
}
