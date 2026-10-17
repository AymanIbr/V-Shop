<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import UserLayout from './Layouts/UserLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
    items: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
    address: { type: Object, default: null },
    // addresses: { type: Array, default: () => [] },
})

const page = usePage()
const user = computed(() => page.props.auth.user)

// ---------- Helpers ----------
const money = (value) => `$${Number(value).toFixed(2)}`

const imageUrl = (product) =>
    product.images?.length ? `/custom/${product.images[0].image}` : null

const maxQuantity = (item) => Math.min(99, item.product.quantity)

const itemCount = computed(() => props.items.reduce((sum, i) => sum + i.quantity, 0))

// ---------- Cart actions ----------
const updateQuantity = (item, quantity) => {
    if (quantity < 1 || quantity > maxQuantity(item)) return

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

// ---------- Address form ----------
const showAddressForm = ref(false)

const form = useForm({
    address1: '',
    address2: '',
    city: '',
    state: '',
    zipcode: '',
    country_code: '',
    type: 'home',
    is_main: false,
})

const saveAddress = () => {
    form.post(route('addresses.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddressForm.value = false,
              Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: page.props.flash?.success,
                    timer: 2000,
                    showConfirmButton: false,
                })
            form.reset()
        },
    })
}

// ---------- Checkout ----------
const checkout = () => {
    router.post(route('checkout.store'), { address_id: props.address.id })
}

// ---------- Shared classes ----------
const inputClass = `w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm
    text-gray-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200
    dark:border-gray-600 dark:bg-gray-700 dark:text-white`

const labelClass = 'mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300'
const errorClass = 'mt-1 text-xs text-red-600'

const btnPrimary = `w-full rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white
    transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50`
</script>

<template>
    <Head title="Cart" />

    <UserLayout>
        <section class="mx-auto max-w-screen-xl px-4 pb-28 pt-10">
            <h1 class="mb-8 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                Shopping Cart
            </h1>

            <!-- Empty cart -->
            <div v-if="!items.length"
                 class="rounded-2xl border border-dashed border-gray-300 py-24 text-center dark:border-gray-600">
                <p class="text-gray-500 dark:text-gray-400">Your cart is empty.</p>
                <Link href="/"
                      class="mt-4 inline-block cursor-pointer rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800">
                    Continue shopping
                </Link>
            </div>

            <div v-else class="grid gap-8 lg:grid-cols-3">

                <!-- ============ Cart items ============ -->
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
                                                :disabled="item.quantity <= 1"
                                                class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700">
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
                                            class="cursor-pointer font-medium text-red-600 hover:underline dark:text-red-500">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ============ Order Summary ============ -->
                <aside class="sticky top-6 h-fit rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Order Summary</h2>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400">Total items</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ itemCount }}</dd>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <dt class="font-medium text-gray-900 dark:text-white">Total</dt>
                            <dd class="text-xl font-bold text-gray-900 dark:text-white">{{ money(total) }}</dd>
                        </div>
                    </dl>

                    <!-- ===== Authenticated user ===== -->
                    <template v-if="user">
                        <div class="border-t border-gray-200 pt-4 text-sm dark:border-gray-700">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="font-medium text-gray-900 dark:text-white">Shipping address</p>
                                <button v-if="address && !showAddressForm" type="button"
                                        @click="showAddressForm = true"
                                        class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                    Change
                                </button>
                            </div>

                            <!-- Saved address -->
                            <address v-if="address && !showAddressForm"
                                     class="rounded-lg bg-gray-50 p-4 not-italic text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ address.address1 }}<template v-if="address.address2">, {{ address.address2 }}</template>
                                </p>
                                <p class="mt-1">{{ address.city }}, {{ address.state }} {{ address.zipcode }}</p>
                                <p>{{ address.country_code }}</p>
                            </address>

                            <!-- Address form -->
                            <form v-else @submit.prevent="saveAddress" class="mt-3 space-y-4">
                                <div>
                                    <label for="address1" :class="labelClass">Address Line 1</label>
                                    <input id="address1" v-model.trim="form.address1" type="text"
                                           autocomplete="address-line1" :class="inputClass"
                                           placeholder="Street address" />
                                    <p v-if="form.errors.address1" :class="errorClass">{{ form.errors.address1 }}</p>
                                </div>

                                <div>
                                    <label for="address2" :class="labelClass">Address Line 2 <span class="text-gray-400">(optional)</span></label>
                                    <input id="address2" v-model.trim="form.address2" type="text"
                                           autocomplete="address-line2" :class="inputClass"
                                           placeholder="Apartment, suite, etc." />
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="city" :class="labelClass">City</label>
                                        <input id="city" v-model.trim="form.city" type="text"
                                               autocomplete="address-level2" :class="inputClass" placeholder="City" />
                                        <p v-if="form.errors.city" :class="errorClass">{{ form.errors.city }}</p>
                                    </div>
                                    <div>
                                        <label for="zipcode" :class="labelClass">Postal Code</label>
                                        <input id="zipcode" v-model.trim="form.zipcode" type="text"
                                               autocomplete="postal-code" :class="inputClass" placeholder="Postal code" />
                                        <p v-if="form.errors.zipcode" :class="errorClass">{{ form.errors.zipcode }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="state" :class="labelClass">State / Region</label>
                                        <input id="state" v-model.trim="form.state" type="text"
                                               autocomplete="address-level1" :class="inputClass" placeholder="State" />
                                        <p v-if="form.errors.state" :class="errorClass">{{ form.errors.state }}</p>
                                    </div>
                                    <div>
                                        <label for="country_code" :class="labelClass">Country</label>
                                        <input id="country_code" v-model.trim="form.country_code" type="text"
                                               autocomplete="country" maxlength="2" :class="inputClass"
                                               class="uppercase" placeholder="e.g. PS" />
                                        <p v-if="form.errors.country_code" :class="errorClass">{{ form.errors.country_code }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="type" :class="labelClass">Address Type</label>
                                    <select id="type" v-model="form.type" :class="inputClass">
                                        <option value="home">Home</option>
                                        <option value="work">Work</option>
                                    </select>
                                </div>

                                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <input v-model="form.is_main" type="checkbox"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    Set as my default address
                                </label>

                                <button type="submit" :disabled="form.processing" :class="btnPrimary">
                                    {{ form.processing ? 'Saving…' : 'Save Address' }}
                                </button>

                                <button v-if="address" type="button" @click="showAddressForm = false"
                                        class="w-full rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Cancel
                                </button>
                            </form>
                        </div>

                        <!-- Checkout -->
                        <button type="button" @click="checkout" :disabled="!address"
                                :class="btnPrimary" class="mt-5">
                            Proceed to Checkout
                        </button>
                    </template>

                    <!-- ===== Guest ===== -->
                    <div v-else class="border-t border-gray-200 pt-5 dark:border-gray-700">
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                            Please log in to continue with your order.
                        </p>
                        <Link :href="route('login')" :class="btnPrimary" class="block text-center">
                            Log in to Checkout
                        </Link>
                    </div>

                    <Link href="/"
                          class="mt-4 block text-center text-sm text-gray-500 hover:underline dark:text-gray-400">
                        Continue Shopping
                    </Link>
                </aside>
            </div>
        </section>
    </UserLayout>
</template>
