<?php

namespace App\Commerce\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getCurrentCart(Request $request): Cart
    {
        $query = Cart::query()->where('status', 'draft');

        if ($user = $request->user()) {
            $cart = $query->where('user_id', $user->id)->first();
        } else {
            $sid = $request->session()->getId();
            $cart = $query->where('session_id', $sid)->first();
        }

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $user->id ?? null,
                'session_id' => $request->session()->getId(),
            ]);
        }

        return $cart;
    }

    public function addItem(Cart $cart, array $data): CartItem
    {
        return DB::transaction(function () use ($cart, $data) {
            $item = $cart->items()->create([
                'product_type' => $data['product_type'],
                'product_id'   => $data['product_id'],
                'name'         => $data['name'],
                'qty'          => $data['qty'],
                'unit_price'   => $data['unit_price'],
                'total_price'  => $data['qty'] * $data['unit_price'],
                'spec_snapshot'=> $data['spec_snapshot'],
                'pricing_breakdown' => $data['pricing_breakdown'],
            ]);

            $cart->recalcTotals();

            return $item;
        });
    }

    public function updateQty(CartItem $item, int $qty): void
    {
        $item->update([
            'qty' => $qty,
            'total_price' => $qty * $item->unit_price,
        ]);
        $item->cart->recalcTotals();
    }

    public function removeItem(CartItem $item): void
    {
        $cart = $item->cart;
        $item->delete();
        $cart->recalcTotals();
    }
}
