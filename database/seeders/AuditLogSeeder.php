<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuditLogSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks temporarily to truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AuditLog::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Baseline logs are now stored in storage/app/audit_baseline.json
        // to ensure the Admin only sees real system-generated logs.
    }
}
