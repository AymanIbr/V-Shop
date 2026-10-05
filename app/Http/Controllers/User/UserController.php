<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand', 'images'])
            ->where('is_published', true)
            ->latest()
            ->take(8)
            ->get();

        return Inertia::render('User/Index', [
            'products' => $products,
        ]);
    }
}
