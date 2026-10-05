<template>

    <Head title="Admin Dashboard" />


    <div class="antialiased bg-gray-50 dark:bg-gray-900">


        <!-- Navbar start -->

        <Navbar />

        <!-- Navbar end -->


        <!-- Sidebar start -->
        <Sidebar />
        <!-- Sidebar end -->

        <main class="p-4 md:ml-64 h-auto pt-20">
            <slot />
        </main>

    </div>


</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import Navbar from './Navbar.vue';
import Sidebar from './Sidebar.vue';

import { onMounted } from 'vue'
import { initFlowbite } from 'flowbite'

onMounted(() => {
    initFlowbite();
    router.on('navigate', () => setTimeout(initFlowbite, 50))
})

</script>
