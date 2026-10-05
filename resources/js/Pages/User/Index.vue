<script setup>
import { Head } from '@inertiajs/vue3'
import UserLayout from './Layouts/UserLayout.vue';

defineProps({
    products: {
        type: Array,
        required: true
    }
})

</script>


<template>

    <Head title="Home" />

    <UserLayout>

        <main class="max-w-screen-xl mx-auto px-4 pt-10 pb-28">

            <header class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Latest Products list</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ products.length }} items</p>
            </header>

            <div v-if="!products.length" class="py-24 text-center text-gray-500 dark:text-gray-400">
                No products available yet.
            </div>

            <div v-else class="grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 lg:grid-cols-4">
                <article v-for="product in products" :key="product.id" class="group">

                    <!-- Image -->
                    <div class="relative aspect-square overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-800">
                        <img v-if="product.images?.length" :src="`/custom/${product.images[0].image}`"
                            :alt="product.title"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            :class="{ 'opacity-50': !product.in_stock }" />
                        <div v-else class="flex h-full items-center justify-center text-sm text-gray-400">
                            No image
                        </div>

                        <span v-if="!product.in_stock"
                            class="absolute top-3 start-3 rounded-full bg-gray-900/80 px-3 py-1 text-xs font-medium text-white">
                            Sold out
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="mt-4 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ product.brand?.name }}
                            </p>
                            <h3 class="mt-0.5 truncate text-sm font-medium text-gray-900 dark:text-white">
                                {{ product.title }}
                            </h3>
                            <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                                ${{ Number(product.price).toFixed(2) }}
                            </p>
                        </div>

                        <button type="button" :disabled="!product.in_stock" aria-label="Add to cart"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-900 text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-300 dark:bg-white dark:text-gray-900 dark:hover:bg-blue-500 dark:hover:text-white dark:disabled:bg-gray-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                        </button>
                    </div>
                </article>
            </div>
        </main>

    </UserLayout>


</template>
