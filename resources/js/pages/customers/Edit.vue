<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { edit, index, update } from '@/actions/App/Http/Controllers/CustomerController';
import Heading from '@/components/Heading.vue';
import { dashboard } from '@/routes';
import type { Customer } from '@/types';
import CustomerForm from './components/CustomerForm.vue';

const props = defineProps<{
    customer: Customer;
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
                href: '/customers',
            },
            {
                title: 'Customers',
                href: index.url(),
            },
            {
                title: 'Edit Customer',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit Customer - ${customer.name}`" />

    <div class="flex flex-col gap-6 p-6 max-w-4xl">
        <Heading
            :title="`Edit Customer: ${customer.name}`"
            description="Update contact information, company, and account status."
        />

        <div class="rounded-xl border bg-card p-6 shadow-xs">
            <CustomerForm
                :customer="customer"
                :submit-url="update.url(customer.id)"
                submit-method="put"
            />
        </div>
    </div>
</template>
