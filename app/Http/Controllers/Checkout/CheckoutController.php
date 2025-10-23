<?php

namespace App\Http\Controllers\Checkout;

use App\Commerce\Cart\CartService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutSubmitRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        // Ambil cart via service yang sudah ada
        /** @var \App\Services\CartService $cartService */
        $cartService = app(CartService::class);
        $cart = $cartService->getCurrentCart($request);

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        // Siapkan data untuk wizard (Step 1-3 berada di FE)
        return Inertia::render('Checkout/Wizard', [
            'cart' => [
                'items' => $cart->items->map(function($i){
                    return [
                        'id' => $i->id,
                        'product_type' => $i->product_type,
                        'product_id' => $i->product_id,
                        'name' => $i->name,
                        'qty' => $i->qty,
                        'unit_price' => $i->unit_price,
                        'total_price' => $i->unit_price * $i->qty,
                        'note' => $i->note,
                        'spec_snapshot' => $i->spec_snapshot,
                        'pricing_breakdown' => $i->pricing_breakdown,
                    ];
                }),
                'subtotal' => $cart->items->sum(fn($i) => $i->unit_price * $i->qty),
            ],
            'profile_prefill' => [
                'name' => $request->user()->profile->full_name ?? $request->user()->name ?? '',
                'phone' => $request->user()->profile->phone ?? '',
                'address' => $request->user()->profile->address ?? '',
            ],
            'config' => [
                'min_order_total' => 15000, // diterapkan nanti saat checkout rules
            ],
        ]);
    }

    public function submit(CheckoutSubmitRequest $request)
    {
        /** @var \App\Services\CartService $cartService */
        $cartService = app(CartService::class);
        $cart = $cartService->getCurrentCart($request);

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $subtotal = (int) $cart->items->sum(fn($i) => $i->unit_price * $i->qty);

        // (Opsional, nanti) enforce minimum order 15k pada checkout:
        $min = 15000;
        $adjustment = 0;
        if ($subtotal > 0 && $subtotal < $min) {
            $adjustment = $min - $subtotal;
        }

        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('proofs', 'public');
        }

        $order = DB::transaction(function () use ($request, $cart, $subtotal, $adjustment, $proofPath) {
            $order = Order::create([
                'user_id'          => optional($request->user())->id,
                'code'             => Order::generateCode(),
                'customer_name'    => $request->customer_name,
                'customer_phone'   => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'payment_method'   => $request->payment_method, // 'bank' | 'qris'
                'payment_proof_path' => $proofPath,
                'subtotal_amount'  => $subtotal,
                'discount_amount'  => 0,
                'shipping_amount'  => 0,
                'adjustment_amount'=> $adjustment,
                'total_amount'     => $subtotal + $adjustment,
                'status'           => 'pending',
                'meta'             => [
                    'payment_note' => $request->input('payment_note'),
                ],
            ]);

            foreach ($cart->items as $ci) {
                $unit = (int) $ci->unit_price;
                $qty  = (int) $ci->qty;
                OrderItem::create([
                    'order_id'         => $order->id,
                    'product_type'     => $ci->product_type,
                    'product_id'       => $ci->product_id,
                    'product_name'     => $ci->name,
                    'spec_snapshot'    => $ci->spec_snapshot,
                    'pricing_breakdown'=> $ci->pricing_breakdown,
                    'note'             => $ci->note,
                    'qty'              => $qty,
                    'unit_price'       => $unit,
                    'total_price'      => $unit * $qty,
                ]);
            }

            return $order;
        });

        // kosongkan cart
        $cartService->clear($request);

        return redirect()->route('checkout.success', $order->code)
            ->with('success', 'Pesanan berhasil dibuat. Kami akan mengecek pembayaran Anda.');
    }

public function success(Request $request, string $code)
{
    $order = Order::where('code', $code)->firstOrFail();

    return Inertia::render('Checkout/Success', [
        'order' => [
            'code' => $order->code,
            'total' => $order->total_amount,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_proof_url' => $order->payment_proof_url,
            'created_at' => $order->created_at->toDateTimeString(),
        ],
    ]);
}

}
