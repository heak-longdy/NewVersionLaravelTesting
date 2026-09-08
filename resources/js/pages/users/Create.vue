<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import UserForm from '@/pages/users/components/UserForm.vue';
import { dashboard } from '@/routes';

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
                title: 'Create',
                href: '/users/create',
            },
        ],
    },
});
</script>

<template>
    <Head title="Create User - Setting" />

    <div class="flex flex-col gap-6 p-6">
        <Heading
            title="Create User"
            description="Add a new user to the application with email and secure password."
        />

        <div class="max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>User Details</CardTitle>
                    <CardDescription>
                        Fill in the required information to set up the user account.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <UserForm
                        submit-url="/users"
                        submit-method="post"
                    />
                </CardContent>
            </Card>
        </div>
    </div>
</template>
