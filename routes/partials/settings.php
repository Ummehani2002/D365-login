<?php

use App\Http\Controllers\RbacMenuMatchController;
use App\Http\Controllers\RbacPermissionController;
use App\Http\Controllers\RbacRoleController;
use App\Http\Controllers\RbacUserController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    // ─── Settings root redirect ───────────────────────────────────────────────
    Route::get('/settings', function () {
        $user = request()->user();
        return $user && $user->isSuperAdmin()
            ? redirect()->route('settings.token')
            : redirect()->route('settings.users.index');
    })->middleware('super_admin')->name('settings.index');

    // ─── Global super-admin only ──────────────────────────────────────────────
    Route::middleware('global_super_admin')->group(function () {
        Route::get('/settings/token',          [SettingsController::class, 'tokenIndex'])->name('settings.token');
        Route::post('/settings/token/generate',[SettingsController::class, 'generateToken'])->name('settings.token.generate');
        Route::get('/settings/credentials',   [SettingsController::class, 'credsIndex'])->name('settings.credentials');
        Route::post('/settings/credentials',  [SettingsController::class, 'saveCredentials'])->name('settings.credentials.save');
        Route::get('/settings/roles-permissions', fn () => redirect()->route('settings.token'))->name('settings.roles-permissions');
    });

    // ─── RBAC (super admin) ───────────────────────────────────────────────────
    Route::prefix('/settings/rbac')->middleware('super_admin')->group(function () {

        // Pages
        Route::get('/users',       [RbacUserController::class, 'index'])->name('settings.users.index');
        Route::get('/roles',       [RbacRoleController::class, 'index'])->name('settings.roles.index');
        Route::get('/permissions', [RbacPermissionController::class, 'index'])->name('settings.permissions.index');
        Route::get('/menu-match',  [RbacMenuMatchController::class, 'index'])->name('settings.menu-match.index');

        // Users API
        Route::prefix('/api/users')->group(function () {
            Route::get('/memberships',                              [RbacUserController::class, 'listMemberships'])->name('settings.users.api.memberships.index');
            Route::post('/memberships',                             [RbacUserController::class, 'storeMembership'])->name('settings.users.api.memberships.store');
            Route::put('/memberships/{membership}',                 [RbacUserController::class, 'updateMembership'])->name('settings.users.api.memberships.update');
            Route::delete('/memberships/{membership}',              [RbacUserController::class, 'destroyMembership'])->name('settings.users.api.memberships.destroy');
            Route::get('/companies',                                [RbacUserController::class, 'listCompanies'])->name('settings.users.api.companies.index');
            Route::get('/roles',                                    [RbacUserController::class, 'rolesForCompany'])->name('settings.users.api.roles.index');
            Route::get('/memberships/{membership}/role-scopes',    [RbacUserController::class, 'roleScopes'])->name('settings.users.api.memberships.role-scopes.index');
            Route::put('/memberships/{membership}/role-scopes',    [RbacUserController::class, 'upsertRoleScope'])->name('settings.users.api.memberships.role-scopes.upsert');
        });

        // Roles API
        Route::prefix('/api/roles')->group(function () {
            Route::get('/',                      [RbacRoleController::class, 'listRoles'])->name('settings.roles.api.roles.index');
            Route::post('/',                     [RbacRoleController::class, 'store'])->name('settings.roles.api.roles.store');
            Route::put('/{role}',                [RbacRoleController::class, 'update'])->name('settings.roles.api.roles.update');
            Route::delete('/{role}',             [RbacRoleController::class, 'destroy'])->name('settings.roles.api.roles.destroy');
            Route::get('/permissions/list',      [RbacRoleController::class, 'listPermissions'])->name('settings.roles.api.permissions.index');
        });

        // Permissions API
        Route::prefix('/api/permissions')->group(function () {
            Route::get('/',              [RbacPermissionController::class, 'listPermissions'])->name('settings.permissions.api.permissions.index');
            Route::post('/',             [RbacPermissionController::class, 'store'])->name('settings.permissions.api.permissions.store');
            Route::put('/{permission}',  [RbacPermissionController::class, 'update'])->name('settings.permissions.api.permissions.update');
            Route::delete('/{permission}',[RbacPermissionController::class, 'destroy'])->name('settings.permissions.api.permissions.destroy');
        });

        // Menu-match API
        Route::prefix('/api/menu-match')->group(function () {
            Route::get('/mappings',       [RbacMenuMatchController::class, 'listMappings'])->name('settings.menu-match.api.mappings.index');
            Route::put('/mappings',       [RbacMenuMatchController::class, 'updateMappings'])->name('settings.menu-match.api.mappings.update');
            Route::get('/available-menus',[RbacMenuMatchController::class, 'listAvailableMenus'])->name('settings.menu-match.api.available-menus.index');
            Route::post('/assign',        [RbacMenuMatchController::class, 'assignMapping'])->name('settings.menu-match.api.assign.store');
        });
    });
});
