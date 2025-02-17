<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Role;
class UpdateAdminUserRequest extends FormRequest
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
            'user_id'=>[
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $user_id = decode_id($this->input('user_id'));
                    if (!(User::where('id', $user_id)->exists())) {
                            $fail('user_id is not found.');
                    }
                },
            ],
            'branch_ids' => 'required|array',
            'branch_ids.*' => 'exists:mst_branches,id',
            'role_id' => [
				'required',
					'string',
					function ($attribute, $value, $fail) {
							$role_id = decode_id($this->input('role_id'));
                            if (!(Role::where('id', $role_id)->exists())) {
                                 $fail('Role_id not found.');
                            }
					},
			],
          
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
