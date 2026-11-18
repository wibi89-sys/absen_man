<?php

namespace Database\Seeders;

use App\Services\DatabaseManagementService;
use Illuminate\Database\Seeder;

class ProductionDatabaseSeeder extends Seeder
{
    /**
     * Seed baseline database for real production without dummy records.
     */
    public function run(): void
    {
        $service = app(DatabaseManagementService::class);
        $service->seedCleanProduction();
    }
}
