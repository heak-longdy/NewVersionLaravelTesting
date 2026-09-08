<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    AlertCircle,
    Check,
    FileUp,
    HardDriveUpload,
    RefreshCw,
    UploadCloud,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
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
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

interface StagedFileItem {
    id: string;
    file: File;
    status: 'pending' | 'uploading' | 'success' | 'error';
    progress: number;
    error?: string;
}

const isOpen = defineModel<boolean>('open', { default: false });

const emit = defineEmits<{
    (e: 'uploaded'): void;
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const stagedItems = ref<StagedFileItem[]>([]);
const isUploading = ref(false);
const activeXhr = ref<XMLHttpRequest | null>(null);

// Single file custom metadata
const singleFileName = ref('');
const singleFileDescription = ref('');
const globalErrorMessage = ref('');

const formatBytes = (bytes: number): string => {
    if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(1) + ' MB';
    }
    if (bytes >= 1024) {
        return (bytes / 1024).toFixed(0) + ' KB';
    }
    return bytes + ' B';
};

const handleFiles = (incoming: FileList | File[]) => {
    globalErrorMessage.value = '';
    const list = Array.from(incoming);

    for (const f of list) {
        stagedItems.value.push({
            id: `${f.name}-${f.size}-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`,
            file: f,
            status: 'pending',
            progress: 0,
        });
    }

    if (stagedItems.value.length === 1 && !singleFileName.value) {
        const file = stagedItems.value[0].file;
        const lastDot = file.name.lastIndexOf('.');
        singleFileName.value = lastDot > 0 ? file.name.substring(0, lastDot) : file.name;
    }
};

const onFileInputChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        handleFiles(target.files);
    }
    target.value = '';
};

const onDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        handleFiles(event.dataTransfer.files);
    }
};

const removeStagedItem = (index: number) => {
    if (isUploading.value) return;
    stagedItems.value.splice(index, 1);
    if (stagedItems.value.length === 0) {
        singleFileName.value = '';
        singleFileDescription.value = '';
        globalErrorMessage.value = '';
    }
};

const clearAll = () => {
    if (isUploading.value) return;
    stagedItems.value = [];
    singleFileName.value = '';
    singleFileDescription.value = '';
    globalErrorMessage.value = '';
};

const close = () => {
    if (isUploading.value && activeXhr.value) {
        activeXhr.value.abort();
    }
    isUploading.value = false;
    clearAll();
    isOpen.value = false;
};

const pendingOrErrorCount = computed(() => {
    return stagedItems.value.filter((i) => i.status === 'pending' || i.status === 'error').length;
});

const overallProgress = computed(() => {
    if (stagedItems.value.length === 0) return 0;
    const total = stagedItems.value.reduce((acc, curr) => acc + curr.progress, 0);
    return Math.round(total / stagedItems.value.length);
});

const uploadSingleFile = (
    item: StagedFileItem,
    customName?: string,
    customDesc?: string,
): Promise<boolean> => {
    return new Promise((resolve) => {
        item.status = 'uploading';
        item.progress = 0;
        item.error = undefined;

        const formData = new FormData();
        formData.append('file', item.file);
        if (customName) formData.append('name', customName);
        if (customDesc) formData.append('description', customDesc);

        const xhr = new XMLHttpRequest();
        activeXhr.value = xhr;

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                item.progress = Math.round((e.loaded / e.total) * 100);
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                item.status = 'success';
                item.progress = 100;
                resolve(true);
            } else {
                item.status = 'error';
                item.progress = 0;
                let message = 'Upload failed.';
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.message) {
                        message = data.message;
                    } else if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        const err = data.errors[firstKey];
                        message = Array.isArray(err) ? err[0] : String(err);
                    }
                } catch {
                    if (xhr.status === 413) {
                        message = 'File exceeds maximum upload size allowed by server.';
                    } else if (xhr.status === 422) {
                        message = 'Validation failed for this file.';
                    } else if (xhr.status === 500) {
                        message = 'Server encountered an error while storing this file.';
                    }
                }
                item.error = message;
                resolve(false);
            }
        });

        xhr.addEventListener('error', () => {
            item.status = 'error';
            item.progress = 0;
            item.error = 'Network error or server connection refused.';
            resolve(false);
        });

        xhr.addEventListener('abort', () => {
            item.status = 'pending';
            item.progress = 0;
            resolve(false);
        });

        xhr.open('POST', '/file-manager');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        }

        xhr.send(formData);
    });
};

