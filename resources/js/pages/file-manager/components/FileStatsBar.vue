<script setup lang="ts">
import { Files, HardDrive, ShieldCheck, Trash2 } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import type { FileManagerStats } from '@/types';

defineProps<{
    stats: FileManagerStats;
    activeTab: 'all' | 'trash';
}>();

const emit = defineEmits<{
    (e: 'switch-tab', tab: 'all' | 'trash'): void;
}>();
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Files -->
        <Card class="border-sidebar-border/70 shadow-xs hover:border-sidebar-border transition-colors">
            <CardContent class="p-4 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Files</p>
                    <p class="text-2xl font-bold tracking-tight text-foreground">
                        {{ stats.total_files.toLocaleString() }}
                    </p>
                </div>
                <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
                    <Files class="size-5" />
                </div>
            </CardContent>
        </Card>

        <!-- Storage Used -->
        <Card class="border-sidebar-border/70 shadow-xs hover:border-sidebar-border transition-colors">
            <CardContent class="p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Storage Used</p>
                        <p class="text-2xl font-bold tracking-tight text-foreground">
                            {{ stats.storage_used_formatted }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <HardDrive class="size-5" />
                    </div>
                </div>
                <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                    <div
                        class="bg-blue-600 h-1.5 rounded-full transition-all duration-500"
                        :style="{ width: Math.min(100, Math.max(5, (stats.storage_used / (100 * 1048576)) * 100)) + '%' }"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Recycle Bin (Trash) -->
        <Card
            class="border-sidebar-border/70 shadow-xs hover:border-sidebar-border transition-colors cursor-pointer group"
            :class="{ 'ring-2 ring-amber-500/50': activeTab === 'trash' }"
            @click="emit('switch-tab', 'trash')"
        >
            <CardContent class="p-4 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Recycle Bin</p>
                    <div class="flex items-center gap-2">
                        <p class="text-2xl font-bold tracking-tight" :class="stats.trash_count > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-foreground'">
                            {{ stats.trash_count.toLocaleString() }}
                        </p>
                        <span class="text-xs text-muted-foreground">files</span>
                    </div>
                </div>
                <div
                    class="p-2.5 rounded-xl transition-colors"
                    :class="stats.trash_count > 0 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 group-hover:bg-amber-500/20' : 'bg-muted text-muted-foreground'"
                >
                    <Trash2 class="size-5" />
                </div>
            </CardContent>
        </Card>

        <!-- Accepted Extensions -->
        <Card class="border-sidebar-border/70 shadow-xs hover:border-sidebar-border transition-colors">
            <CardContent class="p-4 flex items-center justify-between">
                <div class="space-y-1.5">
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Accepted Files</p>
                    <div class="flex items-center gap-1.5">
                        <Badge variant="outline" class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20 font-medium">
                            Any Extension Allowed
                        </Badge>
                    </div>
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <ShieldCheck class="size-5" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>
