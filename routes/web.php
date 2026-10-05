<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProfileController;
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



Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
