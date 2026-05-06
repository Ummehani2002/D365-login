<?php
namespace App\Http\Requests\Rbac;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void
    {
        $this->merge(['user_code' => trim((string) $this->input('user_code', '')) ?: null,'email' => strtolower(trim((string) $this->input('email', '')))]);
    }
    public function rules(): array
    {
        $userCodeRules = ['nullable', 'string', 'max:191'];
        if (Schema::hasColumn('users', 'user_code')) {
            $userCodeRules[] = Rule::unique('users', 'user_code');
        }

        return ['company_id' => ['required','integer','exists:companies,id'],'email' => ['required','email','max:255'],'name' => ['required','string','max:255'],'user_code' => $userCodeRules,'provider' => ['nullable','string','max:512'],'role_ids' => ['nullable','array'],'role_ids.*' => ['integer','exists:roles,id'],'role_scopes' => ['nullable','array'],'role_scopes.*.role_id' => ['required','integer','exists:roles,id'],'role_scopes.*.all_organizations' => ['required','boolean'],'role_scopes.*.company_ids' => ['nullable','array'],'role_scopes.*.company_ids.*' => ['integer','exists:companies,id']];
    }
}
