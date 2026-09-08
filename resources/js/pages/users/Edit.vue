<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import UserForm from '@/pages/users/components/UserForm.vue';
import { dashboard } from '@/routes';
import type { UserItem } from '@/types';

const props = defineProps<{
    user: UserItem;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Setting',
                href: '/users',
            },
            {
                title: 'Users',
                href: '/users',
            },
            {
                title: 'Edit',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit ${user.name} - Setting`" />

    <div class="flex flex-col gap-6 p-6">
        <Heading
            :title="`Edit User: ${user.name}`"
            description="Update user account details or set a new password."
        />

        <div class="max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>User Details</CardTitle>
                    <CardDescription>
                        Update the name, email, or password for this account.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <UserForm
                        :user="user"
                        :submit-url="`/users/${user.id}`"
                        submit-method="put"
                    />
                </CardContent>
            </Card>
        </div>
    </div>
</template>
