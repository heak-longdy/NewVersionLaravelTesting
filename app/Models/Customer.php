<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'image',
        'phone',
        'company',
        'address',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    /**
     * Scope a query to search customers by keyword.
     *
     * @param  Builder<Customer>  $query
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(filled($search), function (Builder $query) use ($search) {
            $search = trim($search);
            $driver = $query->getConnection()->getDriverName();
            $operator = $driver === 'pgsql' ? 'ilike' : 'like';

            $query->where(function (Builder $query) use ($search, $operator) {
                // If numeric or starts with #, search by exact customer ID
                if (is_numeric($search) || preg_match('/^#\d+$/', $search)) {
                    $id = (int) ltrim($search, '#');
                    $query->where('id', $id);

                    return;
                }

                // If international phone search format (starts with +)
                if (str_starts_with($search, '+')) {
                    $query->where('phone', $operator, "{$search}%");

                    return;
                }

                // If email search format
                if (str_contains($search, '@')) {
                    $query->where('email', $operator, "{$search}%");

                    return;
                }

                // General search: prefix search on name, email, company, and phone
                $query->where('name', $operator, "{$search}%")
                    ->orWhere('email', $operator, "{$search}%")
                    ->orWhere('company', $operator, "{$search}%")
                    ->orWhere('phone', $operator, "{$search}%");
            });
        });
    }

    /**
     * Scope a query to filter customers by status.
     *
     * @param  Builder<Customer>  $query
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(filled($status), function (Builder $query) use ($status) {
            $query->where('status', $status);
        });
    }
}
