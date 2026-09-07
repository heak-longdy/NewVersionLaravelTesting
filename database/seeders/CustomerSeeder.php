<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetCount = 99999999;
        $batchSize = 500000; // 500,000 records per batch for optimal performance and memory safety

        $this->command->info('Resetting customers table to ensure clean IDs and unique emails...');
        DB::statement('TRUNCATE TABLE customers RESTART IDENTITY CASCADE');

        $this->command->info("Starting generation of {$targetCount} customer records with profile photos...");

        $driver = DB::getDriverName();
        $startTotal = microtime(true);

        if ($driver === 'pgsql') {
            // High-speed PostgreSQL generate_series batch generator
            $totalBatches = (int) ceil($targetCount / $batchSize);

            for ($batch = 0; $batch < $totalBatches; $batch++) {
                $batchStart = $batch * $batchSize + 1;
                $batchEnd = min(($batch + 1) * $batchSize, $targetCount);
                $batchNum = $batch + 1;

                $t0 = microtime(true);
                $this->command->line("Processing batch {$batchNum}/{$totalBatches} (records {$batchStart} to {$batchEnd})...");

                DB::statement("
                    INSERT INTO customers (name, email, image, phone, company, address, status, notes, created_at, updated_at)
                    SELECT
                        'Customer ' || i,
                        'customer_' || i || '_' || replace(gen_random_uuid()::text, '-', '') || '@example.com',
                        'https://i.pravatar.cc/150?u=' || i,
                        '+1-555-' || lpad((floor(random() * 9000000 + 1000000))::int::text, 7, '0'),
                        'Company ' || (i % 5000 + 1),
                        (i % 9999 + 1) || ' Market Street, Suite ' || (i % 200 + 1),
                        CASE WHEN i % 3 = 0 THEN 'inactive' ELSE 'active' END,
                        'Auto-generated sample customer #' || i,
                        NOW() - (random() * interval '365 days'),
                        NOW()
                    FROM generate_series({$batchStart}, {$batchEnd}) AS s(i)
                    ON CONFLICT (email) DO NOTHING
                ");

                $elapsed = round(microtime(true) - $t0, 2);
                $this->command->info("✓ Batch {$batchNum}/{$totalBatches} inserted in {$elapsed}s");
            }
        } else {
            // Fallback for MySQL/SQLite using chunked bulk inserts
            $chunkSize = 1000;
            $now = now();

            for ($i = 0; $i < $targetCount; $i += $chunkSize) {
                $records = [];
                $limit = min($chunkSize, $targetCount - $i);
                for ($j = 0; $j < $limit; $j++) {
                    $index = $i + $j + 1;
                    $records[] = [
                        'name' => "Customer {$index}",
                        'email' => "customer_{$index}_".bin2hex(random_bytes(4)).'@example.com',
                        'image' => "https://i.pravatar.cc/150?u=customer_{$index}",
                        'phone' => '+1-555-'.str_pad((string) mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                        'company' => 'Company '.(($index % 5000) + 1),
                        'address' => (($index % 9999) + 1).' Market Street',
                        'status' => $index % 3 === 0 ? 'inactive' : 'active',
                        'notes' => "Auto-generated sample customer #{$index}",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                Customer::insert($records);
            }
        }

        $totalElapsed = round(microtime(true) - $startTotal, 2);
        $this->command->info("🎉 Completed! {$targetCount} records with images successfully inserted in {$totalElapsed}s.");
    }
}
