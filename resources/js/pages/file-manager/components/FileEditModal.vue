<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { FileUp, Info, Pencil, RefreshCw, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
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
import type { FileItem } from '@/types';

const props = defineProps<{
    file: FileItem | null;
}>();

const isOpen = defineModel<boolean>('open', { default: false });

const emit = defineEmits<{
    (e: 'updated'): void;
}>();

const replacementInputRef = ref<HTMLInputElement | null>(null);
const replacementFile = ref<File | null>(null);

const form = useForm({
    name: '',
    description: '',
    file: null as File | null,
});

watch(
    () => props.file,
    (newFile) => {
        if (newFile) {
            form.name = newFile.name;
            form.description = newFile.description ?? '';
            form.file = null;
            replacementFile.value = null;
        }
    },
    { immediate: true },
);

const onReplacementChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        replacementFile.value = target.files[0];
        form.file = target.files[0];
    }
};

const removeReplacement = () => {
    replacementFile.value = null;
    form.file = null;
    if (replacementInputRef.value) {
        replacementInputRef.value.value = '';
    }
};

const close = () => {
    replacementFile.value = null;
    form.reset();
    form.clearErrors();
    isOpen.value = false;
};

const submitUpdate = () => {
    if (!props.file) return;

    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/file-manager/${props.file.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            close();
            emit('updated');
        },
    });
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-xl font-bold">
                    <Pencil class="size-5 text-primary" />
                    Edit File Details
                </DialogTitle>
                <DialogDescription>
                    Update file display title, notes, or replace file content.
                </DialogDescription>
            </DialogHeader>

            <form v-if="file" @submit.prevent="submitUpdate" class="space-y-4 py-2">
                <!-- Info banner -->
                <div class="p-3 rounded-xl border border-sidebar-border bg-muted/30 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Original Filename:</span>
                        <span class="font-mono font-medium text-foreground truncate max-w-[240px]" :title="file.original_name">
                            {{ file.original_name }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Size & Extension:</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-foreground">{{ file.formatted_size }}</span>
                            <Badge variant="outline" class="text-[10px] uppercase font-bold">
                                {{ file.extension || 'file' }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <!-- Display Name Field -->
                <div class="space-y-1.5">
                    <Label for="edit-name">Display Name</Label>
                    <Input
                        id="edit-name"
                        v-model="form.name"
                        placeholder="Enter display title"
                        required
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Description Field -->
                <div class="space-y-1.5">
                    <Label for="edit-desc">Description / Notes</Label>
                    <Input
                        id="edit-desc"
                        v-model="form.description"
                        placeholder="Add notes or tags for this file"
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <!-- Replace File Content Option -->
                <div class="space-y-2 pt-2 border-t border-sidebar-border/60">
                    <div class="flex items-center justify-between">
                        <div>
                            <Label class="text-xs font-semibold">Replace File Content</Label>
                            <p class="text-[11px] text-muted-foreground">Optionally upload a newer version of this file.</p>
                        </div>
                        <input
                            ref="replacementInputRef"
                            type="file"
                            class="hidden"
                            @change="onReplacementChange"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="replacementInputRef?.click()"
                        >
                            <RefreshCw class="size-3.5 mr-1.5" />
                            Choose New File
                        </Button>
                    </div>

                    <!-- Selected replacement preview -->
                    <div
                        v-if="replacementFile"
                        class="flex items-center justify-between p-2.5 rounded-xl border border-primary/40 bg-primary/5 text-xs"
                    >
                        <div class="flex items-center gap-2 truncate">
                            <FileUp class="size-4 text-primary shrink-0" />
                            <span class="font-medium text-foreground truncate">{{ replacementFile.name }}</span>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="size-6 text-muted-foreground hover:text-destructive shrink-0"
                            @click="removeReplacement"
                        >
                            <X class="size-3.5" />
                        </Button>
                    </div>
                </div>

                <DialogFooter class="flex sm:justify-between items-center gap-2 pt-2">
                    <Button variant="outline" type="button" @click="close" :disabled="form.processing">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Spinner v-if="form.processing" class="size-4 mr-1.5" />
                        Save Changes
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
