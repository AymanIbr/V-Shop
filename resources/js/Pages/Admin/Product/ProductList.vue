<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import { Plus } from '@element-plus/icons-vue'

// const  products = usePage().props.products;
const page = usePage();

defineProps({
    products: Array,
    categories: Array,
    brands: Array,
})
//https://element-plus.org/   Element Plus مكتبة مكوّنات واجهات (UI components) جاهزة لـ Vue 3، وهي النسخة المحدّثة من Element UI التي كانت لـ Vue 2.
// 1- npm install element-plus --save
// 2- import 'element-plus/dist/index.css'  في ملف main.js

// use vue sweetalert2
//1- npm install -S vue-sweetalert2
// 2- import 'sweetalert2/dist/sweetalert2.min.css'  في ملف app.js



const form = useForm({
    title: '',
    description: '',
    price: '',
    quantity: 0,
    category_id: '',
    brand_id: '',
    is_published: false,
    // in_stock: false,
    // images: null,

    // update image
    images: [],
    deleted_images: [],
});

const errors = computed(() => form.errors);

// open add product modal
const isAddProduct = ref(false);
const dialogVisible = ref(false);
const editMode = ref(false);
const editingId = ref(null)

const openAddModal = () => {
    isAddProduct.value = true;

    editMode.value = false;
    // resetProductImage
    resetImages()
    dialogVisible.value = true;

}

const openEditModal = (product) => {
    // console.log('Edit product:', product);

    isAddProduct.value = false;
    dialogVisible.value = true;

    editMode.value = true;
    editingId.value = product.id
    form.clearErrors()

    form.title = product.title
    form.description = product.description ?? ''
    form.price = product.price
    form.quantity = product.quantity
    form.category_id = product.category_id ?? ''
    form.brand_id = product.brand_id ?? ''
    form.is_published = product.is_published
    // form.images = []

    // update product images
    form.images = [];
    form.deleted_images = [];

    // use display old images in the upload component
    productImages.value = (product.images ?? []).map(image => ({
        uid: `old-${image.id}`,
        id: image.id,
        name: image.image,
        url: `/custom/${image.image}`,
        status: 'success',
        isOld: true,
    }));

}

const resetProductForm = () => {
    form.reset();
    form.clearErrors();

    form.title = '';
    form.description = '';
    form.price = '';
    form.quantity = 0;
    form.category_id = '';
    form.brand_id = '';
    form.is_published = false;
    form.images = null;

    editingId.value = null;
    editMode.value = false;
    isAddProduct.value = false;
    // resetProductImage
    resetImages()
};


const submit = () => {
    const options = {
        forceFormData: true,
        onSuccess: () => {
            dialogVisible.value = false
            form.reset()
            resetProductForm()

            // SweetAlert
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                icon: 'success',
                text: page.props.flash.success,
            });
        },
    }

    if (editMode.value) {
        form.transform((data) => ({ ...data, _method: 'put' }))
            .post(route('admin.products.update', editingId.value), options)
    } else {
        form.post(route('admin.products.store'), options)
    }
};

// upload multiple image
const productImages = ref([])
const previewVisible = ref(false)
const previewUrl = ref('')

// const syncImages = (_file, files) => {
//     form.images = files.filter((f) => f.raw).map((f) => f.raw)
// }

const syncImages = (_file, files) => {
    form.images = files
        .filter(file => file.raw && !file.isOld)
        .map(file => file.raw);
};

const handleImageRemove = (file) => {
    if (file.isOld && file.id) {
        form.deleted_images.push(file.id);
    }
    syncImages(null, productImages.value);
};


const handlePictureCardPreview = (file) => {
    previewUrl.value = file.url
    previewVisible.value = true
}

const resetImages = () => {
    productImages.value = []
    form.images = []
}

// toggle product published & stock

const togglePublished = (product) =>
    router.patch(route('admin.products.publish', product.id), {}, { preserveScroll: true })

const toggleStock = (product) =>
    router.patch(route('admin.products.stock', product.id), {}, { preserveScroll: true })


    // delete product

    const deleteProduct = (product) => {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                router.delete(route('admin.products.destroy', product.id), {
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire(
                            'Deleted!',
                            'Product has been deleted.',
                            'success'
                        )
                    }
                })
            }
        })
    }


</script>

