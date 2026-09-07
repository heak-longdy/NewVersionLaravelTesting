<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Camera, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import { index } from '@/actions/App/Http/Controllers/CustomerController';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { useInitials } from '@/composables/useInitials';
import type { Customer } from '@/types';

const props = defineProps<{
    customer?: Customer;
    submitUrl: string;
    submitMethod: 'post' | 'put';
}>();

const { getInitials } = useInitials();
const fileInputRef = ref<HTMLInputElement | null>(null);
const imagePreview = ref<string | null>(props.customer?.image ?? null);

const form = useForm({
    name: props.customer?.name ?? '',
    email: props.customer?.email ?? '',
    image: null as File | null,
    remove_image: false,
    phone: props.customer?.phone ?? '',
    company: props.customer?.company ?? '',
    address: props.customer?.address ?? '',
    status: props.customer?.status ?? 'active',
    notes: props.customer?.notes ?? '',
});

const isActive = computed({
    get: () => form.status === 'active',
    set: (val: boolean) => {
        form.status = val ? 'active' : 'inactive';
    },
});

const onImageSelected = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.image = file;
        form.remove_image = false;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    form.remove_image = true;
    imagePreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const submit = () => {
    if (props.submitMethod === 'put') {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(props.submitUrl, {
            forceFormData: true,
        });
    } else {
        form.post(props.submitUrl, {
            forceFormData: true,
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Customer Photo / Avatar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 p-4 rounded-xl border bg-card/60 shadow-2xs">
            <Avatar class="size-20 rounded-full border-2 border-border shadow-xs overflow-hidden shrink-0">
                <AvatarImage
                    v-if="imagePreview"
                    :src="imagePreview"
                    :alt="form.name || 'Customer'"
                    class="object-cover w-full h-full"
                />
                <AvatarFallback class="text-lg font-semibold bg-muted text-foreground">
                    {{ getInitials(form.name || 'Customer') }}
                </AvatarFallback>
            </Avatar>

            <div class="space-y-2 flex-1">
                <div>
                    <Label class="text-base font-medium">Customer Photo</Label>
                    <p class="text-xs text-muted-foreground">Upload a JPG, PNG, or WEBP image (max 2MB).</p>
                </div>
                <div class="flex items-center gap-3">
                    <input
                        ref="fileInputRef"
                        type="file"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="hidden"
                        @change="onImageSelected"
                        :disabled="form.processing"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="fileInputRef?.click()"
                        :disabled="form.processing"
                    >
                        <Camera class="size-4 mr-1.5" />
                        {{ imagePreview ? 'Change Photo' : 'Upload Photo' }}
                    </Button>
                    <Button
                        v-if="imagePreview"
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="removeImage"
                        :disabled="form.processing"
                        class="text-destructive hover:text-destructive hover:bg-destructive/10"
                    >
                        <Trash2 class="size-4 mr-1.5" />
                        Remove
                    </Button>
                </div>
                <InputError :message="form.errors.image" />
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Name -->
            <div class="space-y-2">
                <Label for="name">
                    Name <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="e.g. John Doe"
                    :disabled="form.processing"
                />
                <InputError :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <Label for="email">
                    Email <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="e.g. john@example.com"
                    :disabled="form.processing"
                />
                <InputError :message="form.errors.email" />
            </div>

            <!-- Phone -->
            <div class="space-y-2">
                <Label for="phone">Phone Number</Label>
                <Input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    placeholder="e.g. +1 (555) 234-5678"
                    :disabled="form.processing"
                />
                <InputError :message="form.errors.phone" />
            </div>

            <!-- Company -->
            <div class="space-y-2">
                <Label for="company">Company</Label>
                <Input
                    id="company"
                    v-model="form.company"
                    type="text"
                    placeholder="e.g. Acme Corporation"
                    :disabled="form.processing"
                />
                <InputError :message="form.errors.company" />
            </div>
        </div>

        <!-- Status Switch -->
        <div class="flex items-center justify-between rounded-lg border p-4 shadow-xs">
            <div class="space-y-0.5">
                <Label for="status" class="text-base font-medium">Customer Status</Label>
                <p class="text-sm text-muted-foreground">
                    Active customers can receive communications and make purchases.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium" :class="isActive ? 'text-primary' : 'text-muted-foreground'">
                    {{ isActive ? 'Active' : 'Inactive' }}
                </span>
                <Switch
                    id="status"
                    :checked="isActive"
                    @update:checked="isActive = $event"
                    :disabled="form.processing"
                />
            </div>
        </div>

        <!-- Address -->
        <div class="space-y-2">
            <Label for="address">Address</Label>
            <textarea
                id="address"
                v-model="form.address"
                rows="2"
                placeholder="Street address, City, State, ZIP..."
                :disabled="form.processing"
                class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            ></textarea>
            <InputError :message="form.errors.address" />
        </div>

        <!-- Notes -->
        <div class="space-y-2">
            <Label for="notes">Internal Notes</Label>
            <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                placeholder="Additional notes about this customer..."
                :disabled="form.processing"
                class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            ></textarea>
            <InputError :message="form.errors.notes" />
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <Button variant="outline" as-child :disabled="form.processing">
                <Link :href="index.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                <Spinner v-if="form.processing" />
                <span v-if="form.processing">Saving...</span>
                <span v-else>{{ customer ? 'Update Customer' : 'Create Customer' }}</span>
            </Button>
        </div>
    </form>
</template>
