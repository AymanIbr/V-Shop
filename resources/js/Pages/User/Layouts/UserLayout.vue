<script setup>

import { onMounted } from 'vue'
import { initFlowbite } from 'flowbite'
import Header from './Header.vue';
import Footer from './Footer.vue';

onMounted(() => {
    initFlowbite();
})


</script>


<template>

    <!-- Header -->
    <Header />
    <!-- end header -->


    <!-- Main Content -->
    <slot />
    <!-- End Main Content -->

    <!-- footer -->
    <Footer />
    <!-- end footer -->


</template>
