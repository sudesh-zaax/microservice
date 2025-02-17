<?php

namespace App\Http\Requests;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AssignPermissionToRoleRequest extends FormRequest
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
		 $guardName = auth()->getDefaultDriver();
		return [
           'role_id' => ['required','string', function($attribute,$value,$fail):void {
                $roles=   Role::where('id',decode_id(hashid: $value))->first();
                if(!$roles){
                         $fail('Please enter valid role id.');
                }
            }],
            'permission_name' => ['required', 'array'],
            'permission_name.*' => ['integer', 'exists:permissions,id']
		];
	}
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
