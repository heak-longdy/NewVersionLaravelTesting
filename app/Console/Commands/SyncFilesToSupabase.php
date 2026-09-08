<?php

namespace App\Console\Commands;

use App\Models\FileItem;
use App\Services\SupabaseStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncFilesToSupabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supabase:sync-files {--force : Overwrite existing objects in Supabase}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync local public files to Supabase Storage and update database records to disk=supabase';

    /**
     * Execute the console command.
     */
    public function handle(SupabaseStorageService $supabaseStorage): int
    {
        if (! $supabaseStorage->isConfigured()) {
            $this->error('Supabase Storage is not configured. Please ensure SUPABASE_URL and SUPABASE_SECRET_KEY are set.');

            return self::FAILURE;
        }

        $this->info('Checking Supabase Storage bucket connection...');
        $bucket = $supabaseStorage->getDefaultBucket();

        if (! $supabaseStorage->ensureBucketExists($bucket)) {
            $this->warn("Bucket '{$bucket}' could not be automatically verified. Will proceed with upload attempts.");
        } else {
            $this->info("Bucket '{$bucket}' is ready.");
        }

        $files = FileItem::withTrashed()->where('disk', '!=', 'supabase')->get();

        if ($files->isEmpty()) {
            $this->info('No files found with disk != supabase. Everything is already synced!');

            return self::SUCCESS;
        }

        $this->info("Found {$files->count()} file(s) to sync to Supabase Storage.");

        $synced = 0;
        $failed = 0;

        foreach ($files as $fileItem) {
            $this->line("Processing: {$fileItem->name} ({$fileItem->original_name})...");

            // Find physical file on disk
            $candidates = [
                public_path('storage/'.$fileItem->file_path),
                base_path('storage/app/public/'.$fileItem->file_path),
                storage_path('app/public/'.$fileItem->file_path),
            ];

            $localPath = null;
            foreach ($candidates as $candidate) {
                if (file_exists($candidate) && ! is_dir($candidate)) {
                    $localPath = $candidate;
                    break;
                }
            }

            if (! $localPath) {
                $this->warn("  [SKIP] Local file not found on disk: {$fileItem->file_path}");
                $failed++;

                continue;
            }

            try {
                $supabaseStorage->upload($localPath, $fileItem->file_path, $bucket);
                $fileItem->disk = 'supabase';
                $fileItem->saveQuietly();

                $this->info("  [OK] Uploaded -> {$supabaseStorage->getPublicUrl($fileItem->file_path, $bucket)}");
                $synced++;
            } catch (\Throwable $e) {
                $this->error("  [ERROR] Failed to upload: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Sync completed: {$synced} synced, {$failed} skipped/failed.");

        return self::SUCCESS;
    }
}
