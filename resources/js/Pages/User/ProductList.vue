<script setup>
import { Head, router } from '@inertiajs/vue3';
import UserLayout from './Layouts/UserLayout.vue';
import { reactive, ref } from 'vue'

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },

    brands: {
        type: Array,
        default: () => [],
    },

    categories: {
        type: Array,
        default: () => [],
    },
    filters: { type: Object, default: () => ({}) },
});


const state = reactive({
    min_price: props.filters.min_price ?? '',
    max_price: props.filters.max_price ?? '',
    brands: (props.filters.brands ?? []).map(Number),
    categories: (props.filters.categories ?? []).map(Number),
    sort_by: props.filters.sort_by ?? 'newest',
})

const applyFilters = () => {
    router.get(route('productList.index'), {
        min_price: state.min_price || undefined,
        max_price: state.max_price || undefined,
        brands: state.brands.length ? state.brands : undefined,
        categories: state.categories.length ? state.categories : undefined,
        sort_by: state.sort_by !== 'newest' ? state.sort_by : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['products'],  //Partial Reload
    })
}

const clearFilters = () => {
    state.min_price = ''
    state.max_price = ''
    state.brands = []
    state.categories = []
    state.sort_by = 'newest'
    applyFilters()
}

// cart
import { useCart } from '@/Composables/useCart'
const { addToCart } = useCart()


const showBrands = ref(false)
const showCategories = ref(false)

</script>


<template>

    <Head title="Products List" />

    <UserLayout>
        <aside class="flex" aria-label="Product filters and results">

            <!-- Filters -->
            <div class="w-full max-w-[280px] shrink-0 border-r border-slate-300 px-4 py-6 md:px-6" role="region"
                aria-labelledby="filter-heading">

                <div class="mb-6 flex items-center border-b border-slate-300 pb-2">
                    <h2 id="filter-heading" class="text-lg font-semibold text-slate-900">
                        Filter
                    </h2>

                    <button type="button" @click="clearFilters"
                        class="ml-auto cursor-pointer rounded text-sm font-semibold text-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                        aria-label="Clear all filters">
                        Clear all
                    </button>
                </div>

                <!-- Price -->
                <fieldset>
                    <legend class="text-sm font-semibold text-slate-900 dark:text-white">Price</legend>

                    <div class="mt-4 flex items-center gap-2">
                        <input v-model="state.min_price" type="number" min="0" placeholder="Min"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />

                        <span class="text-gray-400">-</span>

                        <input v-model="state.max_price" type="number" min="0" placeholder="Max"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    </div>

                    <button type="button" @click="applyFilters"
                        class="mt-3 w-full rounded-lg cursor-pointer bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                        Apply
                    </button>
                </fieldset>
                <hr class="my-6 border-slate-300" />

                <!-- Brand -->
                <fieldset>
                    <legend class="w-full">
                        <button type="button" @click="showBrands = !showBrands"
                            class="flex w-full items-center justify-between text-sm font-semibold text-slate-900"
                            :aria-expanded="showBrands">
                            <span>Brand</span>

                            <span class="text-xl font-normal cursor-pointer text-slate-500">
                                {{ showBrands ? '−' : '+' }}
                            </span>
                        </button>
                    </legend>

                    <ul v-show="showBrands" class="mt-6 space-y-4" aria-label="Brand options">
                        <li v-for="brand in brands" :key="brand.id">
                            <label :for="`brand-${brand.id}`" class="group inline-flex items-center gap-2.5">
                                <input type="checkbox" class="sr-only" :id="`brand-${brand.id}`" v-model="state.brands"
                                    @change="applyFilters" :value="brand.id" />

                                <span
                                    class="flex h-4 w-4 shrink-0 items-center justify-center rounded bg-white outline-1 outline-slate-300 group-has-[input:checked]:bg-blue-600 group-has-[input:checked]:outline-blue-600 group-focus-within:outline-2 group-focus-within:outline-blue-600"
                                    aria-hidden="true">
                                    <svg class="size-3 text-white opacity-0 group-has-[input:checked]:opacity-100"
                                        viewBox="0 0 12 10" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 5l3 3 7-7" />
                                    </svg>
                                </span>

                                <span class="text-sm text-slate-900">
                                    {{ brand.name }}
                                </span>
                            </label>
                        </li>
                    </ul>
                </fieldset>

                <hr class="my-6 border-slate-300" />

                <!-- Category -->
                <fieldset>
                    <legend class="w-full">
                        <button type="button" @click="showCategories = !showCategories"
                            class="flex w-full items-center justify-between text-sm font-semibold text-slate-900"
                            :aria-expanded="showCategories">
                            <span>Category</span>

                            <span class="text-xl cursor-pointer font-normal text-slate-500">
                                {{ showCategories ? '−' : '+' }}
                            </span>
                        </button>
                    </legend>

                    <ul v-show="showCategories" class="mt-6 space-y-4" aria-label="Category options">
                        <li v-for="category in categories" :key="category.id">
                            <label :for="`category-${category.id}`" class="group inline-flex items-center gap-2.5">
                                <input type="checkbox" class="sr-only" :id="`category-${category.id}`"
                                    v-model="state.categories" @change="applyFilters" :value="category.id" />

                                <span
                                    class="flex h-4 w-4 shrink-0 items-center justify-center rounded bg-white outline-1 outline-slate-300 group-has-[input:checked]:bg-blue-600 group-has-[input:checked]:outline-blue-600 group-focus-within:outline-2 group-focus-within:outline-blue-600"
                                    aria-hidden="true">
                                    <svg class="size-3 text-white opacity-0 group-has-[input:checked]:opacity-100"
                                        viewBox="0 0 12 10" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 5l3 3 7-7" />
                                    </svg>
                                </span>

                                <span class="text-sm text-slate-900">
                                    {{ category.name }}
                                </span>
                            </label>
                        </li>
                    </ul>
                </fieldset>


            </div>


            <!-- Products -->
            <div class="w-full p-6" role="main" aria-label="Product results">

                <!-- Products Header / Sort -->
                <div
                    class="mb-6 flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">
                            Products
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Discover our latest products
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label for="sort" class="whitespace-nowrap text-sm font-medium text-slate-700">
                            Sort by:
                        </label>

                        <select v-model="state.sort_by" @change="applyFilters"
                            class="cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                            <option value="newest">
                                Newest
                            </option>

                            <option value="price_asc">
                                Price: Low to High
                            </option>

                            <option value="price_desc">
                                Price: High to Low
                            </option>
                        </select>
                    </div>

                </div>


                <!-- Products Grid -->
                <div class="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">

                    <article v-for="product in products.data" :key="product.id" class="group">

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
                                @click="addToCart(product)"
                                class="flex h-9 cursor-pointer w-9 shrink-0 items-center justify-center rounded-full bg-gray-900 text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-300 dark:bg-white dark:text-gray-900 dark:hover:bg-blue-500 dark:hover:text-white dark:disabled:bg-gray-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                </svg>
                            </button>
                        </div>
                    </article>


                    <!-- <div class="h-48 w-full rounded-md bg-gray-100" role="img" aria-label="Product"></div> -->

                </div>

            </div>

        </aside>
    </UserLayout>
</template>

<!--
category list  => https://readymadeui.com/tailwind-ecommerce/product-filter
-->
