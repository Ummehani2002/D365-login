<?php

namespace App\Http\Requests\Rbac;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $userCode = trim((string) $this->input('user_code', ''));
        $email = strtolower(trim((string) $this->input('email', '')));

        $this->merge([
            'user_code' => $userCode !== '' ? $userCode : null,
            'email' => $email,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'user_code' => ['nullable', 'string', 'max:191', Rule::unique('users', 'user_code')],
            'provider' => ['nullable', 'string', 'max:512'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'role_scopes' => ['nullable', 'array'],
            'role_scopes.*.role_id' => ['required', 'integer', 'exists:roles,id'],
            'role_scopes.*.all_organizations' => ['required', 'boolean'],
            'role_scopes.*.company_ids' => ['nullable', 'array'],
            'role_scopes.*.company_ids.*' => ['integer', 'exists:companies,id'],
        ];
    }
}

