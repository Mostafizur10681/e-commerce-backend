<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BangladeshLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing tables to prevent duplicate records
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('thanas')->truncate();
        DB::table('districts')->truncate();
        DB::table('divisions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $sqlPath = database_path('bangladesh_locations.sql');
        if (!file_exists($sqlPath)) {
            throw new \Exception("bangladesh_locations.sql not found at: " . $sqlPath);
        }

        $sql = file_get_contents($sqlPath);

        // Split statements by semicolon
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (empty($statement)) {
                continue;
            }
            // Execute only INSERT INTO statements (the table structures are handled by migrations)
            if (stripos($statement, 'INSERT INTO') === 0) {
                DB::statement($statement);
            }
        }
    }
}
