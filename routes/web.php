<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ProductListController;
use App\Http\Controllers\User\UserAddressController;
use App\Http\Controllers\User\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


// user route

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });


Route::get('/', [UserController::class, 'index'])->name('user.home');
Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('{product}', 'store')->name('store');
    Route::patch('{product}', 'update')->name('update');
    Route::delete('{product}', 'destroy')->name('destroy');
});

// Route for Product List & Filter
Route::prefix('products')->controller(ProductListController::class)->group(function () {
    Route::get('/', 'index')->name('productList.index');
});


// routes/web.php
Route::middleware('auth')->group(function () {
    Route::post('/addresses', [UserAddressController::class, 'store'])->name('addresses.store');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel/{order}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
});
Route::post('/stripe/webhook', [CheckoutController::class, 'webhook'])->name('stripe.webhook');


// user dashboard
Route::get('/dashboard',[DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// end


// admin route

Route::group(['prefix' => 'admin', 'middleware' => 'redirectAdmin'], function () {
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.login.post');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/publish', [ProductController::class, 'togglePublished'])
        ->name('products.publish');
    Route::patch('products/{product}/stock', [ProductController::class, 'toggleStock'])
        ->name('products.stock');
});

// end


require __DIR__ . '/auth.php';



// download breeze vue
// composer require laravel/breeze --dev
// php artisan breeze:install vue

//  Multiple Authentication and Admin Dashboard User Interface
// 1- add isAdmin in users table
// 2- make middleware =>  php artisan make:middleware AdminMiddleware
// 3- go to app.php & make middleware as name aliases
// 4- make middleware redirectAdmin
// 5- make AdminAuthController
// 6- make folder Admin\Auth\Login.vue


//change any input table
// php artisan tinker
// DB::table('users')->where('email', 'admin@gmail.com')->update(['isAdmin' => 1]);



// make home page
// php artisan make:controller User/UserController


// make dashboard user
// 1- php artisan make:controller User/DashboardController
// 2- make method index
// 3- make page Dashboard inside User Folder
// 4- update route dashboard



// play list
// https://www.youtube.com/watch?v=3v-TdjdvEIg&list=PLRQ1njZomKvE4XJQjdrYuyKMw6-0-6e6x&index=16
