<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class CompanyMembershipRoleScope extends Model
{
    protected $fillable = ['company_membership_id','role_id','all_organizations'];
    protected function casts(): array { return ['all_organizations' => 'boolean']; }
    public function membership(): BelongsTo { return $this->belongsTo(CompanyMembership::class, 'company_membership_id'); }
    public function companyMembership(): BelongsTo { return $this->membership(); }
    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function companies(): BelongsToMany { return $this->belongsToMany(Company::class, 'company_membership_role_scope_companies', 'company_membership_role_scope_id', 'company_id')->withTimestamps(); }
}
