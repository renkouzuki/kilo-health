<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAuditLogsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'userId'=>'required|integer|exists:users,id'
        ];
    }

    public function messages()
    {
        return [
            'userId.required' => 'The user ID is required to fetch audit logs.',
            'userId.integer' => 'The user ID must be an integer.',
            'userId.exists' => 'The specified user does not exist.',
        ];
    }
}
