<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { create, index, store } from '@/actions/App/Http/Controllers/CustomerController';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import CustomerForm from './components/CustomerForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Setting',
                href: '/customers',
            },
            {
                title: 'Customers',
                href: index.url(),
            },
            {
                title: 'Create Customer',
                href: create.url(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Create Customer" />

    <div class="flex flex-col gap-6 p-6 max-w-4xl">
        <Heading
            title="Create Customer"
            description="Add a new customer to your database with their contact details and status."
        />

        <div class="rounded-xl border bg-card p-6 shadow-xs">
            <CustomerForm
                :submit-url="store.url()"
                submit-method="post"
            />
        </div>
    </div>
</template>
