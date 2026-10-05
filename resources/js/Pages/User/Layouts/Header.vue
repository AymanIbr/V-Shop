<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage()
// const canRegister = computed(() => page.props.canRegister);
// const canLogin = computed(() => page.props.canLogin);
const auth = computed(() => page.props.auth);

</script>

<template>

    <nav class="bg-white border-gray-200 dark:bg-gray-900">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            <Link href="/" class="flex items-center">
                <span class="text-2xl font-semibold dark:text-white">V.Shop</span>
            </Link>
            <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">

                <!-- Shopping Cart -->
                <Link class="relative flex items-center mr-4 justify-center w-10 h-10
           text-gray-600 hover:text-blue-600
           rounded-lg hover:bg-gray-100
           transition-all duration-200
           dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700" aria-label="Shopping Cart">
                    <!-- Cart Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h12.75l3-9H5.106M7.5 14.25L5.106 5.272M7.5 14.25h10.5m-9 3.75a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm9 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                    </svg>

                    <!-- Cart Count -->
                    <span class="absolute -top-1 -right-1
               min-w-[20px] h-5 px-1
               flex items-center justify-center
               rounded-full
               bg-red-500 text-white
               text-xs font-bold
               ring-2 ring-white dark:ring-gray-900">
                        5
                    </span>
                </Link>


                <!-- user login -->
                <template v-if="auth.user">
                    <button type="button" id="user-menu-button" data-dropdown-toggle="user-dropdown"
                        data-dropdown-placement="bottom-end" aria-expanded="false"
                        class="flex text-sm cursor-pointer rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600">
                        <span class="sr-only">Open user menu</span>
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-700 text-sm font-medium uppercase text-white">
                            {{ auth.user.name?.charAt(0) }}
                        </span>
                    </button>

                    <!-- Dropdown menu -->
                    <div id="user-dropdown"
                        class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm dark:bg-gray-700 dark:divide-gray-600">
                        <div class="px-4 py-3">
                            <span class="block text-sm text-gray-900 dark:text-white">{{ auth.user.name }}</span>
                            <span class="block text-sm text-gray-500 truncate dark:text-gray-400">{{ auth.user.email
                                }}</span>
                        </div>
                        <ul class="py-2" aria-labelledby="user-menu-button">
                            <li>
                                <Link :href="route('profile.edit')"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                    Profile
                                </Link>
                            </li>
                            <li v-if="auth.user.isAdmin">
                                <Link :href="route('admin.dashboard')"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                    Dashboard
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('logout')" method="post" as="button"
                                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                    Sign out
                                </Link>
                            </li>
                        </ul>
                    </div>
                </template>

                <div v-else class="flex items-center gap-3">
                    <!-- Login -->
                    <Link :href="route('login')" class="px-5 py-2.5 text-sm font-medium text-gray-700
               bg-white border border-gray-300 rounded-lg
               hover:bg-gray-50 hover:text-blue-700
               focus:outline-none focus:ring-4 focus:ring-gray-100
               transition-all duration-200">
                        Log in
                    </Link>

                    <!-- Register -->
                    <Link :href="route('register')" class="px-5 py-2.5 text-sm font-medium text-white
               bg-blue-600 rounded-lg
               hover:bg-blue-700
               focus:outline-none focus:ring-4 focus:ring-blue-300
               shadow-sm hover:shadow-md
               transition-all duration-200">
                        Register
                    </Link>
                </div>


                <!-- Mobile Nav bar -->
                <button data-collapse-toggle="navbar-user" type="button"
                    class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                    aria-controls="navbar-user" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 17 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 1h15M1 7h15M1 13h15" />
                    </svg>
                </button>
            </div>

            <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-user">
                <ul
                    class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                    <li>
                        <a href="#"
                            class="block py-2 px-3 text-white bg-blue-700 rounded-sm md:bg-transparent md:text-blue-700 md:p-0 md:dark:text-blue-500"
                            aria-current="page">Home</a>
                    </li>
                    <li>
                        <a href="#"
                            class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">About</a>
                    </li>
                    <li>
                        <a href="#"
                            class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Services</a>
                    </li>
                    <li>
                        <a href="#"
                            class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Pricing</a>
                    </li>
                    <li>
                        <a href="#"
                            class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

</template>
