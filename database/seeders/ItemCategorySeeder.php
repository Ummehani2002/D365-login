<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    /**
     * Seed item categories for each company.
     */
    public function run(): void
    {
        $templates = [
            ['id' => 'CAT001', 'name' => 'Raw Materials'],
            ['id' => 'CAT002', 'name' => 'Finished Goods'],
            ['id' => 'CAT003', 'name' => 'Services'],
            ['id' => 'CAT004', 'name' => 'Office Supplies'],
            ['id' => 'CAT005', 'name' => 'Maintenance'],
        ];

        Company::query()->orderBy('id')->chunk(100, function ($companies) use ($templates): void {
            foreach ($companies as $company) {
                $companyCode = strtoupper((string) ($company->company_id ?? $company->d365_id ?? 'COMP'));

                foreach ($templates as $template) {
                    $d365Id = $companyCode.'-'.$template['id'];
                    $name = $template['name'].' - '.$companyCode;

                    ItemCategory::query()->firstOrCreate(
                        [
                            'company_id' => (int) $company->id,
                            'd365_id' => $d365Id,
                        ],
                        [
                            'name' => $name,
                        ]
                    );
                }
            }
        });
    }
}