const submitUpload = async () => {
    if (stagedItems.value.length === 0 || isUploading.value) return;

    globalErrorMessage.value = '';
    isUploading.value = true;

    const isSingle = stagedItems.value.length === 1;
    let anySuccess = false;
    let anyError = false;

    // Process files that are pending or errored
    for (const item of stagedItems.value) {
        if (item.status === 'success') continue;

        const success = await uploadSingleFile(
            item,
            isSingle ? singleFileName.value : undefined,
            isSingle ? singleFileDescription.value : undefined,
        );

        if (success) {
            anySuccess = true;
        } else {
            anyError = true;
        }
    }

    activeXhr.value = null;
    isUploading.value = false;

    if (anySuccess) {
        emit('uploaded');
    }

    // If all files uploaded successfully, auto-close modal
    if (!anyError) {
        close();
    } else {
        // Filter out completed files so user can see and retry remaining files
        stagedItems.value = stagedItems.value.filter((i) => i.status !== 'success');
        globalErrorMessage.value = anySuccess
            ? 'Some files were uploaded, but others failed. Please review errors below.'
            : 'Upload failed for the selected files. Please check file sizes and server limits.';
    }
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-xl max-h-[90vh] flex flex-col p-6">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-xl font-bold">
                    <UploadCloud class="size-5 text-primary" />
                    Upload Files
                </DialogTitle>
                <DialogDescription>
                    Upload files with <span class="font-semibold text-foreground">any extension</span>. Max 100MB per file.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-2 flex-1 overflow-y-auto pr-1">
                <!-- Global Error Alert -->
                <div
                    v-if="globalErrorMessage"
                    class="p-3 rounded-xl bg-destructive/10 border border-destructive/20 text-destructive text-xs flex items-start gap-2.5"
                >
                    <AlertCircle class="size-4 shrink-0 mt-0.5" />
                    <div class="flex-1 font-medium">
                        {{ globalErrorMessage }}
                    </div>
                </div>

                <!-- Drag & Drop Zone -->
                <div
                    class="border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-3 relative"
                    :class="[
                        isDragging
                            ? 'border-primary bg-primary/10 scale-[0.99]'
                            : 'border-sidebar-border hover:border-primary/60 hover:bg-muted/30 bg-muted/10',
                        isUploading ? 'pointer-events-none opacity-60' : '',
                    ]"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="onDrop"
                    @click="fileInputRef?.click()"
                >
                    <input
                        ref="fileInputRef"
                        type="file"
                        multiple
                        class="hidden"
                        :disabled="isUploading"
                        @click.stop
                        @change="onFileInputChange"
                    />

                    <div class="size-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shadow-xs">
                        <HardDriveUpload class="size-7" />
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-foreground">
                            Drag & drop files here, or <span class="text-primary hover:underline">browse</span>
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Accepts images, videos, audio, documents, archives, code, executables, or custom data formats.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge variant="outline" class="text-[11px] bg-background/50 font-normal">
                            Any Extension Supported
                        </Badge>
                        <Badge variant="outline" class="text-[11px] bg-background/50 font-normal">
                            Up to 100MB
                        </Badge>
                    </div>
                </div>

                <!-- Staged Files List -->
                <div v-if="stagedItems.length > 0" class="space-y-2">
                    <div class="flex items-center justify-between text-xs text-muted-foreground px-1">
                        <span>Ready to upload ({{ stagedItems.length }} {{ stagedItems.length === 1 ? 'file' : 'files' }})</span>
                        <button
                            v-if="!isUploading"
                            type="button"
                            class="text-destructive hover:underline text-xs"
                            @click="clearAll"
                        >
                            Clear all
                        </button>
                    </div>

                    <div class="max-h-52 overflow-y-auto space-y-2 pr-1">
                        <div
                            v-for="(item, idx) in stagedItems"
                            :key="item.id"
                            class="p-2.5 rounded-xl border transition-all text-xs"
                            :class="[
                                item.status === 'error'
                                    ? 'border-destructive/40 bg-destructive/5'
                                    : item.status === 'success'
                                      ? 'border-emerald-500/40 bg-emerald-500/5'
                                      : 'border-sidebar-border/80 bg-card/60',
                            ]"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <!-- Status Icon -->
                                    <div class="shrink-0">
                                        <Spinner v-if="item.status === 'uploading'" class="size-4 text-primary" />
                                        <div
                                            v-else-if="item.status === 'success'"
                                            class="size-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]"
                                        >
                                            <Check class="size-3" />
                                        </div>
                                        <AlertCircle
                                            v-else-if="item.status === 'error'"
                                            class="size-4 text-destructive"
                                        />
                                        <FileUp v-else class="size-4 text-primary" />
                                    </div>

                                    <span class="font-medium text-foreground truncate" :title="item.file.name">
                                        {{ item.file.name }}
                                    </span>
                                    <span class="text-muted-foreground shrink-0 font-mono text-[11px]">
                                        ({{ formatBytes(item.file.size) }})
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span v-if="item.status === 'uploading'" class="text-[11px] font-mono text-primary font-medium">
                                        {{ item.progress }}%
                                    </span>
                                    <span v-else-if="item.status === 'success'" class="text-[11px] font-medium text-emerald-500">
                                        Uploaded
                                    </span>

                                    <Button
                                        v-if="!isUploading"
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="size-6 text-muted-foreground hover:text-destructive shrink-0"
                                        @click="removeStagedItem(idx)"
                                    >
                                        <X class="size-3.5" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Per-item upload error banner -->
                            <div
                                v-if="item.error"
                                class="mt-2 pt-1.5 border-t border-destructive/20 text-destructive text-[11px] flex items-center gap-1.5"
                            >
                                <AlertCircle class="size-3.5 shrink-0" />
                                <span>{{ item.error }}</span>
                            </div>

                            <!-- Per-item progress bar when active -->
                            <div v-if="item.status === 'uploading'" class="mt-2 w-full bg-muted rounded-full h-1 overflow-hidden">
                                <div
                                    class="bg-primary h-1 rounded-full transition-all duration-150"
                                    :style="{ width: `${item.progress}%` }"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Single file metadata inputs -->
                    <div
                        v-if="stagedItems.length === 1 && stagedItems[0].status !== 'success'"
                        class="space-y-3 pt-2 border-t border-sidebar-border/60"
                    >
                        <div class="space-y-1">
                            <Label for="file-title" class="text-xs">Custom Display Name (Optional)</Label>
                            <Input
                                id="file-title"
                                v-model="singleFileName"
                                placeholder="E.g. Q3 Financial Report"
                                class="h-9 text-xs"
                                :disabled="isUploading"
                            />
                        </div>
                        <div class="space-y-1">
                            <Label for="file-desc" class="text-xs">Description / Notes (Optional)</Label>
                            <Input
                                id="file-desc"
                                v-model="singleFileDescription"
                                placeholder="Add notes about this file..."
                                class="h-9 text-xs"
                                :disabled="isUploading"
                            />
                        </div>
                    </div>

                    <!-- Overall Progress Indicator when uploading multiple -->
                    <div v-if="isUploading && stagedItems.length > 1" class="space-y-1 pt-2 border-t border-sidebar-border/60">
                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span>Uploading files...</span>
                            <span class="font-mono">{{ overallProgress }}%</span>
                        </div>
                        <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                            <div
                                class="bg-primary h-1.5 rounded-full transition-all duration-200"
                                :style="{ width: `${overallProgress}%` }"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex sm:justify-between items-center gap-2 pt-2 border-t border-sidebar-border/60">
                <Button variant="outline" type="button" @click="close" :disabled="isUploading">
                    Cancel
                </Button>
                <Button
                    type="button"
                    :disabled="pendingOrErrorCount === 0 || isUploading"
                    @click="submitUpload"
                >
                    <Spinner v-if="isUploading" class="size-4 mr-1.5" />
                    <RefreshCw v-else-if="stagedItems.some(i => i.status === 'error')" class="size-4 mr-1.5" />
                    <UploadCloud v-else class="size-4 mr-1.5" />
                    <template v-if="isUploading">
                        Uploading...
                    </template>
                    <template v-else-if="stagedItems.some(i => i.status === 'error')">
                        Retry ({{ pendingOrErrorCount }})
                    </template>
                    <template v-else>
                        {{ stagedItems.length > 1 ? `Upload (${stagedItems.length}) Files` : 'Upload File' }}
                    </template>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
