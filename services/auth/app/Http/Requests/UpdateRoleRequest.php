<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
class UpdateRoleRequest extends FormRequest
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
            'id' => [
				'required',
					'string',
					function ($attribute, $value, $fail) {
					$role = Role::where('id', decode_id($value))->first();
					if (!$role) {
						$fail('Invalid Role id.');
					}
				},
			],
			'name' => [
				'required',
					'string',
					function ($attribute, $value, $fail) {
					$role = Role::where('name', $value)->where('guard_name', $this->getGuard())->where('id','!=',decode_id($this->input('id')))->first();
					if ($role) {
						$fail('Role is already exists.');
					}
				},
			],
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

	protected function getGuard()
	{
		return request()->input('guard') ?? 'admin';
	}
	

}
