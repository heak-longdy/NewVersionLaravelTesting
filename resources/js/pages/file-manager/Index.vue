<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Check,
    Files,
    FolderKanban,
    Grid,
    HardDrive,
    LayoutGrid,
    List,
    Plus,
    RotateCcw,
    Search,
    Trash,
    Trash2,
    UploadCloud,
    X,
} from '@lucide/vue';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
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
import FileEditModal from './components/FileEditModal.vue';
import FileGridCard from './components/FileGridCard.vue';
import FilePreviewModal from './components/FilePreviewModal.vue';
import FileStatsBar from './components/FileStatsBar.vue';
import FileTableView from './components/FileTableView.vue';
import FileUploadModal from './components/FileUploadModal.vue';
import { dashboard } from '@/routes';
import type {
    FileCategory,
    FileItem,
    FileManagerFilters,
    FileManagerStats,
    PaginatedData,
} from '@/types';

const props = defineProps<{
    files: PaginatedData<FileItem>;
    filters: FileManagerFilters;
    stats: FileManagerStats;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'File Manager',
                href: '/file-manager',
            },
        ],
    },
});

// View mode: grid vs table (persisted in localStorage)
const viewMode = ref<'grid' | 'table'>(
    (localStorage.getItem('fm_view_mode') as 'grid' | 'table') || 'grid',
);

const setViewMode = (mode: 'grid' | 'table') => {
    viewMode.value = mode;
    localStorage.setItem('fm_view_mode', mode);
};

// Filter states
const tab = ref<'all' | 'trash'>(props.filters.tab || 'all');
const search = ref(props.filters.search ?? '');
const category = ref<FileCategory>(props.filters.category || 'all');
const sort = ref(props.filters.sort || 'date_desc');
const isSearching = ref(false);

