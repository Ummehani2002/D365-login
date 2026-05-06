# RBAC Complete Handoff File Map

This package includes all RBAC backend + UI + integration files used in this Laravel project.

## app/Http/Controllers
- RbacPermissionController.php
- RbacRoleController.php
- RbacUserController.php
- RbacMenuMatchController.php
- SettingsController.php
- DashboardController.php

## app/Http/Middleware
- CheckPermission.php
- EnsureSuperAdmin.php

## app/Http/Requests/Rbac
- StorePermissionRequest.php
- UpdatePermissionRequest.php
- StoreRoleRequest.php
- UpdateRoleRequest.php
- StoreMembershipRequest.php
- UpdateMembershipRequest.php
- UpsertRoleScopeRequest.php
- SaveMenuPermissionMatchRequest.php
- AssignMenuPermissionMatchRequest.php

## app/Models
- User.php
- Company.php
- Permission.php
- Role.php
- CompanyMembership.php
- CompanyMembershipRoleScope.php
- MenuPermissionMatch.php

## app/Services/Rbac
- MembershipService.php
- RoleService.php
- PermissionService.php
- RoleScopeService.php
- MenuAccessService.php

## app/Providers
- AppServiceProvider.php

## bootstrap
- app.php

## config
- rbac_menu_match.php

## routes
- web.php

## database/migrations
- 2026_05_04_140000_create_company_rbac_tables.php
- 2026_05_04_150000_add_is_super_admin_to_users_table.php
- 2026_05_04_160000_company_membership_multiple_roles.php
- 2026_05_04_174500_make_roles_global_remove_company_scope.php
- 2026_05_04_175500_rebuild_roles_table_without_company_or_slug.php
- 2026_05_05_100000_add_rbac_profile_columns_to_users_table.php
- 2026_05_05_120000_add_role_organization_scopes.php
- 2026_05_05_130000_create_menu_permission_matches_table.php
- 2026_05_05_140000_normalize_rbac_tables.php
- 2026_05_05_170000_bootstrap_rbac_manage_permissions.php

## database/seeders
- PermissionSeeder.php

## resources/views/settings/rbac
- users.blade.php
- roles.blade.php
- permissions.blade.php
- menu-match.blade.php
- partials/sidebar.blade.php
- partials/styles.blade.php
- partials/d365-rbac-page-styles.blade.php

## integration views
- resources/views/settings/token.blade.php
- resources/views/settings/credentials.blade.php
- resources/views/dashboard.blade.php
- resources/views/partials/global-company-selector.blade.php
