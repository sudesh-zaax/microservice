<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\User;
class CheckLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_name' => [
                'required',
                'string',
                'exists:users,user_name',
                function ($attribute, $value, $fail) {
                    $user = User::where('user_name', $value)->where('is_active', 1)->first();
                    
                    if ($user && $user->user_type !== 1) {
                        $fail('Unauthorized user name');
                    }
                },
            ],
			'password' => ['required','min:8'],
        ];
    }
        /**
     * Summary of failedValidation
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return never
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }  
   
}
