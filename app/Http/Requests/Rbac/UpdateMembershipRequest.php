<?php
namespace App\Http\Requests\Rbac;
use App\Models\CompanyMembership;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
class UpdateMembershipRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void
    {
        $this->merge(['user_code' => trim((string) $this->input('user_code', '')) ?: null,'email' => strtolower(trim((string) $this->input('email', '')))]);
    }
    public function rules(): array
    {
        $membership = $this->route('membership');
        $userCodeRules = ['nullable', 'string', 'max:191'];
        if (Schema::hasColumn('users', 'user_code')) {
            $userCodeRules[] = Rule::unique('users', 'user_code')->ignore($membership?->user_id);
        }
        return ['email' => ['required','email','max:255'],'name' => ['required','string','max:255'],'user_code' => $userCodeRules,'provider' => ['nullable','string','max:512'],'role_ids' => ['nullable','array'],'role_ids.*' => ['integer','exists:roles,id']];
    }
}
