<script setup lang="ts">
import { Download, ExternalLink, Eye, FileText, Info, X } from '@lucide/vue';
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
import type { FileItem } from '@/types';

defineProps<{
    file: FileItem | null;
}>();

const isOpen = defineModel<boolean>('open', { default: false });

const formatDate = (dateStr?: string) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString();
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader v-if="file">
                <div class="flex items-center gap-2">
                    <DialogTitle class="text-lg font-bold truncate">
                        {{ file.name }}
                    </DialogTitle>
                    <Badge variant="outline" class="uppercase text-[10px] font-bold">
                        {{ file.extension || 'file' }}
                    </Badge>
                </div>
                <DialogDescription class="truncate font-mono text-xs">
                    {{ file.original_name }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="file" class="space-y-4 py-2">
                <!-- Image Preview Area -->
                <div
                    v-if="file.is_image"
                    class="w-full max-h-[400px] rounded-2xl overflow-hidden bg-black/5 dark:bg-black/30 border border-sidebar-border/70 flex items-center justify-center p-2"
                >
                    <img
                        :src="file.url"
                        :alt="file.name"
                        class="max-h-[380px] w-auto max-w-full object-contain rounded-lg shadow-sm"
                    />
                </div>

                <!-- Non-Image Placeholder Box -->
                <div
                    v-else
                    class="p-8 rounded-2xl border border-dashed border-sidebar-border bg-muted/20 flex flex-col items-center justify-center text-center gap-2"
                >
                    <div class="size-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shadow-xs">
                        <FileText class="size-8" />
                    </div>
                    <p class="font-semibold text-sm text-foreground">{{ file.name }}</p>
                    <p class="text-xs text-muted-foreground">Direct preview not available in browser for this file type.</p>
                </div>

                <!-- Description / Notes -->
                <div v-if="file.description" class="p-3 rounded-xl bg-muted/40 border border-sidebar-border/60 text-xs">
                    <span class="font-semibold block text-foreground mb-0.5">Description / Notes:</span>
                    <p class="text-muted-foreground">{{ file.description }}</p>
                </div>

                <!-- Detailed File Metadata Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3.5 rounded-xl border border-sidebar-border/70 bg-card text-xs">
                    <div>
                        <span class="text-muted-foreground block text-[11px]">Size</span>
                        <span class="font-mono font-medium text-foreground">{{ file.formatted_size }} ({{ file.size.toLocaleString() }} B)</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-[11px]">Type / Category</span>
                        <span class="font-medium text-foreground capitalize">{{ file.category }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-[11px]">MIME Type</span>
                        <span class="font-mono text-foreground truncate block" :title="file.mime_type || 'Unknown'">
                            {{ file.mime_type || 'Unknown' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-[11px]">Uploaded Date</span>
                        <span class="text-foreground">{{ formatDate(file.created_at) }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-[11px]">Last Updated</span>
                        <span class="text-foreground">{{ formatDate(file.updated_at) }}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-[11px]">Storage Disk</span>
                        <span class="font-mono text-foreground uppercase">{{ file.disk }}</span>
                    </div>
                </div>
            </div>

            <DialogFooter v-if="file" class="flex sm:justify-between items-center gap-2">
                <Button variant="outline" type="button" @click="isOpen = false">
                    Close
                </Button>
                <a :href="`/file-manager/${file.id}/download`" download>
                    <Button type="button">
                        <Download class="size-4 mr-1.5" />
                        Download File
                    </Button>
                </a>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
