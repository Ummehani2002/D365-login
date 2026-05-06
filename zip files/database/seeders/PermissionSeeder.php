<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['slug' => 'settings.access', 'name' => 'Access settings'],
            ['slug' => 'users.manage', 'name' => 'Manage users'],
            ['slug' => 'roles.manage', 'name' => 'Manage roles'],
            ['slug' => 'permissions.manage', 'name' => 'Manage permissions'],
            ['slug' => 'menu_match.manage', 'name' => 'Manage menu match'],

            // Existing module permissions kept for backwards compatibility.
            ['slug' => 'masters.access', 'name' => 'Access masters'],
            ['slug' => 'modules.access', 'name' => 'Access modules'],
            ['slug' => 'item_issue.access', 'name' => 'Item issue module'],
            ['slug' => 'pr.access', 'name' => 'Purchase requisition module'],
            ['slug' => 'grn.access', 'name' => 'Goods receive note module'],
        ];

        foreach ($rows as $row) {
            Permission::query()->firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']]
            );
        }
    }
}

