<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;

class EnsureCartNotEmpty {
  public function handle(Request $r, Closure $next){
    $cartService = app(\App\Commerce\Cart\CartService::class);
    $cart = $cartService->getCurrentCart($r);
    if (!$cart || $cart->items->isEmpty()){
      return redirect()->route('cart.index')->with('error','Keranjang masih kosong.');
    }
    return $next($r);
  }
}