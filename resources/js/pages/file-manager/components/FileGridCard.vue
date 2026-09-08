<script setup lang="ts">
import {
    Archive,
    Code2,
    Download,
    Eye,
    File,
    FileText,
    MoreVertical,
    Music,
    Pencil,
    RotateCcw,
    Trash2,
    Video,
} from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { FileItem } from '@/types';

const props = defineProps<{
    file: FileItem;
    isTrashView?: boolean;
}>();

const emit = defineEmits<{
    (e: 'preview', file: FileItem): void;
    (e: 'edit', file: FileItem): void;
    (e: 'trash', file: FileItem): void;
    (e: 'restore', file: FileItem): void;
    (e: 'force-delete', file: FileItem): void;
}>();

const extensionUpper = computed(() => {
    return (props.file.extension || 'FILE').toUpperCase();
});

const categoryColor = computed(() => {
    switch (props.file.category) {
        case 'image':
            return {
                bg: 'bg-emerald-500/10 dark:bg-emerald-500/20',
                text: 'text-emerald-600 dark:text-emerald-400',
                badge: 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30',
            };
        case 'document':
            return {
                bg: 'bg-rose-500/10 dark:bg-rose-500/20',
                text: 'text-rose-600 dark:text-rose-400',
                badge: 'bg-rose-500/10 text-rose-600 border-rose-500/30',
            };
        case 'video':
            return {
                bg: 'bg-purple-500/10 dark:bg-purple-500/20',
                text: 'text-purple-600 dark:text-purple-400',
                badge: 'bg-purple-500/10 text-purple-600 border-purple-500/30',
            };
        case 'audio':
            return {
                bg: 'bg-pink-500/10 dark:bg-pink-500/20',
                text: 'text-pink-600 dark:text-pink-400',
                badge: 'bg-pink-500/10 text-pink-600 border-pink-500/30',
            };
        case 'archive':
            return {
                bg: 'bg-amber-500/10 dark:bg-amber-500/20',
                text: 'text-amber-600 dark:text-amber-400',
                badge: 'bg-amber-500/10 text-amber-600 border-amber-500/30',
            };
        case 'code':
            return {
                bg: 'bg-sky-500/10 dark:bg-sky-500/20',
                text: 'text-sky-600 dark:text-sky-400',
                badge: 'bg-sky-500/10 text-sky-600 border-sky-500/30',
            };
        default:
            return {
                bg: 'bg-slate-500/10 dark:bg-slate-500/20',
                text: 'text-slate-600 dark:text-slate-400',
                badge: 'bg-slate-500/10 text-slate-600 border-slate-500/30',
            };
    }
});

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <Card class="border-sidebar-border/70 group hover:border-primary/50 hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between">
        <!-- Card Top / Preview -->
        <div class="relative w-full aspect-video bg-muted/40 flex items-center justify-center overflow-hidden border-b border-sidebar-border/50">
            <!-- Image thumbnail preview -->
            <img
                v-if="file.is_image"
                :src="file.url"
                :alt="file.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
            />

            <!-- Non-image dynamic icon view -->
            <div v-else class="flex flex-col items-center justify-center gap-2 p-4 text-center">
                <div class="size-14 rounded-2xl flex items-center justify-center shadow-xs" :class="[categoryColor.bg, categoryColor.text]">
                    <FileText v-if="file.category === 'document'" class="size-7" />
                    <Video v-else-if="file.category === 'video'" class="size-7" />
                    <Music v-else-if="file.category === 'audio'" class="size-7" />
                    <Archive v-else-if="file.category === 'archive'" class="size-7" />
                    <Code2 v-else-if="file.category === 'code'" class="size-7" />
                    <File v-else class="size-7" />
                </div>
            </div>

            <!-- Extension badge overlay -->
            <div class="absolute top-2.5 left-2.5">
                <Badge variant="outline" class="font-bold text-[10px] tracking-wider uppercase backdrop-blur-md bg-background/80 shadow-xs" :class="categoryColor.text">
                    {{ extensionUpper }}
                </Badge>
            </div>

            <!-- Context Dropdown Menu -->
            <div class="absolute top-2 right-2 opacity-90 group-hover:opacity-100 transition-opacity">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="secondary" size="icon" class="size-8 rounded-lg bg-background/80 backdrop-blur-md shadow-xs hover:bg-background">
                            <MoreVertical class="size-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <template v-if="!isTrashView">
                            <DropdownMenuItem @click="emit('preview', file)">
                                <Eye class="size-4 mr-2" />
                                Preview
                            </DropdownMenuItem>
                            <a :href="`/file-manager/${file.id}/download`" download class="w-full">
                                <DropdownMenuItem>
                                    <Download class="size-4 mr-2" />
                                    Download
                                </DropdownMenuItem>
                            </a>
                            <DropdownMenuItem @click="emit('edit', file)">
                                <Pencil class="size-4 mr-2" />
                                Edit / Rename
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem class="text-amber-600 focus:text-amber-600" @click="emit('trash', file)">
                                <Trash2 class="size-4 mr-2" />
                                Move to Trash
                            </DropdownMenuItem>
                        </template>
                        <template v-else>
                            <DropdownMenuItem class="text-emerald-600 focus:text-emerald-600" @click="emit('restore', file)">
                                <RotateCcw class="size-4 mr-2" />
                                Restore File
                            </DropdownMenuItem>
                            <a :href="`/file-manager/${file.id}/download`" download class="w-full">
                                <DropdownMenuItem>
                                    <Download class="size-4 mr-2" />
                                    Download
                                </DropdownMenuItem>
                            </a>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem class="text-destructive focus:text-destructive" @click="emit('force-delete', file)">
                                <Trash2 class="size-4 mr-2" />
                                Delete Permanently
                            </DropdownMenuItem>
                        </template>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <!-- Card Body -->
        <CardContent class="p-3.5 space-y-2 flex-1 flex flex-col justify-between">
            <div>
                <h4 class="font-medium text-sm text-foreground truncate" :title="file.name">
                    {{ file.name }}
                </h4>
                <p class="text-xs text-muted-foreground truncate" :title="file.original_name">
                    {{ file.original_name }}
                </p>
                <p v-if="file.description" class="text-xs text-muted-foreground line-clamp-1 italic mt-1" :title="file.description">
                    "{{ file.description }}"
                </p>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-sidebar-border/50 text-[11px] text-muted-foreground">
                <span class="font-medium text-foreground/80">{{ file.formatted_size }}</span>
                <span>{{ formatDate(isTrashView && file.deleted_at ? file.deleted_at : file.created_at) }}</span>
            </div>
        </CardContent>
    </Card>
</template>
