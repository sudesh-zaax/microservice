<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
class CheckPermissionRequest extends FormRequest
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
			'name' => [
				'required',
					'string',
					function ($attribute, $value, $fail) {
						
							$name = $this->input('name');
								if ((Permission::where('name', $name)->where('guard_name', $this->getGuard())->exists())) {
								$fail('Permission already exists.');
							}
					},
			],
			'description' => [
				'required',
				'string',
			],
			'icon' => [
				Rule::requiredIf(function () {
					$type = $this->input('type');
					return in_array($type, [1, 2]);
				})
			],
			'type' => [
				'required',
				'integer',
			],
			'order' => [
				'required',
				'integer',
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
	protected function getGuard()
	{	
		return auth()->getDefaultDriver()??'';
	}

}
