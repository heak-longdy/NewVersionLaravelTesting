<?php

namespace App\Models;

use Database\Factories\FileItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string $original_name
 * @property string $file_path
 * @property string $disk
 * @property string|null $mime_type
 * @property string|null $extension
 * @property int $size
 * @property string|null $description
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted_size
 * @property-read string $category
 * @property-read string $url
 * @property-read bool $is_image
 * @property-read User|null $user
 */
class FileItem extends Model
{
    /** @use HasFactory<FileItemFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'original_name',
        'file_path',
        'disk',
        'mime_type',
        'extension',
        'size',
        'description',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'formatted_size',
        'category',
        'url',
        'is_image',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * User who uploaded the file.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted file size.
     */
    protected function formattedSize(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $bytes = $this->size;
                if ($bytes >= 1073741824) {
                    return number_format($bytes / 1073741824, 2).' GB';
                }
                if ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 2).' MB';
                }
                if ($bytes >= 1024) {
                    return number_format($bytes / 1024, 1).' KB';
                }

                return $bytes.' B';
            }
        );
    }

    /**
     * Get the categorized file type.
     */
    protected function category(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $ext = strtolower($this->extension ?? '');
                $mime = strtolower($this->mime_type ?? '');

                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico', 'tiff', 'avif']) || str_starts_with($mime, 'image/')) {
                    return 'image';
                }

                if (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv', 'odt', 'ods']) || str_contains($mime, 'document') || str_contains($mime, 'pdf')) {
                    return 'document';
                }

                if (in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'flv']) || str_starts_with($mime, 'video/')) {
                    return 'video';
                }

                if (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'wma']) || str_starts_with($mime, 'audio/')) {
                    return 'audio';
                }

                if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'iso', 'dmg']) || str_contains($mime, 'zip') || str_contains($mime, 'tar') || str_contains($mime, 'compressed')) {
                    return 'archive';
                }

                if (in_array($ext, ['js', 'ts', 'vue', 'jsx', 'tsx', 'php', 'py', 'html', 'css', 'json', 'xml', 'sql', 'sh', 'java', 'c', 'cpp', 'rs', 'go', 'yaml', 'yml', 'md'])) {
                    return 'code';
                }

                return 'other';
            }
        );
    }

    /**
     * Determine if file is an image.
     */
    protected function isImage(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->category === 'image'
        );
    }

    /**
     * Get public or storage URL for preview.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->disk === 'supabase') {
                    $supabaseUrl = rtrim((string) (config('services.supabase.url') ?: env('SUPABASE_URL', env('NEXT_PUBLIC_SUPABASE_URL', ''))), '/');
                    $bucket = config('services.supabase.bucket') ?: 'file-manager';
                    $path = ltrim($this->file_path, '/');

                    return "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$path}";
                }

                if ($this->disk === 'public') {
                    return '/storage/'.ltrim($this->file_path, '/');
                }

                if ($this->disk === 's3') {
                    return Storage::disk('s3')->url($this->file_path);
                }

                return route('file-manager.download', $this->id);
            }
        );
    }

    /**
     * Scope search across name, original name, extension, and description.
     *
     * @param  Builder<FileItem>  $query
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(filled($search), function (Builder $query) use ($search) {
            $search = trim($search);
            $driver = $query->getConnection()->getDriverName();
            $operator = $driver === 'pgsql' ? 'ilike' : 'like';

            $query->where(function (Builder $query) use ($search, $operator) {
                $query->where('name', $operator, "%{$search}%")
                    ->orWhere('original_name', $operator, "%{$search}%")
                    ->orWhere('extension', $operator, "%{$search}%")
                    ->orWhere('description', $operator, "%{$search}%");
            });
        });
    }

    /**
     * Scope filter by category.
     *
     * @param  Builder<FileItem>  $query
     */
    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $query->when(filled($category) && $category !== 'all', function (Builder $query) use ($category) {
            $categories = [
                'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico', 'tiff', 'avif'],
                'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv', 'odt', 'ods'],
                'video' => ['mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'flv'],
                'audio' => ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac', 'wma'],
                'archive' => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'iso', 'dmg'],
                'code' => ['js', 'ts', 'vue', 'jsx', 'tsx', 'php', 'py', 'html', 'css', 'json', 'xml', 'sql', 'sh', 'java', 'c', 'cpp', 'rs', 'go', 'yaml', 'yml', 'md'],
            ];

            if (isset($categories[$category])) {
                $query->whereIn('extension', $categories[$category]);
            } elseif ($category === 'other') {
                $allKnown = array_merge(...array_values($categories));
                $query->whereNotIn('extension', $allKnown)->orWhereNull('extension');
            }
        });
    }
}
