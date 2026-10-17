<script setup>
import { Head } from '@inertiajs/vue3';
import UserLayout from './Layouts/UserLayout.vue';


const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
})

// console.log(props.orders);
const money = (value) => {
    return `$${Number(value ?? 0).toFixed(2)}`;
};

</script>

<template>

    <Head title="MyOrders" />

    <UserLayout>

        <div class="relative max-w-screen-xl px-4 py-16 mx-auto overflow-x-auto">

            <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                My Orders
            </h1>

            <table class="w-full text-sm text-left text-gray-500 rtl:text-right dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-4">Order #</th>
                        <th scope="col" class="px-6 py-4">Products</th>
                        <!-- <th scope="col" class="px-6 py-4">Brand</th>
                        <th scope="col" class="px-6 py-4">Category</th> -->
                        <th scope="col" class="px-6 py-4">Total</th>
                        <th scope="col" class="px-6 py-4">Status</th>
                        <th scope="col" class="px-6 py-4">Date</th>
                        <th scope="col" class="px-6 py-4">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="order in orders" :key="order.id"
                        class="bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            #{{ order.id }}
                        </td>

                        <td class="px-6 py-4">
                            <div v-for="item in order.items" :key="item.id" class="mb-1">
                                {{ item.product?.title ?? 'Product unavailable' }}
                                <span class="text-gray-400">
                                    (x{{ item.quantity }})
                                </span>
                            </div>
                        </td>

                        <!-- <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                            {{ order.items[0]?.product?.brand?.name ?? 'N/A' }}
                        </td> -->

                        <!-- <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                            {{ order.items[0]?.product?.category?.name ?? 'N/A' }}
                        </td> -->

                        <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                            {{ money(order.total_price) }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full" :class="{
                                'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                'bg-green-100 text-green-800': order.status === 'paid',
                                'bg-red-100 text-red-800': order.status === 'cancelled',
                                'bg-gray-100 text-gray-800': !['pending', 'paid', 'cancelled'].includes(order.status),
                            }">
                                {{ order.status }}
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ order.created_at }}
                        </td>

                        <td class="px-6 py-4">
                            <Link href="" class="font-medium text-blue-600 hover:underline dark:text-blue-500">
                            View Details
                            </Link>
                        </td>
                    </tr>

                    <tr v-if="orders.length === 0">
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            You haven't placed any orders yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </UserLayout>

</template>
