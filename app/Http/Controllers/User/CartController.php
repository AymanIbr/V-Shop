<?php

namespace App\Http\Controllers\User;

use App\Models\Product;
use App\Models\UserAddress;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController
{
    public function __construct(private CartService $cart) {}

    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('User/CartList', [
            'items'     => $this->cart->getCartItems()->values(),
            'total'     => $this->cart->getTotal(),
            'address'   => $user?->addresses()->where('is_main', true)->first(),
            'addresses' => $user?->addresses()->latest()->get() ?? [],
        ]);
    }

    public function store(Request $request, Product $product)
    {
        abort_unless($product->is_published && $product->in_stock, 404);

        $data = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ]);

        $this->cart->add($product, $data['quantity'] ?? 1);

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->setQuantity($product, $data['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Product $product)
    {
        $this->cart->remove($product);

        return back()->with('success', 'Item removed from cart.');
    }
}
