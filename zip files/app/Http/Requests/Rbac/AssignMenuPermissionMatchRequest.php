<?php

namespace App\Http\Requests\Rbac;

use Illuminate\Foundation\Http\FormRequest;

class AssignMenuPermissionMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'menu_key' => ['required', 'string', 'max:191'],
            'permission_id' => ['nullable', 'integer', 'exists:permissions,id'],
        ];
    }
}

