<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $page = (int) $request->input('page', 1);
        $perPage = 10;
        $columns = [
            'id',
            'name',
            'email',
            'image',
            'phone',
            'company',
            'status',
            'created_at',
        ];

        $query = Customer::query()
            ->select($columns)
            ->search($search)
            ->status($status);

        if (filled($search)) {
            // High-speed single query search pagination avoiding expensive count scans
            $items = (clone $query)->forPage($page, $perPage + 1)->get();
            $hasMore = $items->count() > $perPage;
            $displayItems = $hasMore ? $items->slice(0, $perPage) : $items;
            $total = $hasMore ? ($page * $perPage) + 1 : (($page - 1) * $perPage) + $items->count();

            $customers = (new LengthAwarePaginator(
                $displayItems,
                $total,
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            ))->withQueryString();
        } else {
            $total = $this->getCustomerCount($status);
            $customers = $query
                ->latest('id')
                ->paginate($perPage, $columns, 'page', $page, $total)
                ->withQueryString();
        }

        return Inertia::render('customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
            ],
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): Response
    {
        return Inertia::render('customers/Create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('customers', 'public');
            $data['image'] = '/storage/'.$path;
        }

        Customer::create($data);
        $this->invalidateCustomerCountCache();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Customer created successfully.'),
        ]);

        return to_route('customers.index');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): RedirectResponse
    {
        return to_route('customers.edit', $customer);
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): Response
    {
        return Inertia::render('customers/Edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($customer->image && str_starts_with($customer->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $customer->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('customers', 'public');
            $data['image'] = '/storage/'.$path;
        } elseif ($request->boolean('remove_image')) {
            if ($customer->image && str_starts_with($customer->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $customer->image);
                Storage::disk('public')->delete($oldPath);
            }
            $data['image'] = null;
        }

        $statusChanged = isset($data['status']) && $data['status'] !== $customer->status;
        $customer->update($data);

        if ($statusChanged) {
            $this->invalidateCustomerCountCache();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Customer updated successfully.'),
        ]);

        return to_route('customers.index');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->image && str_starts_with($customer->image, '/storage/')) {
            $path = str_replace('/storage/', '', $customer->image);
            Storage::disk('public')->delete($path);
        }

        $customer->delete();
        $this->invalidateCustomerCountCache();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Customer deleted successfully.'),
        ]);

        return to_route('customers.index');
    }

    /**
     * Get the estimated or cached count of customers.
     */
    protected function getCustomerCount(?string $status): int
    {
        if (app()->environment('testing')) {
            return Customer::status($status)->count();
        }

        $cacheKey = 'customers_count_'.($status ?: 'all');

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($status) {
            if (DB::getDriverName() === 'pgsql') {
                $stat = DB::selectOne("
                    SELECT 
                        c.reltuples::bigint AS total,
                        s.most_common_vals::text AS vals,
                        s.most_common_freqs::text AS freqs
                    FROM pg_class c
                    LEFT JOIN pg_stats s ON s.tablename = c.relname AND s.attname = 'status'
                    WHERE c.relname = 'customers'
                ");

                if ($stat && $stat->total > 0) {
                    if (empty($status)) {
                        return (int) $stat->total;
                    }

                    if (! empty($stat->vals) && ! empty($stat->freqs)) {
                        $vals = explode(',', trim($stat->vals, '{}'));
                        $freqs = explode(',', trim($stat->freqs, '{}'));
                        foreach ($vals as $idx => $val) {
                            if ($val === $status && isset($freqs[$idx])) {
                                return (int) round($stat->total * (float) $freqs[$idx]);
                            }
                        }
                    }
                }
            }

            return Customer::status($status)->count();
        });
    }

    /**
     * Invalidate customer counts from cache.
     */
    protected function invalidateCustomerCountCache(): void
    {
        Cache::forget('customers_count_all');
        Cache::forget('customers_count_active');
        Cache::forget('customers_count_inactive');
    }
}
