<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items.product.brand','items.product.category'])->get();
        return Inertia::render('User/Dashboard', [
            'orders' => $orders
        ]);
    }
}
