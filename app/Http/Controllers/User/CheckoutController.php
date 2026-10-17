<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    private StripeClient $stripe;

    public function __construct(private CartService $cart)
    {
        $this->stripe = new StripeClient(
            env('SECRET_KEY')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $address = $user->addresses()->findOrFail($data['address_id']);
        $items = $this->cart->getCartItems();

        if ($items->isEmpty()) {
            return back()->withErrors([
                'cart' => 'Your cart is empty.',
            ]);
        }

        try {
            $order = DB::transaction(function () use ($user, $address, $items) {
                $order = $user->orders()->create([
                    'user_address_id' => $address->id,
                    'total_price' => $items->sum('subtotal'),
                    'status' => 'pending',
                ]);

                foreach ($items as $item) {
                    $order->items()->create([
                        'product_id' => $item['product']->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['product']->price,
                    ]);
                }

                $order->payments()->create([
                    'amount' => $order->total_price,
                    'type' => 'stripe',
                    'status' => 'pending',
                ]);

                return $order;
            });

            // stripe payment
            $session = $this->stripe->checkout->sessions->create([
                'mode' => 'payment',
                'customer_email' => $user->email,

                'line_items' => $items->map(fn($item) => [
                    'quantity' => $item['quantity'],
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => (int) round(
                            $item['product']->price * 100
                        ),
                        'product_data' => [
                            'name' => $item['product']->title,
                        ],
                    ],
                ])->values()->all(),

                'metadata' => [
                    'order_id' => (string) $order->id,
                ],

                'success_url' => route('checkout.success')
                    . '?session_id={CHECKOUT_SESSION_ID}',

                'cancel_url' => route('checkout.cancel', $order),
            ]);

            $order->update([
                'session_id' => $session->id,
            ]);

            return Inertia::location($session->url);
        } catch (ApiErrorException $e) {
            report($e);

            return back()->withErrors([
                'cart' => 'Unable to start payment. Please try again.',
            ]);
        }
    }

    public function success(Request $request)
    {
        $data = $request->validate([
            'session_id' => ['required', 'string'],
        ]);

        $order = $request->user()->orders()
            ->where('session_id', $data['session_id'])
            ->firstOrFail();

        try {
            // contact with service stripe
            $session = $this->stripe->checkout->sessions
                ->retrieve($order->session_id);

            if (
                $session->payment_status !== 'paid' ||
                $session->currency !== 'usd' ||
                $session->amount_total != round($order->total_price * 100) ||
                $session->metadata->order_id != $order->id
            ) {
                return redirect()->route('cart.index')
                    ->with('error', 'Payment not confirmed.');
            }

            if ($order->status === 'pending') {
                DB::transaction(function () use ($order) {
                    $order->update(['status' => 'paid']);

                    $order->payments()
                        ->where('type', 'stripe')
                        ->update(['status' => 'paid']);
                });
            }

            if ($order->status !== 'paid') {
                return redirect()->route('cart.index')
                    ->with('error', 'Payment not confirmed.');
            }

            $this->cart->clear();

            return Inertia::render('User/CheckoutSuccess', [
                'order' => $order->load('items.product'),
            ]);
        } catch (ApiErrorException $e) {
            report($e);

            return redirect()->route('cart.index')
                ->with('error', 'Unable to verify payment.');
        }
    }
    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status === 'paid') {
            return redirect()->route('checkout.success', [
                'session_id' => $order->session_id,
            ]);
        }

        try {
            if ($order->session_id) {
                $session = $this->stripe->checkout->sessions
                    ->retrieve($order->session_id);

                if ($session->payment_status === 'paid') {
                    return redirect()->route('checkout.success', [
                        'session_id' => $order->session_id,
                    ]);
                }

                if ($session->status === 'open') {
                    $this->stripe->checkout->sessions
                        ->expire($order->session_id);
                }
            }
        } catch (ApiErrorException $e) {
            report($e);

            return redirect()->route('cart.index')
                ->with('error', 'Unable to verify payment.');
        }

        $order->update(['status' => 'cancelled']);

        $order->payments()
            ->where('type', 'stripe')
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        return redirect()->route('cart.index')
            ->with('error', 'Payment was cancelled.');
    }
}
