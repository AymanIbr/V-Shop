<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class CartService
{
    private const COOKIE  = 'cart';
    private const MINUTES = 10080;   // 7 days
    private const MAX_QTY = 99;


    public function getCartCount(): int
    {
        return (int) $this->quantities()->sum();
    }

    public function getCartItems(): Collection
    {
        $quantities = $this->quantities();

        return Product::with('images')
            ->where('is_published', true)
            ->whereIn('id', $quantities->keys())    // product_id
            ->get()
            ->map(fn (Product $product) => [
                'product'  => $product,
                'quantity' => $quantities[$product->id],
                'subtotal' => round((float) $product->price * $quantities[$product->id], 2),
            ]);
    }

    public function getTotal(): float
    {
        return (float) $this->getCartItems()->sum('subtotal');
    }


    public function add(Product $product, int $quantity = 1): void
    {
        $this->setQuantity($product, $this->quantities()->get($product->id, 0) + $quantity);
    }

    public function setQuantity(Product $product, int $quantity): void
    {
        $quantity = min($quantity, self::MAX_QTY, $product->quantity);   // لا تتجاوز المخزون

        if ($quantity < 1) {
            $this->remove($product);
            return;
        }

        if ($user = auth()->user()) {
            $user->cartItems()->updateOrCreate(['product_id' => $product->id], ['quantity' => $quantity]);
        } else {
            $this->saveGuest($this->guestQuantities()->put($product->id, $quantity));
        }
    }

    public function remove(Product $product): void
    {
        if ($user = auth()->user()) {
            $user->cartItems()->where('product_id', $product->id)->delete();
        } else {
            $this->saveGuest($this->guestQuantities()->except($product->id));
        }
    }

    public function clear(): void
    {
        auth()->user()?->cartItems()->delete();

        Cookie::queue(Cookie::forget(self::COOKIE));
    }


    public function saveCookieCartItemsToDatabase(User $user): void
    {
        $guest = $this->guestQuantities();

        if ($guest->isEmpty()) {
            return;
        }

        $validIds = Product::where('is_published', true)->whereIn('id', $guest->keys())->pluck('id');
        $existing = $user->cartItems()->pluck('quantity', 'product_id');

        DB::transaction(function () use ($user, $guest, $validIds, $existing) {
            foreach ($validIds as $id) {
                $user->cartItems()->updateOrCreate(
                    ['product_id' => $id],
                    ['quantity' => min($existing->get($id, 0) + $guest[$id], self::MAX_QTY)]
                );
            }
        });

        Cookie::queue(Cookie::forget(self::COOKIE));
    }


    /** [product_id => quantity] للمسجّل أو الزائر */
    private function quantities(): Collection
    {
        return auth()->check()
            ? auth()->user()->cartItems()->pluck('quantity', 'product_id')
            : $this->guestQuantities();
    }

    private function guestQuantities(): Collection
    {
        $items = json_decode(request()->cookie(self::COOKIE, '[]'), true);

        return collect(is_array($items) ? $items : [])
            ->filter(fn ($i) => is_array($i)
                && is_numeric($i['product_id'] ?? null)
                && is_numeric($i['quantity'] ?? null))
            ->groupBy('product_id')
            ->map(fn ($group) => min(self::MAX_QTY, (int) $group->sum('quantity')))
            ->filter(fn ($quantity) => $quantity > 0);
    }

    private function saveGuest(Collection $quantities): void
    {
        $items = $quantities
            ->map(fn ($quantity, $id) => ['product_id' => $id, 'quantity' => $quantity])
            ->values();

        Cookie::queue(self::COOKIE, $items->toJson(), self::MINUTES);
    }
}
