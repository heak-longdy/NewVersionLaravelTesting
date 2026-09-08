<script setup lang="ts">
import {
    Archive,
    Code2,
    Download,
    Eye,
    File,
    FileText,
    MoreHorizontal,
    Music,
    Pencil,
    RotateCcw,
    Trash2,
    Video,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { FileItem } from '@/types';

defineProps<{
    files: FileItem[];
    isTrashView?: boolean;
}>();

const emit = defineEmits<{
    (e: 'preview', file: FileItem): void;
    (e: 'edit', file: FileItem): void;
    (e: 'trash', file: FileItem): void;
    (e: 'restore', file: FileItem): void;
    (e: 'force-delete', file: FileItem): void;
}>();

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div class="rounded-xl border border-sidebar-border/70 overflow-hidden bg-card/50">
        <Table>
            <TableHeader>
                <TableRow class="hover:bg-transparent bg-muted/40">
                    <TableHead class="w-[340px]">Name</TableHead>
                    <TableHead>Extension</TableHead>
                    <TableHead>Size</TableHead>
                    <TableHead class="hidden md:table-cell">Original Filename</TableHead>
                    <TableHead>{{ isTrashView ? 'Deleted At' : 'Uploaded At' }}</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="file in files"
                    :key="file.id"
                    class="hover:bg-muted/40 transition-colors"
                >
                    <!-- Name & Icon -->
                    <TableCell>
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg shrink-0 overflow-hidden bg-muted/60 flex items-center justify-center border border-sidebar-border/60">
                                <img
                                    v-if="file.is_image"
                                    :src="file.url"
                                    :alt="file.name"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                />
                                <template v-else>
                                    <FileText v-if="file.category === 'document'" class="size-5 text-rose-500" />
                                    <Video v-else-if="file.category === 'video'" class="size-5 text-purple-500" />
                                    <Music v-else-if="file.category === 'audio'" class="size-5 text-pink-500" />
                                    <Archive v-else-if="file.category === 'archive'" class="size-5 text-amber-500" />
                                    <Code2 v-else-if="file.category === 'code'" class="size-5 text-sky-500" />
                                    <File v-else class="size-5 text-slate-500" />
                                </template>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-sm text-foreground truncate" :title="file.name">
                                    {{ file.name }}
                                </p>
                                <p v-if="file.description" class="text-xs text-muted-foreground truncate" :title="file.description">
                                    {{ file.description }}
                                </p>
                            </div>
                        </div>
                    </TableCell>

                    <!-- Extension badge -->
                    <TableCell>
                        <Badge variant="outline" class="uppercase font-mono text-[11px] font-semibold">
                            {{ file.extension || 'file' }}
                        </Badge>
                    </TableCell>

                    <!-- Size -->
                    <TableCell class="font-mono text-xs text-foreground/80">
                        {{ file.formatted_size }}
                    </TableCell>

                    <!-- Original Filename -->
                    <TableCell class="hidden md:table-cell text-xs text-muted-foreground truncate max-w-[200px]" :title="file.original_name">
                        {{ file.original_name }}
                    </TableCell>

                    <!-- Date -->
                    <TableCell class="text-xs text-muted-foreground whitespace-nowrap">
                        {{ formatDate(isTrashView && file.deleted_at ? file.deleted_at : file.created_at) }}
                    </TableCell>

                    <!-- Actions -->
                    <TableCell class="text-right">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="size-8">
                                    <MoreHorizontal class="size-4" />
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
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
