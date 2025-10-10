<?php

namespace App\Commerce\Cart\Http\Controllers;

use App\Commerce\Cart\CartService;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(Request $r, CartService $service)
    {
        $cart = $service->getCurrentCart($r)->load('items');
        return Inertia::render('Cart/Index', ['cart' => $cart]);
    }

    public function add(Request $r, CartService $service)
    {
        $data = $r->validate([
            'product_type' => ['required','in:pop,mmt,sticker'],
            'product_id'   => ['required','integer'],
            'name'         => ['required','string'],
            'qty'          => ['required','integer','min:1'],
            'unit_price'   => ['required','numeric','min:0'],
            'spec_snapshot'=> ['required','array'],
            'pricing_breakdown' => ['required','array'],
        ]);

        $cart = $service->getCurrentCart($r);
        $item = $service->addItem($cart, $data);

        return response()->json(['ok'=>true,'item'=>$item,'cart'=>$cart->fresh('items')]);
    }

    public function update(Request $r, CartService $service, CartItem $item)
    {
        $data = $r->validate(['qty'=>['required','integer','min:1']]);
        $service->updateQty($item, $data['qty']);
        return response()->json(['ok'=>true]);
    }

    public function remove(Request $r, CartService $service, CartItem $item)
    {
        $service->removeItem($item);
        return response()->json(['ok'=>true]);
    }
}
