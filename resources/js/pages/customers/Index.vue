<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Mail, MoreHorizontal, Pencil, Phone, Plus, Search, Trash2, Users, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { create, destroy, edit, index } from '@/actions/App/Http/Controllers/CustomerController';
import Heading from '@/components/Heading.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useInitials } from '@/composables/useInitials';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import type { Customer, CustomerFilters, PaginatedData } from '@/types';

const props = defineProps<{
    customers: PaginatedData<Customer>;
    filters: CustomerFilters;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Setting',
                href: '/customers',
            },
            {
                title: 'Customers',
                href: index.url(),
            },
        ],
    },
});

const { getInitials } = useInitials();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const isSearching = ref(false);

let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    isSearching.value = true;
    router.get(
        index.url(),
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
            onFinish: () => {
                isSearching.value = false;
            },
        },
    );
};

const clearSearch = () => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
    search.value = '';
    applyFilters();
};

watch(search, (newVal, oldVal) => {
    if (newVal === oldVal) return;
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
    searchDebounceTimer = setTimeout(() => {
        applyFilters();
    }, 500);
});

const setStatus = (newStatus: string) => {
    status.value = newStatus;
    applyFilters();
};

const formatNumber = (num?: number | null): string => {
    if (num == null) return '0';
    return Number(num).toLocaleString();
};

// Delete dialog state
const customerToDelete = ref<Customer | null>(null);
const isDeleting = ref(false);

const confirmDelete = (customer: Customer) => {
    customerToDelete.value = customer;
};

const deleteCustomer = () => {
    if (!customerToDelete.value) return;

    isDeleting.value = true;
    router.delete(destroy.url(customerToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            customerToDelete.value = null;
        },
    });
};

