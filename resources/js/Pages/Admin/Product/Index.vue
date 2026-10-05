<template>

    <AdminLayout>

        <Head title="Products" />
        <ProductList :products="products" :categories="categories" :brands="brands"></ProductList>
    </AdminLayout>

</template>

<script setup>
import AdminLayout from '../Components/AdminLayout.vue';
import { Head } from '@inertiajs/vue3'
import ProductList from './ProductList.vue';


defineProps({
    products: Array,
    categories: Array,
    brands: Array,
});


</script>
