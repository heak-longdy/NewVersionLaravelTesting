<script setup lang="ts">
import { Check, FolderKanban, Image as ImageIcon, Plus, Search, UploadCloud } from '@lucide/vue';
import { onMounted, ref, watch } from 'vue';
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
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import type { FileItem, PaginatedData } from '@/types';

const props = withDefaults(
    defineProps<{
        category?: string;
        title?: string;
    }>(),
    {
        category: 'image',
        title: 'Select from File Manager',
    },
);

const isOpen = defineModel<boolean>('open', { default: false });

const emit = defineEmits<{
    (e: 'select', file: FileItem): void;
}>();

const files = ref<FileItem[]>([]);
const isLoading = ref(false);
const search = ref('');
const selectedFile = ref<FileItem | null>(null);
const fileUploadInputRef = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);

let searchDebounce: ReturnType<typeof setTimeout> | null = null;

const fetchFiles = async () => {
    isLoading.value = true;
    try {
        const params = new URLSearchParams();
        if (props.category) params.append('category', props.category);
        if (search.value) params.append('search', search.value);
        params.append('per_page', '30');

        const response = await fetch(`/file-manager/api/files?${params.toString()}`, {
            headers: {
                Accept: 'application/json',
            },
        });
        if (response.ok) {
            const data: PaginatedData<FileItem> = await response.json();
            files.value = data.data;
        }
    } catch (err) {
        console.error('Failed to fetch files for picker:', err);
    } finally {
        isLoading.value = false;
    }
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            selectedFile.value = null;
            search.value = '';
            fetchFiles();
        }
    },
);

watch(search, () => {
    if (searchDebounce) clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        fetchFiles();
    }, 350);
});

const onDirectUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files || target.files.length === 0) return;

    const file = target.files[0];
    isUploading.value = true;

    try {
        const formData = new FormData();
        formData.append('file', file);

        // Get CSRF token if present
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const response = await fetch('/file-manager', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: formData,
        });

        if (response.ok) {
            const resData = await response.json();
            if (resData.file) {
                // Prepend uploaded file and auto-select it
                files.value.unshift(resData.file);
                selectedFile.value = resData.file;
            } else {
                fetchFiles();
            }
        }
    } catch (err) {
        console.error('Failed to upload file in picker:', err);
    } finally {
        isUploading.value = false;
        if (fileUploadInputRef.value) {
            fileUploadInputRef.value.value = '';
        }
    }
};

const confirmSelection = () => {
    if (selectedFile.value) {
        emit('select', selectedFile.value);
        isOpen.value = false;
    }
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-2xl max-h-[85vh] flex flex-col">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-lg font-bold">
                    <FolderKanban class="size-5 text-primary" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    Choose an existing image from your File Manager library or quickly upload a new one.
                </DialogDescription>
            </DialogHeader>

            <!-- Search and Action Bar -->
            <div class="flex items-center gap-2.5 py-1">
                <div class="relative flex-1">
                    <Search class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Search files or images..."
                        class="pl-9 h-9 text-xs"
                    />
                </div>

                <!-- Hidden file input for quick direct upload to library -->
                <input
                    ref="fileUploadInputRef"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="onDirectUpload"
                />

                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="h-9 shrink-0"
                    :disabled="isUploading"
                    @click="fileUploadInputRef?.click()"
                >
                    <Spinner v-if="isUploading" class="size-3.5 mr-1.5" />
                    <UploadCloud v-else class="size-3.5 mr-1.5 text-primary" />
                    {{ isUploading ? 'Uploading...' : 'Quick Upload' }}
                </Button>
            </div>

            <!-- Files Grid -->
            <div class="flex-1 overflow-y-auto min-h-[300px] max-h-[420px] py-2">
                <!-- Loading State -->
                <div v-if="isLoading" class="h-48 flex items-center justify-center">
                    <Spinner class="size-8 text-primary" />
                </div>

                <!-- Empty State -->
                <div
                    v-else-if="files.length === 0"
                    class="h-48 flex flex-col items-center justify-center text-center p-6 border border-dashed rounded-xl border-sidebar-border bg-muted/20"
                >
                    <ImageIcon class="size-10 text-muted-foreground/60 mb-2" />
                    <p class="text-sm font-medium text-foreground">No images found</p>
                    <p class="text-xs text-muted-foreground mt-1">
                        Use the "Quick Upload" button above to upload an image to your library.
                    </p>
                </div>

                <!-- Grid of Images -->
                <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 p-1">
                    <div
                        v-for="file in files"
                        :key="file.id"
                        class="group relative aspect-square rounded-xl overflow-hidden border-2 cursor-pointer transition-all duration-200 bg-muted/30"
                        :class="[
                            selectedFile?.id === file.id
                                ? 'border-primary ring-2 ring-primary/30 shadow-md'
                                : 'border-sidebar-border/70 hover:border-primary/50 hover:shadow-xs',
                        ]"
                        @click="selectedFile = file"
                    >
                        <!-- Thumbnail Image -->
                        <img
                            v-if="file.is_image"
                            :src="file.url"
                            :alt="file.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                            loading="lazy"
                        />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center p-2 text-center">
                            <span class="text-xs font-bold uppercase text-muted-foreground font-mono">
                                .{{ file.extension }}
                            </span>
                        </div>

                        <!-- Checkmark Indicator when selected -->
                        <div
                            v-if="selectedFile?.id === file.id"
                            class="absolute top-2 right-2 size-6 rounded-full bg-primary text-primary-foreground flex items-center justify-center shadow-xs"
                        >
                            <Check class="size-3.5 stroke-[3]" />
                        </div>

                        <!-- Info Overlay on bottom -->
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-2 text-white text-[11px] truncate">
                            <p class="font-medium truncate">{{ file.name }}</p>
                            <p class="text-[10px] text-white/70">{{ file.formatted_size }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer with Selection Info & Buttons -->
            <DialogFooter class="flex sm:justify-between items-center gap-2 pt-2 border-t border-sidebar-border/70">
                <div class="text-xs text-muted-foreground truncate">
                    <template v-if="selectedFile">
                        Selected: <span class="font-medium text-foreground">{{ selectedFile.name }}</span> ({{ selectedFile.formatted_size }})
                    </template>
                    <template v-else>
                        Click an image to select
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" type="button" size="sm" @click="isOpen = false">
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        :disabled="!selectedFile"
                        @click="confirmSelection"
                    >
                        Use This Image
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