const formatDate = (dateStr: string): string => {
    return new Date(dateStr).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Customers" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <Heading
                title="Customers"
                description="Manage customer accounts, contact details, and activity statuses."
            />
            <Button as-child class="shrink-0">
                <Link :href="create.url()">
                    <Plus class="size-4 mr-1.5" />
                    Add Customer
                </Link>
            </Button>
        </div>

        <!-- Filters & Search Bar -->
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative flex-1 max-w-md">
                <Spinner
                    v-if="isSearching"
                    class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground"
                />
                <Search
                    v-else
                    class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by name, email, company, or phone..."
                    class="pl-9 pr-9"
                />
                <button
                    v-if="search"
                    type="button"
                    @click="clearSearch"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                >
                    <X class="size-4" />
                    <span class="sr-only">Clear search</span>
                </button>
            </div>

            <!-- Status filter buttons -->
            <div class="flex items-center gap-1.5 rounded-lg border bg-muted/40 p-1 self-start md:self-auto">
                <button
                    type="button"
                    @click="setStatus('')"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors"
                    :class="status === '' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'"
                >
                    All ({{ formatNumber(customers.total) }})
                </button>
                <button
                    type="button"
                    @click="setStatus('active')"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors"
                    :class="status === 'active' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'"
                >
                    Active
                </button>
                <button
                    type="button"
                    @click="setStatus('inactive')"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors"
                    :class="status === 'inactive' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'"
                >
                    Inactive
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="rounded-xl border bg-card shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[280px]">Customer</TableHead>
                            <TableHead>Contact</TableHead>
                            <TableHead>Company</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Created</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="customers.data.length > 0">
                            <TableRow v-for="customer in customers.data" :key="customer.id">
                                <!-- Customer Avatar, Name & Email -->
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <Avatar class="size-9 rounded-full overflow-hidden shrink-0 border border-border">
                                            <AvatarImage
                                                v-if="customer.image"
                                                :src="customer.image"
                                                :alt="customer.name"
                                                class="object-cover w-full h-full"
                                            />
                                            <AvatarFallback class="text-xs font-semibold bg-muted text-foreground">
                                                {{ getInitials(customer.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-medium text-foreground truncate">
                                                {{ customer.name }}
                                            </span>
                                            <span class="text-xs text-muted-foreground flex items-center gap-1 mt-0.5 truncate">
                                                <Mail class="size-3 shrink-0" />
                                                {{ customer.email }}
                                            </span>
                                        </div>
                                    </div>
                                </TableCell>

                                <!-- Phone -->
                                <TableCell>
                                    <span v-if="customer.phone" class="text-sm flex items-center gap-1.5 text-muted-foreground">
                                        <Phone class="size-3.5" />
                                        {{ customer.phone }}
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground/60">—</span>
                                </TableCell>

                                <!-- Company -->
                                <TableCell>
                                    <span v-if="customer.company" class="text-sm flex items-center gap-1.5 text-foreground">
                                        <Building2 class="size-3.5 text-muted-foreground" />
                                        {{ customer.company }}
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground/60">—</span>
                                </TableCell>

                                <!-- Status Badge -->
                                <TableCell>
                                    <Badge
                                        :variant="customer.status === 'active' ? 'default' : 'secondary'"
                                        class="capitalize"
                                    >
                                        {{ customer.status }}
                                    </Badge>
                                </TableCell>

                                <!-- Created At -->
                                <TableCell class="text-sm text-muted-foreground whitespace-nowrap">
                                    {{ formatDate(customer.created_at) }}
                                </TableCell>

                                <!-- Row Actions -->
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="size-8">
                                                <MoreHorizontal class="size-4" />
                                                <span class="sr-only">Open menu</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link :href="edit.url(customer.id)" class="cursor-pointer">
                                                    <Pencil class="size-4 mr-2" />
                                                    Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                @click="confirmDelete(customer)"
                                                class="text-destructive focus:text-destructive cursor-pointer"
                                            >
                                                <Trash2 class="size-4 mr-2" />
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </template>

                        <!-- Empty State -->
                        <TableRow v-else>
                            <TableCell colspan="6" class="h-64 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="rounded-full bg-muted p-4">
                                        <Users class="size-8 text-muted-foreground" />
                                    </div>
                                    <div class="space-y-1">
                                        <p class="font-medium text-foreground">No customers found</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ search || status ? 'Try adjusting your search or filter options.' : 'Get started by creating your first customer.' }}
                                        </p>
                                    </div>
                                    <Button v-if="!search && !status" as-child size="sm" class="mt-2">
                                        <Link :href="create.url()">
                                            <Plus class="size-4 mr-1.5" />
                                            Add Customer
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination Bar -->
            <div
                v-if="customers.total > 0"
                class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t px-6 py-4"
            >
                <div class="text-sm text-muted-foreground">
                    Showing <span class="font-medium text-foreground">{{ formatNumber(customers.from) }}</span> to
                    <span class="font-medium text-foreground">{{ formatNumber(customers.to) }}</span> of
                    <span class="font-medium text-foreground">{{ formatNumber(customers.total) }}</span> customers
                </div>

                <!-- Page Links -->
                <div v-if="customers.links.length > 3" class="flex items-center gap-1">
                    <template v-for="(link, idx) in customers.links" :key="idx">
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            as-child
                            class="min-w-[36px]"
                        >
                            <Link :href="link.url" preserve-scroll v-html="link.label" />
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            disabled
                            class="min-w-[36px] opacity-40 cursor-not-allowed"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Dialog :open="!!customerToDelete" @update:open="if (!$event) customerToDelete = null;">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete Customer</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete
                    <strong class="font-semibold text-foreground">{{ customerToDelete?.name }}</strong>?
                    This action will permanently remove the customer record and cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="customerToDelete = null" :disabled="isDeleting">
                    Cancel
                </Button>
                <Button variant="destructive" @click="deleteCustomer" :disabled="isDeleting">
                    <Spinner v-if="isDeleting" />
                    <span v-if="isDeleting">Deleting...</span>
                    <span v-else>Delete Customer</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
