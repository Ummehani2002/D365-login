<?php

namespace App\Http\Requests\Rbac;

use App\Models\CompanyMembership;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipRequest extends FormRequest
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
        /** @var CompanyMembership|null $membership */
        $membership = $this->route('membership');

        return [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'user_code' => [
                'nullable',
                'string',
                'max:191',
                Rule::unique('users', 'user_code')->ignore($membership?->user_id),
            ],
            'provider' => ['nullable', 'string', 'max:512'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ];
    }
}

