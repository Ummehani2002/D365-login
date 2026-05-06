<?php
namespace App\Http\Requests\Rbac;
use Illuminate\Foundation\Http\FormRequest;
class SaveMenuPermissionMatchRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'mappings' => ['required', 'array', 'min:1'],
            'mappings.*.id' => ['required', 'integer', 'exists:menu_permission_matches,id'],
            'mappings.*.permission_id' => ['nullable', 'integer', 'exists:permissions,id'],
        ];
    }
}
