<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Mail, MoreHorizontal, Pencil, Plus, Search, Trash2, UserCheck, Users, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { useInitials } from '@/composables/useInitials';
import { dashboard } from '@/routes';
import type { PaginatedData, UserFilters, UserItem } from '@/types';

const props = defineProps<{
    users: PaginatedData<UserItem>;
    filters: UserFilters;
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
                href: '/users',
            },
            {
                title: 'Users',
                href: '/users',
            },
        ],
    },
});

const page = usePage();
const currentAuthUserId = computed(() => (page.props.auth as any)?.user?.id);
const { getInitials } = useInitials();

const search = ref(props.filters.search ?? '');
const isSearching = ref(false);

let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    isSearching.value = true;
    router.get(
        '/users',
        {
            search: search.value || undefined,
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

// Delete dialog state
const userToDelete = ref<UserItem | null>(null);
const isDeleting = ref(false);

const confirmDelete = (user: UserItem) => {
    userToDelete.value = user;
};

const deleteUser = () => {
    if (!userToDelete.value) return;

    isDeleting.value = true;
    router.delete(`/users/${userToDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            userToDelete.value = null;
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
    <Head title="Users - Setting" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading
                title="User Management"
                description="Manage application users, view authentication status, and configure team member access."
            />
            <Button as-child class="shrink-0">
                <Link href="/users/create">
                    <Plus class="mr-1.5 size-4" />
                    Add User
                </Link>
            </Button>
        </div>

        <!-- Search Bar -->
        <div class="flex flex-col items-stretch justify-between gap-4 md:flex-row md:items-center">
            <div class="relative max-w-md flex-1">
                <Spinner
                    v-if="isSearching"
                    class="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2"
                />
                <Search
                    v-else
                    class="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2"
                />
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or email..."
                    class="pr-9 pl-9"
                />
                <button
                    v-if="search"
                    type="button"
                    class="text-muted-foreground hover:text-foreground absolute top-1/2 right-3 -translate-y-1/2"
                    @click="clearSearch"
                >
                    <X class="size-4" />
                </button>
            </div>
            <div class="text-sm text-muted-foreground">
                Showing <span class="font-medium text-foreground">{{ users.data.length }}</span> of <span class="font-medium text-foreground">{{ users.total }}</span> users
            </div>
        </div>

        <!-- Table Card -->
        <div class="rounded-xl border bg-card shadow-xs">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[300px]">User</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Joined Date</TableHead>
                        <TableHead class="w-[80px] text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="users.data.length === 0">
                        <TableCell colspan="5" class="h-48 text-center">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <Users class="text-muted-foreground size-10" />
                                <p class="text-base font-medium">No users found</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ search ? 'Try adjusting your search query.' : 'Get started by creating your first user.' }}
                                </p>
                                <Button v-if="!search" as-child size="sm" class="mt-2">
                                    <Link href="/users/create">
                                        <Plus class="mr-1 size-4" />
                                        Add User
                                    </Link>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <TableRow
                        v-for="user in users.data"
                        :key="user.id"
                        class="group transition-colors hover:bg-muted/50"
                    >
                        <!-- User (Avatar + Name) -->
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <Avatar class="size-9 border shadow-xs">
                                    <AvatarImage
                                        v-if="user.avatar"
                                        :src="user.avatar"
                                        :alt="user.name"
                                    />
                                    <AvatarFallback class="bg-primary/10 text-primary text-xs font-bold">
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1.5 font-medium text-foreground">
                                        {{ user.name }}
                                        <Badge
                                            v-if="user.id === currentAuthUserId"
                                            variant="outline"
                                            class="border-primary/30 text-primary text-[10px] px-1.5 py-0"
                                        >
                                            You
                                        </Badge>
                                    </div>
                                    <span class="text-xs text-muted-foreground">
                                        ID #{{ user.id }}
                                    </span>
                                </div>
                            </div>
                        </TableCell>

                        <!-- Email -->
                        <TableCell>
                            <div class="flex items-center gap-1.5 text-sm text-muted-foreground">
                                <Mail class="size-3.5 shrink-0" />
                                <span>{{ user.email }}</span>
                            </div>
                        </TableCell>

                        <!-- Status -->
                        <TableCell>
                            <Badge
                                v-if="user.email_verified_at"
                                variant="secondary"
                                class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20"
                            >
                                <UserCheck class="mr-1 size-3" />
                                Verified
                            </Badge>
                            <Badge
                                v-else
                                variant="outline"
                                class="text-amber-600 dark:text-amber-400 border-amber-500/30"
                            >
                                Unverified
                            </Badge>
                        </TableCell>

                        <!-- Joined Date -->
                        <TableCell class="text-sm text-muted-foreground">
                            {{ formatDate(user.created_at) }}
                        </TableCell>

                        <!-- Actions -->
                        <TableCell class="text-right">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="size-8"
                                    >
                                        <MoreHorizontal class="size-4" />
                                        <span class="sr-only">Open menu</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-40">
                                    <DropdownMenuItem as-child>
                                        <Link
                                            :href="`/users/${user.id}/edit`"
                                            class="flex cursor-pointer items-center"
                                        >
                                            <Pencil class="mr-2 size-4 text-muted-foreground" />
                                            Edit
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        v-if="user.id !== currentAuthUserId"
                                        class="text-destructive focus:text-destructive flex cursor-pointer items-center"
                                        @click="confirmDelete(user)"
                                    >
                                        <Trash2 class="mr-2 size-4" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div
            v-if="users.links && users.links.length > 3"
            class="flex items-center justify-between border-t pt-4"
        >
            <div class="text-sm text-muted-foreground">
                Showing {{ users.from ?? 0 }} to {{ users.to ?? 0 }} of {{ users.total }} results
            </div>
            <div class="flex items-center gap-1">
                <template v-for="(link, idx) in users.links" :key="idx">
                    <Button
                        v-if="link.url"
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        as-child
                        class="h-8 min-w-8 px-2"
                    >
                        <Link :href="link.url" preserve-scroll>
                            <span v-html="link.label"></span>
                        </Link>
                    </Button>
                    <Button
                        v-else
                        variant="outline"
                        size="sm"
                        disabled
                        class="h-8 min-w-8 px-2 opacity-50"
                    >
                        <span v-html="link.label"></span>
                    </Button>
                </template>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog :open="!!userToDelete" @update:open="(val) => !val && (userToDelete = null)">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Delete User</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <span class="font-medium text-foreground">{{ userToDelete?.name }}</span> ({{ userToDelete?.email }})? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-0">
                    <Button
                        variant="outline"
                        :disabled="isDeleting"
                        @click="userToDelete = null"
                    >
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        :disabled="isDeleting"
                        @click="deleteUser"
                    >
                        <Spinner v-if="isDeleting" class="mr-1.5 size-4" />
                        <span>{{ isDeleting ? 'Deleting...' : 'Delete User' }}</span>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
