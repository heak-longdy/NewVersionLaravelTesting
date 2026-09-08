<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupabaseStorageService
{
    protected ?string $url;

    protected ?string $key;

    protected string $defaultBucket;

    public function __construct()
    {
        $this->url = rtrim((string) (config('services.supabase.url') ?: env('SUPABASE_URL', env('NEXT_PUBLIC_SUPABASE_URL', ''))), '/');
        $this->key = (string) (config('services.supabase.secret_key') ?: env('SUPABASE_SECRET_KEY', env('SUPABASE_PUBLISHABLE_KEY', '')));
        $this->defaultBucket = (string) (config('services.supabase.bucket') ?: 'file-manager');
    }

    /**
     * Check if Supabase Storage credentials are configured.
     */
    public function isConfigured(): bool
    {
        return filled($this->url) && filled($this->key);
    }

    /**
     * Get the default bucket name.
     */
    public function getDefaultBucket(): string
    {
        return $this->defaultBucket;
    }

    /**
     * Upload an UploadedFile or raw binary content to Supabase Storage.
     *
     * @param  UploadedFile|string  $file  UploadedFile instance or local file path
     * @param  string  $path  Destination path inside the bucket (e.g. "unique_name.jpg")
     * @param  string|null  $bucket  Bucket name (defaults to 'file-manager')
     * @return string Stored object path
     *
     * @throws Exception
     */
    public function upload(UploadedFile|string $file, string $path, ?string $bucket = null): string
    {
        $bucket = $bucket ?: $this->defaultBucket;
        $path = ltrim($path, '/');

        if (! $this->isConfigured()) {
            throw new Exception('Supabase Storage is not configured. Please set SUPABASE_URL and SUPABASE_SECRET_KEY in your environment.');
        }

        if ($file instanceof UploadedFile) {
            $contents = file_get_contents($file->getRealPath());
            $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        } elseif (is_file($file)) {
            $contents = file_get_contents($file);
            $mimeType = mime_content_type($file) ?: 'application/octet-stream';
        } else {
            $contents = $file;
            $mimeType = 'application/octet-stream';
        }

        if ($contents === false) {
            throw new Exception('Failed to read file contents for Supabase upload.');
        }

        // Endpoint: POST /storage/v1/object/{bucket}/{wildcard}
        $endpoint = "{$this->url}/storage/v1/object/{$bucket}/{$path}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->key}",
            'apikey' => $this->key,
            'Content-Type' => $mimeType,
            'x-upsert' => 'true',
        ])->withBody($contents, $mimeType)->post($endpoint);

        if ($response->failed()) {
            $errorMsg = $response->json('message') ?? $response->json('error') ?? $response->body();
            Log::error('Supabase Storage upload failed', [
                'status' => $response->status(),
                'error' => $errorMsg,
                'path' => $path,
            ]);

            throw new Exception("Supabase Storage upload failed ({$response->status()}): {$errorMsg}");
        }

        return $path;
    }

    /**
     * Delete file(s) from Supabase Storage.
     *
     * @param  string|array<int, string>  $paths  Object path(s) to remove
     * @param  string|null  $bucket  Bucket name
     */
    public function delete(string|array $paths, ?string $bucket = null): bool
    {
        $bucket = $bucket ?: $this->defaultBucket;

        if (! $this->isConfigured()) {
            return false;
        }

        $prefixes = array_map(fn ($p) => ltrim((string) $p, '/'), (array) $paths);

        $endpoint = "{$this->url}/storage/v1/object/{$bucket}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->key}",
            'apikey' => $this->key,
            'Content-Type' => 'application/json',
        ])->delete($endpoint, [
            'prefixes' => $prefixes,
        ]);

        return $response->successful();
    }

    /**
     * Get the public URL for a file stored in a public bucket.
     */
    public function getPublicUrl(string $path, ?string $bucket = null): string
    {
        $bucket = $bucket ?: $this->defaultBucket;
        $path = ltrim($path, '/');

        return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
    }

    /**
     * Stream a file download from Supabase Storage with custom filename.
     */
    public function download(string $path, string $filename, ?string $bucket = null): StreamedResponse
    {
        $bucket = $bucket ?: $this->defaultBucket;
        $publicUrl = $this->getPublicUrl($path, $bucket);

        return response()->streamDownload(function () use ($publicUrl): void {
            $stream = fopen($publicUrl, 'r');
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, $filename);
    }

    /**
     * Ensure public bucket exists in Supabase.
     */
    public function ensureBucketExists(?string $bucket = null): bool
    {
        $bucket = $bucket ?: $this->defaultBucket;

        if (! $this->isConfigured()) {
            return false;
        }

        // Check if bucket exists
        $check = Http::withHeaders([
            'Authorization' => "Bearer {$this->key}",
            'apikey' => $this->key,
        ])->get("{$this->url}/storage/v1/bucket/{$bucket}");

        if ($check->successful()) {
            return true;
        }

        // Create public bucket if missing
        $create = Http::withHeaders([
            'Authorization' => "Bearer {$this->key}",
            'apikey' => $this->key,
            'Content-Type' => 'application/json',
        ])->post("{$this->url}/storage/v1/bucket", [
            'id' => $bucket,
            'name' => $bucket,
            'public' => true,
        ]);

        return $create->successful();
    }
}