<template>

    <!-- Dialog Add or Edit -->

    <!-- Product Add / Edit Dialog -->
    <el-dialog v-model="dialogVisible" :title="editMode ? 'Edit Product' : 'Add Product'" width="650px"
        destroy-on-close>
        <!-- Start form flow bite -->
        <form @submit.prevent="submit">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Product title
                    </label>

                    <input id="title" type="text" v-model="form.title"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Enter product title" />

                    <span v-if="errors.title" class="text-red-500 text-sm mt-1 block">
                        {{ errors.title }}
                    </span>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Description
                    </label>

                    <textarea id="description" v-model="form.description" rows="4"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Enter product description"></textarea>

                    <span v-if="errors.description" class="text-red-500 text-sm mt-1 block">
                        {{ errors.description }}
                    </span>
                </div>

                <!-- Price -->
                <div>
                    <label for="price" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Price
                    </label>

                    <input id="price" type="number" step="0.01" min="0" v-model="form.price"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="0.00" />

                    <span v-if="errors.price" class="text-red-500 text-sm mt-1 block">
                        {{ errors.price }}
                    </span>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Quantity
                    </label>

                    <input id="quantity" type="number" min="0" v-model="form.quantity"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="0" />

                    <span v-if="errors.quantity" class="text-red-500 text-sm mt-1 block">
                        {{ errors.quantity }}
                    </span>
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Category
                    </label>

                    <select id="category_id" v-model="form.category_id"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select category</option>

                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>

                    <span v-if="errors.category_id" class="text-red-500 text-sm mt-1 block">
                        {{ errors.category_id }}
                    </span>
                </div>

                <!-- Brand -->
                <div>
                    <label for="brand_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Brand
                    </label>

                    <select id="brand_id" v-model="form.brand_id"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select brand</option>

                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                            {{ brand.name }}
                        </option>
                    </select>

                    <span v-if="errors.brand_id" class="text-red-500 text-sm mt-1 block">
                        {{ errors.brand_id }}
                    </span>
                </div>

                <!-- Images -->
                <div class="md:col-span-2">
                    <label for="images" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Product images
                    </label>

                    <!-- Element plus -->

                    <el-upload v-model:file-list="productImages" list-type="picture-card" :auto-upload="false" multiple
                        :limit="8" accept="image/jpeg,image/png,image/webp" :on-change="syncImages"
                        :on-remove="handleImageRemove" :on-preview="handlePictureCardPreview">
                        <el-icon>
                            <Plus />
                        </el-icon>
                    </el-upload>

                    <el-dialog v-model="previewVisible" width="500" append-to-body>
                        <img class="w-full" :src="previewUrl" alt="Preview" /> <Plus />
                    </el-dialog>

                    <!-- <input id="images" type="file" multiple accept="image/*"
                        @change="form.images = [...$event.target.files]"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600" /> -->

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        You can select multiple images.
                    </p>

                    <span v-if="errors.images" class="text-red-500 text-sm mt-1 block">
                        {{ errors.images }}
                    </span>

                    <!-- Image validation errors -->
                    <span v-if="errors['images.0']" class="text-red-500 text-sm mt-1 block">
                        {{ errors['images.0'] }}
                    </span>
                </div>

                <!-- Published -->
                <div class="md:col-span-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="form.is_published" class="sr-only peer">

                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer
                        peer-focus:ring-4 peer-focus:ring-blue-300
                        dark:peer-focus:ring-blue-800
                        dark:bg-gray-700
                        peer-checked:after:translate-x-full
                        peer-checked:after:border-white
                        after:content-['']
                        after:absolute
                        after:top-0.5
                        after:left-[2px]
                        after:bg-white
                        after:border-gray-300
                        after:border
                        after:rounded-full
                        after:h-5
                        after:w-5
                        after:transition-all
                        dark:border-gray-600
                        peer-checked:bg-blue-600"></div>

                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                            Publish product
                        </span>
                    </label>

                    <span v-if="errors.is_published" class="text-red-500 text-sm mt-1 block">
                        {{ errors.is_published }}
                    </span>
                </div>

            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="dialogVisible = false; resetProductForm()"
                    class="text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 font-medium rounded-lg text-sm px-4 py-2.5">
                    Cancel
                </button>

                <button type="submit" :disabled="form.processing"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 cursor-pointer focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2.5 disabled:opacity-50">
                    {{ form.processing ? 'Saving...' : (editMode ? 'Update Product' : 'Add Product') }}
                </button>
            </div>
        </form>

        <!-- End Form -->

    </el-dialog>


    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div class="mx-auto max-w-screen-xl px-4 lg:px-8">
            <!-- Start coding here -->
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div
                    class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                    <div class="w-full md:w-1/2">
                        <form class="flex items-center">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                        fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" id="simple-search"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Search" required="">
                            </div>
                        </form>
                    </div>
                    <div
                        class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                        <button type="button" @click="openAddModal"
                            class="flex items-center cursor-pointer justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                            <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewbox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                            </svg>
                            Add product
                        </button>
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <button id="actionsDropdownButton" data-dropdown-toggle="actionsDropdown"
                                class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                                type="button">
                                <svg class="-ml-1 mr-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                                Actions
                            </button>
                            <div id="actionsDropdown"
                                class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                                    aria-labelledby="actionsDropdownButton">
                                    <li>
                                        <a href="#"
                                            class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Mass
                                            Edit</a>
                                    </li>
                                </ul>
                                <div class="py-1">
                                    <a href="#"
                                        class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Delete
                                        all</a>
                                </div>
                            </div>
                            <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown"
                                class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                                type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                    class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                        clip-rule="evenodd" />
                                </svg>
                                Filter
                                <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </button>
                            <div id="filterDropdown"
                                class="z-10 hidden w-48 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                                <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Choose brand</h6>
                                <ul class="space-y-2 text-sm" aria-labelledby="filterDropdownButton">
                                    <li class="flex items-center">
                                        <input id="apple" type="checkbox" value=""
                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="apple"
                                            class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Apple
                                            (56)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="fitbit" type="checkbox" value=""
                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="fitbit"
                                            class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Microsoft
                                            (16)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="razor" type="checkbox" value=""
                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="razor"
                                            class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Razor
                                            (49)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="nikon" type="checkbox" value=""
                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="nikon"
                                            class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Nikon
                                            (12)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="benq" type="checkbox" value=""
                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="benq"
                                            class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">BenQ
                                            (74)</label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">Product image</th>
                                <th scope="col" class="px-4 py-3">Product name</th>
                                <th scope="col" class="px-4 py-3">Category</th>
                                <th scope="col" class="px-4 py-3">Brand</th>
                                <th scope="col" class="px-4 py-3">Quantity</th>
                                <th scope="col" class="px-4 py-3">Stock</th>
                                <th scope="col" class="px-4 py-3">Publish</th>
                                <th scope="col" class="px-4 py-3">Price</th>
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody  v-if="products?.length">
                            <tr v-for="product in products" :key="product.id"
                                class="border-b border-b-gray-300 dark:border-gray-700">
                                <td class="px-4 py-3">
                                    <img v-if="product.images?.length" :src="`/custom/${product.images[0].image}`"
                                        :alt="product.title" class="w-12 h-12 object-cover rounded-lg" />
                                    <span v-else class="text-gray-400 text-xs">No image</span>
                                </td>
                                <th scope="row"
                                    class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ product.title }}
                                </th>
                                <td class="px-4 py-3">{{ product.category?.name }}</td>
                                <td class="px-4 py-3">{{ product.brand?.name }}</td>
                                <td class="px-4 py-3">{{ product.quantity }}</td>
                                <td class="px-4 py-3">
                                    <button type="button" @click="toggleStock(product)" :class="product.in_stock
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'"
                                        class="text-xs font-medium px-2.5 py-0.5 rounded-full hover:opacity-80 cursor-pointer">
                                        {{ product.in_stock ? 'In stock' : 'Out of stock' }}
                                    </button>
                                </td>

                                <td class="px-4 py-3">
                                    <button type="button" @click="togglePublished(product)" :class="product.is_published
                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                                        class="text-xs font-medium px-2.5 py-0.5 rounded-full hover:opacity-80 cursor-pointer">
                                        {{ product.is_published ? 'Published' : 'Draft' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3">${{ Number(product.price).toFixed(2) }}</td>
                                <td class="px-4 py-3 flex items-center justify-end">
                                    <button :id="`${product.id}-button`" :data-dropdown-toggle="`${product.id}`"
                                        class="inline-flex items-center cursor-pointer p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                                        type="button">
                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                        </svg>
                                    </button>
                                    <div :id="`${product.id}`"
                                        class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                                            :aria-labelledby="`${product.id}-button`">
                                            <li class="hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                                <button
                                                    class="block py-2 px-4 dark:hover:text-white">Show</button>
                                            </li>
                                            <li class="hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                                <button @click="openEditModal(product)"
                                                    class="block py-2 px-4 dark:hover:text-white cursor-pointer">Edit</button>
                                            </li>
                                        </ul>
                                        <div class="py-1 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                            <button @click="deleteProduct(product)"
                                                class="block py-2 px-4 text-sm text-gray-700 dark:text-gray-200 dark:hover:text-white cursor-pointer">Delete</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="9" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No products found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <nav class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                        Showing
                        <span class="font-semibold text-gray-900 dark:text-white">1-10</span>
                        of
                        <span class="font-semibold text-gray-900 dark:text-white">1000</span>
                    </span>
                    <ul class="inline-flex items-stretch -space-x-px">
                        <li>
                            <a href="#"
                                class="flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <span class="sr-only">Previous</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                class="flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">1</a>
                        </li>
                        <li>
                            <a href="#"
                                class="flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">2</a>
                        </li>
                        <li>
                            <a href="#" aria-current="page"
                                class="flex items-center justify-center text-sm z-10 py-2 px-3 leading-tight text-blue-600 bg-blue-50 border border-blue-300 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">3</a>
                        </li>
                        <li>
                            <a href="#"
                                class="flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">...</a>
                        </li>
                        <li>
                            <a href="#"
                                class="flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">100</a>
                        </li>
                        <li>
                            <a href="#"
                                class="flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <span class="sr-only">Next</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>

</template>
