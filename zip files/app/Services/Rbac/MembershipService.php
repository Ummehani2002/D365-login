<?php

namespace App\Services\Rbac;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MembershipService
{
    public function __construct(
        private readonly RoleScopeService $roleScopeService
    ) {
    }

    /**
     * @return Collection<int, CompanyMembership>
     */
    public function listMemberships(): Collection
    {
        return CompanyMembership::query()
            ->with(['user', 'company', 'roles'])
            ->orderByDesc('company_memberships.id')
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listCompanies(): array
    {
        return Company::query()
            ->select(['id', 'name', 'd365_id'])
            ->whereNotNull('d365_id')
            ->orderBy('name')
            ->get()
            ->map(fn (Company $company) => [
                'id' => $company->id,
                'code' => strtoupper((string) $company->d365_id),
                'name' => $company->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Role>
     */
    public function listRoles(): Collection
    {
        return Role::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createMembership(array $payload): CompanyMembership
    {
        $roleIds = $this->normalizeIds($payload['role_ids'] ?? []);
        $roleScopes = $payload['role_scopes'] ?? [];

        $this->validateRoleIds($roleIds);
        $this->roleScopeService->validateRoleScopes($roleIds, $roleScopes);

        return DB::transaction(function () use ($payload, $roleIds, $roleScopes): CompanyMembership {
            $user = User::query()->where('email', $payload['email'])->first();

            if ($user) {
                $user->fill([
                    'name' => $payload['name'],
                    'user_code' => $payload['user_code'] ?? $user->user_code,
                    'provider' => array_key_exists('provider', $payload)
                        ? $payload['provider']
                        : $user->provider,
                ]);
                $user->save();
            } else {
                $user = User::query()->create([
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'password' => Hash::make(Str::random(32)),
                    'user_code' => $payload['user_code'] ?? null,
                    'provider' => $payload['provider'] ?? null,
                ]);
            }

            $exists = CompanyMembership::query()
                ->where('user_id', $user->id)
                ->where('company_id', (int) $payload['company_id'])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'company_id' => ['This user already has access to the selected company.'],
                ]);
            }

            $membership = CompanyMembership::query()->create([
                'user_id' => $user->id,
                'company_id' => (int) $payload['company_id'],
            ]);

            if ($roleIds !== []) {
                $membership->roles()->sync($roleIds);
                $this->roleScopeService->syncScopesForMembership($membership, $roleIds, is_array($roleScopes) ? $roleScopes : []);
            }

            $membership->load(['user', 'company', 'roles']);

            return $membership;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateMembership(CompanyMembership $membership, array $payload): CompanyMembership
    {
        $roleIdsProvided = array_key_exists('role_ids', $payload);
        $roleIds = $this->normalizeIds($payload['role_ids'] ?? []);
        if ($roleIdsProvided) {
            $this->validateRoleIds($roleIds);
        }

        return DB::transaction(function () use ($membership, $payload, $roleIdsProvided, $roleIds): CompanyMembership {
            $user = $membership->user;

            $emailTaken = User::query()
                ->where('email', $payload['email'])
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailTaken) {
                throw ValidationException::withMessages([
                    'email' => ['Another user already uses this email.'],
                ]);
            }

            $user->fill([
                'email' => $payload['email'],
                'name' => $payload['name'],
                'user_code' => array_key_exists('user_code', $payload)
                    ? $payload['user_code']
                    : $user->user_code,
                'provider' => array_key_exists('provider', $payload)
                    ? $payload['provider']
                    : $user->provider,
            ]);
            $user->save();

            if ($roleIdsProvided) {
                $membership->roles()->sync($roleIds);
                $this->roleScopeService->pruneScopesByRoleIds($membership, $roleIds);
            }

            $membership->load(['user', 'company', 'roles']);

            return $membership;
        });
    }

    public function deleteMembership(CompanyMembership $membership): void
    {
        DB::transaction(function () use ($membership): void {
            $membership->roles()->sync([]);
            $membership->delete();
        });
    }

    /**
     * @param  array<int, int>  $roleIds
     */
    private function validateRoleIds(array $roleIds): void
    {
        if ($roleIds === []) {
            return;
        }

        $count = Role::query()
            ->whereIn('id', $roleIds)
            ->count();

        if ($count !== count($roleIds)) {
            throw ValidationException::withMessages([
                'role_ids' => ['One or more selected roles are invalid.'],
            ]);
        }
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->map(fn (mixed $id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}

