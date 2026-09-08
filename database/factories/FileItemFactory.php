<?php

namespace Database\Factories;

use App\Models\FileItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FileItem>
 */
class FileItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<FileItem>
     */
    protected $model = FileItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extensions = ['jpg', 'png', 'pdf', 'zip', 'mp4', 'sql', 'docx', 'xlsx', 'txt', 'exe', 'xyz'];
        $extension = fake()->randomElement($extensions);
        $baseName = fake()->words(2, true);
        $originalName = str_replace(' ', '_', $baseName).'.'.$extension;

        return [
            'user_id' => User::factory(),
            'name' => ucwords($baseName),
            'original_name' => $originalName,
            'file_path' => 'file-manager/'.fake()->uuid().'.'.$extension,
            'disk' => 'public',
            'mime_type' => 'application/octet-stream',
            'extension' => $extension,
            'size' => fake()->numberBetween(1024, 25 * 1048576),
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the file is trashed.
     */
    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * Indicate that the file is an image.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'extension' => 'png',
            'mime_type' => 'image/png',
            'original_name' => 'sample_image.png',
        ]);
    }
}
