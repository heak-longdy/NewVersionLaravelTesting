<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import type { UserItem } from '@/types';

const props = defineProps<{
    user?: UserItem;
    submitUrl: string;
    submitMethod: 'post' | 'put';
}>();

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    if (props.submitMethod === 'put') {
        form.put(props.submitUrl);
    } else {
        form.post(props.submitUrl);
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Name & Email -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="name">
                    Full Name <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="e.g. Jane Doe"
                    :disabled="form.processing"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="space-y-2">
                <Label for="email">
                    Email Address <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="e.g. jane.doe@example.com"
                    :disabled="form.processing"
                />
                <InputError :message="form.errors.email" />
            </div>
        </div>

        <!-- Password & Confirmation -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="password">
                    Password
                    <span v-if="!user" class="text-destructive">*</span>
                    <span v-else class="text-xs text-muted-foreground font-normal ml-1">
                        (Leave blank to keep current)
                    </span>
                </Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    :required="!user"
                    placeholder="••••••••"
                    :disabled="form.processing"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="space-y-2">
                <Label for="password_confirmation">
                    Confirm Password
                    <span v-if="!user" class="text-destructive">*</span>
                </Label>
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    :required="!user"
                    placeholder="••••••••"
                    :disabled="form.processing"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 border-t pt-4">
            <Button variant="outline" as-child :disabled="form.processing">
                <Link href="/users">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                <Spinner v-if="form.processing" class="mr-2" />
                <span v-if="form.processing">Saving...</span>
                <span v-else>{{ user ? 'Update User' : 'Create User' }}</span>
            </Button>
        </div>
    </form>
</template>
