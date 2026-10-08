<script setup>
import { computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import UserLayout from './Layouts/UserLayout.vue'
import Swal from 'sweetalert2';

defineProps({
    items: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
    address: { type: Object, default: null },
    addresses: { type: Array, default: () => [] },
})

const page = usePage()
const user = computed(() => page.props.auth.user)

const money = (value) => `$${Number(value).toFixed(2)}`

const imageUrl = (product) =>
    product.images?.length ? `/custom/${product.images[0].image}` : null

const maxQuantity = (item) => Math.min(99, item.product.quantity)

const updateQuantity = (item, quantity) => {
    if (quantity > maxQuantity(item)) return

    router.patch(route('cart.update', item.product.id), { quantity }, {
        preserveScroll: true,
    })
}

const remove = (item) => {
    Swal.fire({
        title: 'Remove this item?',
        text: item.product.title,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, remove it',
    }).then((result) => {
        if (!result.isConfirmed) return

        router.delete(route('cart.destroy', item.product.id), {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: page.props.flash?.success ?? 'Item removed from cart.',
                    timer: 2000,
                    showConfirmButton: false,
                })
            },
        })
    })
}
</script>

<template>

    <Head title="Cart" />

    <UserLayout>
        <section class="mx-auto max-w-screen-xl px-4 pb-28 pt-10">

            <h1 class="mb-8 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                Shopping Cart
            </h1>

            <div v-if="!items.length"
                class="rounded-2xl border border-dashed border-gray-300 py-24 text-center dark:border-gray-600">
                <p class="text-gray-500 dark:text-gray-400">Your cart is empty.</p>
                <Link href="/"
                    class="mt-4 inline-block rounded-lg cursor-pointer bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800">
                    Continue shopping
                </Link>
            </div>

            <div v-else class="grid gap-8 lg:grid-cols-3">

                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 lg:col-span-2">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3"><span class="sr-only">Image</span></th>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3">Qty</th>
                                <th class="px-4 py-3">Price</th>
                                <th class="px-4 py-3">Subtotal</th>
                                <th class="px-4 py-3"><span class="sr-only">Action</span></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="item in items" :key="item.product.id"
                                class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">

                                <td class="p-4">
                                    <img v-if="imageUrl(item.product)" :src="imageUrl(item.product)"
                                        :alt="item.product.title" class="h-16 w-16 rounded-lg object-cover" />
                                    <div v-else
                                        class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400 dark:bg-gray-700">
                                        No image
                                    </div>
                                </td>

                                <td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">
                                    {{ item.product.title }}
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <button type="button" aria-label="Decrease"
                                            @click="updateQuantity(item, item.quantity - 1)"
                                            class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700">
                                            −
                                        </button>

                                        <span class="w-8 text-center font-medium text-gray-900 dark:text-white">
                                            {{ item.quantity }}
                                        </span>

                                        <button type="button" aria-label="Increase"
                                            @click="updateQuantity(item, item.quantity + 1)"
                                            :disabled="item.quantity >= maxQuantity(item)"
                                            class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700">
                                            +
                                        </button>
                                    </div>
                                </td>

                                <td class="px-4 py-4">{{ money(item.product.price) }}</td>

                                <td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">
                                    {{ money(item.subtotal) }}
                                </td>

                                <td class="px-4 py-4">
                                    <button type="button" @click="remove(item)"
                                        class="font-medium cursor-pointer text-red-600 hover:underline dark:text-red-500">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <aside
                    class="h-fit rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">

                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Summary</h2>

                    <div
                        class="flex items-center justify-between border-b border-gray-200 pb-4 text-sm dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">Items</span>
                        <span class="text-gray-900 dark:text-white">
                            {{items.reduce((sum, item) => sum + item.quantity, 0)}}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-4">
                        <span class="font-medium text-gray-900 dark:text-white">Total</span>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">{{ money(total) }}</span>
                    </div>

                    <div v-if="user" class="mb-5 border-t border-gray-200 pt-4 text-sm dark:border-gray-700">
                        <p class="mb-1 font-medium text-gray-900 dark:text-white">Shipping address</p>

                        <div v-if="address" class="text-gray-600 dark:text-gray-400">
                            <p>
                                {{ address.address1 }}<span v-if="address.address2">, {{ address.address2 }}</span>
                            </p>
                            <p>{{ address.city }}, {{ address.zipcode }} ({{ address.country_code }})</p>
                        </div>
                        <p v-else class="text-gray-500 dark:text-gray-400">No shipping address yet.</p>

                    </div>

                    <button v-if="user" type="button" :disabled="!address"
                        class="w-full rounded-lg bg-blue-700 px-5 py-3 text-sm font-medium text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50">
                        Checkout
                    </button>

                    <Link v-else :href="route('login')"
                        class="block w-full rounded-lg bg-blue-700 px-5 py-3 text-center text-sm font-medium text-white hover:bg-blue-800">
                        Log in to checkout
                    </Link>

                    <Link href="/"
                        class="mt-3 block text-center text-sm text-gray-500 hover:underline dark:text-gray-400">
                        Continue shopping
                    </Link>
                </aside>
            </div>
        </section>
    </UserLayout>
</template>