let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = (customTab?: 'all' | 'trash') => {
    isSearching.value = true;
    router.get(
        '/file-manager',
        {
            tab: customTab ?? tab.value,
            search: search.value || undefined,
            category: category.value !== 'all' ? category.value : undefined,
            sort: sort.value !== 'date_desc' ? sort.value : undefined,
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

const switchTab = (newTab: 'all' | 'trash') => {
    tab.value = newTab;
    applyFilters(newTab);
};

const setCategory = (newCategory: FileCategory) => {
    category.value = newCategory;
    applyFilters();
};

const setSort = (newSort: string) => {
    sort.value = newSort as any;
    applyFilters();
};

const clearSearch = () => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    search.value = '';
    applyFilters();
};

watch(search, (newVal, oldVal) => {
    if (newVal === oldVal) return;
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

// Modal states
const isUploadOpen = ref(false);
const isEditOpen = ref(false);
const isPreviewOpen = ref(false);
const activeFile = ref<FileItem | null>(null);

// Confirmation dialogs
const fileToTrash = ref<FileItem | null>(null);
const fileToRestore = ref<FileItem | null>(null);
const fileToForceDelete = ref<FileItem | null>(null);
const isEmptyTrashDialogOpen = ref(false);
const isActionLoading = ref(false);

const openPreview = (file: FileItem) => {
    activeFile.value = file;
    isPreviewOpen.value = true;
};

const openEdit = (file: FileItem) => {
    activeFile.value = file;
    isEditOpen.value = true;
};

const confirmMoveToTrash = (file: FileItem) => {
    fileToTrash.value = file;
};

const submitMoveToTrash = () => {
    if (!fileToTrash.value) return;
    isActionLoading.value = true;
    router.delete(`/file-manager/${fileToTrash.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            isActionLoading.value = false;
            fileToTrash.value = null;
        },
    });
};

const confirmRestore = (file: FileItem) => {
    fileToRestore.value = file;
};

const submitRestore = () => {
    if (!fileToRestore.value) return;
    isActionLoading.value = true;
    router.post(`/file-manager/${fileToRestore.value.id}/restore`, {}, {
        preserveScroll: true,
        onFinish: () => {
            isActionLoading.value = false;
            fileToRestore.value = null;
        },
    });
};

const confirmForceDelete = (file: FileItem) => {
    fileToForceDelete.value = file;
};

const submitForceDelete = () => {
    if (!fileToForceDelete.value) return;
    isActionLoading.value = true;
    router.delete(`/file-manager/${fileToForceDelete.value.id}/force-delete`, {
        preserveScroll: true,
        onFinish: () => {
            isActionLoading.value = false;
            fileToForceDelete.value = null;
        },
    });
};

const submitEmptyTrash = () => {
    isActionLoading.value = true;
    router.delete('/file-manager/trash/empty', {
        preserveScroll: true,
        onFinish: () => {
            isActionLoading.value = false;
            isEmptyTrashDialogOpen.value = false;
        },
    });
};

const categories: { label: string; value: FileCategory }[] = [
    { label: 'All Files', value: 'all' },
    { label: 'Images', value: 'image' },
    { label: 'Documents', value: 'document' },
    { label: 'Videos', value: 'video' },
    { label: 'Audio', value: 'audio' },
    { label: 'Archives', value: 'archive' },
    { label: 'Code / Data', value: 'code' },
    { label: 'Other Formats', value: 'other' },
];

const sortOptions = [
    { label: 'Newest First', value: 'date_desc' },
    { label: 'Oldest First', value: 'date_asc' },
    { label: 'Name (A to Z)', value: 'name_asc' },
    { label: 'Name (Z to A)', value: 'name_desc' },
    { label: 'Size (Largest)', value: 'size_desc' },
    { label: 'Size (Smallest)', value: 'size_asc' },
];
</script>

<template>
    <div class="flex flex-col gap-6 p-4 md:p-6 max-w-7xl mx-auto w-full">
        <Head title="File Manager" />

        <!-- Header Bar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <Heading
                title="File Manager"
                description="Upload any file format, organize, edit, and recover deleted files from the recycle bin."
            />
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <Button
                    v-if="tab === 'trash' && stats.trash_count > 0"
                    variant="destructive"
                    class="shrink-0 shadow-xs"
                    @click="isEmptyTrashDialogOpen = true"
                >
                    <Trash2 class="size-4 mr-1.5" />
                    Empty Recycle Bin
                </Button>

                <Button
                    class="shrink-0 shadow-xs"
                    @click="isUploadOpen = true"
                >
                    <UploadCloud class="size-4 mr-1.5" />
                    Upload Files
                </Button>
            </div>
        </div>

        <!-- Metric Statistics Bar -->
        <FileStatsBar
            :stats="stats"
            :active-tab="tab"
            @switch-tab="switchTab"
        />

        <!-- Tab Selector: All Files vs Recycle Bin -->
        <div class="flex items-center justify-between border-b border-sidebar-border/80">
            <div class="flex items-center gap-1 -mb-px">
                <button
                    type="button"
                    class="flex items-center gap-2 py-3 px-4 text-sm font-semibold border-b-2 transition-colors cursor-pointer"
                    :class="[
                        tab === 'all'
                            ? 'border-primary text-primary'
                            : 'border-transparent text-muted-foreground hover:text-foreground hover:border-sidebar-border',
                    ]"
                    @click="switchTab('all')"
                >
                    <Files class="size-4" />
                    All Files
                    <Badge variant="secondary" class="ml-1 text-[11px] font-mono">
                        {{ stats.total_files }}
                    </Badge>
                </button>

                <button
                    type="button"
                    class="flex items-center gap-2 py-3 px-4 text-sm font-semibold border-b-2 transition-colors cursor-pointer"
                    :class="[
                        tab === 'trash'
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400'
                            : 'border-transparent text-muted-foreground hover:text-foreground hover:border-sidebar-border',
                    ]"
                    @click="switchTab('trash')"
                >
                    <Trash2 class="size-4" />
                    Recycle Bin (Trash)
                    <Badge
                        variant="secondary"
                        class="ml-1 text-[11px] font-mono"
                        :class="{ 'bg-amber-500/20 text-amber-700 dark:text-amber-300': stats.trash_count > 0 }"
                    >
                        {{ stats.trash_count }}
                    </Badge>
                </button>
            </div>

            <!-- View Mode Switcher -->
            <div class="hidden sm:flex items-center gap-1 p-1 bg-muted/60 rounded-xl border border-sidebar-border/60 mb-2">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-7 rounded-lg"
                    :class="{ 'bg-background shadow-xs text-foreground': viewMode === 'grid' }"
                    @click="setViewMode('grid')"
                    title="Grid View"
                >
                    <LayoutGrid class="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="size-7 rounded-lg"
                    :class="{ 'bg-background shadow-xs text-foreground': viewMode === 'table' }"
                    @click="setViewMode('table')"
                    title="Table View"
                >
                    <List class="size-3.5" />
                </Button>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <!-- Search input -->
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
                    placeholder="Search by name, original file, or extension..."
                    class="pl-9 pr-8 h-9 text-xs"
                />
                <button
                    v-if="search"
                    type="button"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                    @click="clearSearch"
                >
                    <X class="size-3.5" />
                </button>
            </div>

            <!-- Category Pills & Sort -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Sort Dropdown -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm" class="h-9 text-xs">
                            Sort: {{ sortOptions.find((o) => o.value === sort)?.label }}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem
                            v-for="opt in sortOptions"
                            :key="opt.value"
                            @click="setSort(opt.value)"
                            class="flex items-center justify-between text-xs"
                        >
                            {{ opt.label }}
                            <Check v-if="sort === opt.value" class="size-3.5 text-primary" />
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <!-- Category Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
            <button
                v-for="cat in categories"
                :key="cat.value"
                type="button"
                class="px-3 py-1.5 rounded-lg border font-medium transition-colors whitespace-nowrap cursor-pointer"
                :class="[
                    category === cat.value
                        ? 'bg-primary text-primary-foreground border-primary shadow-2xs'
                        : 'bg-card text-muted-foreground border-sidebar-border hover:text-foreground hover:bg-muted/50',
                ]"
                @click="setCategory(cat.value)"
            >
                {{ cat.label }}
            </button>
        </div>

        <!-- Recycle Bin Notice Banner -->
        <div
            v-if="tab === 'trash'"
            class="p-3.5 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-800 dark:text-amber-300 text-xs flex items-center justify-between gap-3"
        >
            <div class="flex items-center gap-2.5">
                <AlertTriangle class="size-4 shrink-0 text-amber-600 dark:text-amber-400" />
                <span>
                    Items in the Recycle Bin will be preserved until you restore them or permanently delete them.
                </span>
            </div>
            <Button
                v-if="stats.trash_count > 0"
                variant="outline"
                size="sm"
                class="h-7 text-xs border-amber-500/40 text-amber-800 dark:text-amber-300 hover:bg-amber-500/20"
                @click="isEmptyTrashDialogOpen = true"
            >
                Empty Trash
            </Button>
        </div>

        <!-- Main Content Area: Files Listing -->
        <div>
            <!-- Empty State -->
            <div
                v-if="files.data.length === 0"
                class="flex flex-col items-center justify-center p-12 text-center rounded-2xl border border-dashed border-sidebar-border bg-card/40 my-4"
            >
                <div class="size-16 rounded-2xl bg-muted flex items-center justify-center text-muted-foreground mb-4 shadow-xs">
                    <Trash2 v-if="tab === 'trash'" class="size-8 text-amber-500" />
                    <Files v-else class="size-8 text-primary" />
                </div>
                <h3 class="font-bold text-lg text-foreground">
                    {{ tab === 'trash' ? 'Recycle Bin is Empty' : 'No files found' }}
                </h3>
                <p class="text-xs text-muted-foreground max-w-sm mt-1 mb-5">
                    <template v-if="tab === 'trash'">
                        Any files you delete will appear here so you can restore them whenever needed.
                    </template>
                    <template v-else-if="search || category !== 'all'">
                        No files matched your current filter criteria. Try clearing search or selecting another category.
                    </template>
                    <template v-else>
                        Upload your first file to get started. You can upload any file extension up to 100MB.
                    </template>
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="search || category !== 'all'"
                        variant="outline"
                        size="sm"
                        @click="category = 'all'; clearSearch()"
                    >
                        Clear Filters
                    </Button>
                    <Button
                        v-if="tab === 'all'"
                        size="sm"
                        @click="isUploadOpen = true"
                    >
                        <UploadCloud class="size-4 mr-1.5" />
                        Upload Files
                    </Button>
                </div>
            </div>

            <!-- Grid View -->
            <div
                v-else-if="viewMode === 'grid'"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
            >
                <FileGridCard
                    v-for="file in files.data"
                    :key="file.id"
                    :file="file"
                    :is-trash-view="tab === 'trash'"
                    @preview="openPreview"
                    @edit="openEdit"
                    @trash="confirmMoveToTrash"
                    @restore="confirmRestore"
                    @force-delete="confirmForceDelete"
                />
            </div>

            <!-- Table View -->
            <div v-else>
                <FileTableView
                    :files="files.data"
                    :is-trash-view="tab === 'trash'"
                    @preview="openPreview"
                    @edit="openEdit"
                    @trash="confirmMoveToTrash"
                    @restore="confirmRestore"
                    @force-delete="confirmForceDelete"
                />
            </div>

            <!-- Pagination Controls -->
            <div
                v-if="files.links && files.links.length > 3"
                class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-4 border-t border-sidebar-border/70"
            >
                <p class="text-xs text-muted-foreground">
                    Showing <span class="font-medium text-foreground">{{ files.from || 0 }}</span> to
                    <span class="font-medium text-foreground">{{ files.to || 0 }}</span> of
                    <span class="font-medium text-foreground">{{ files.total }}</span> items
                </p>

                <div class="flex items-center gap-1">
                    <template v-for="(link, idx) in files.links" :key="idx">
                        <Button
                            v-if="link.url"
                            as-child
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            class="h-8 px-3 text-xs"
                        >
                            <Link :href="link.url" preserve-scroll v-html="link.label" />
                        </Button>
                        <span
                            v-else
                            class="px-2.5 py-1 text-xs text-muted-foreground/60 select-none"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <FileUploadModal
            v-model:open="isUploadOpen"
            @uploaded="applyFilters"
        />

        <FileEditModal
            v-model:open="isEditOpen"
            :file="activeFile"
            @updated="applyFilters"
        />

        <FilePreviewModal
            v-model:open="isPreviewOpen"
            :file="activeFile"
        />

        <!-- Confirm Move to Trash Dialog -->
        <Dialog :open="!!fileToTrash" @update:open="(val) => !val && (fileToTrash = null)">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-amber-600">
                        <Trash2 class="size-5" />
                        Move to Recycle Bin?
                    </DialogTitle>
                    <DialogDescription>
                        "{{ fileToTrash?.name }}" will be moved to the Recycle Bin. You can restore it anytime.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex sm:justify-end gap-2">
                    <Button variant="outline" @click="fileToTrash = null" :disabled="isActionLoading">
                        Cancel
                    </Button>
                    <Button variant="default" class="bg-amber-600 hover:bg-amber-700" @click="submitMoveToTrash" :disabled="isActionLoading">
                        <Spinner v-if="isActionLoading" class="size-4 mr-1.5" />
                        Move to Trash
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Confirm Restore Dialog -->
        <Dialog :open="!!fileToRestore" @update:open="(val) => !val && (fileToRestore = null)">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-emerald-600">
                        <RotateCcw class="size-5" />
                        Restore File?
                    </DialogTitle>
                    <DialogDescription>
                        "{{ fileToRestore?.name }}" will be restored to your active files.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex sm:justify-end gap-2">
                    <Button variant="outline" @click="fileToRestore = null" :disabled="isActionLoading">
                        Cancel
                    </Button>
                    <Button variant="default" class="bg-emerald-600 hover:bg-emerald-700" @click="submitRestore" :disabled="isActionLoading">
                        <Spinner v-if="isActionLoading" class="size-4 mr-1.5" />
                        Restore
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Confirm Force Delete Dialog -->
        <Dialog :open="!!fileToForceDelete" @update:open="(val) => !val && (fileToForceDelete = null)">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <AlertTriangle class="size-5" />
                        Permanently Delete File?
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to permanently delete "{{ fileToForceDelete?.name }}"? This action cannot be undone and will erase the file from storage.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex sm:justify-end gap-2">
                    <Button variant="outline" @click="fileToForceDelete = null" :disabled="isActionLoading">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="submitForceDelete" :disabled="isActionLoading">
                        <Spinner v-if="isActionLoading" class="size-4 mr-1.5" />
                        Delete Permanently
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Confirm Empty Trash Dialog -->
        <Dialog :open="isEmptyTrashDialogOpen" @update:open="(val) => isEmptyTrashDialogOpen = val">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <AlertTriangle class="size-5" />
                        Empty Recycle Bin?
                    </DialogTitle>
                    <DialogDescription>
                        All {{ stats.trash_count }} items in the Recycle Bin will be permanently removed from storage. This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex sm:justify-end gap-2">
                    <Button variant="outline" @click="isEmptyTrashDialogOpen = false" :disabled="isActionLoading">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="submitEmptyTrash" :disabled="isActionLoading">
                        <Spinner v-if="isActionLoading" class="size-4 mr-1.5" />
                        Empty Entire Bin
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
