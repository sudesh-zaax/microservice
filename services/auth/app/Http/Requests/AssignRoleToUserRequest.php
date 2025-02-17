<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\User;
class AssignRoleToUserRequest extends FormRequest
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
			'role_name' => [
				'required',
				'string',
				Rule::exists('roles', 'name')->where('guard_name', $guardName),
			],
			'user_id' => [
				'required',
				'integer',
				'exists:users,id',
				function ($attribute, $value, $fail) {
					$user = User::find($value);
					if ($user && $user->user_type !== 1) {
						$fail('Unauthorized user ID');
					}
				},
			],
		];
	}



}
