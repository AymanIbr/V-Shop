<script setup>
import { Head } from '@inertiajs/vue3'
import UserLayout from './Layouts/UserLayout.vue'

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
})


</script>

<template>

    <Head title="Order Successful" />

    <UserLayout>
        <section class="mx-auto max-w-screen-xl px-4 py-10">

            <!-- Success Message -->
            <div class="mx-auto mb-8 max-w-3xl rounded-2xl border border-green-200
                        bg-green-50 p-8 text-center dark:border-green-800
                        dark:bg-green-950/30">

                <div class="mx-auto flex h-16 w-16 items-center justify-center
                            rounded-full bg-green-100 text-green-600
                            dark:bg-green-900 dark:text-green-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                    Payment Successful!
                </h1>

                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    Thank you for your order. Your payment has been confirmed.
                </p>

                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    <p>Order #{{ order.id }}</p>

                    <div v-for="item in order.items" :key="item.id" class="mt-1">
                        {{ item.product?.title ?? 'Product unavailable' }}
                    </div>
                </div>
            </div>

        </section>
    </UserLayout>
</template>
