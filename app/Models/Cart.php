<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id','session_id','status',
        'subtotal','discount_total','shipping_total','grand_total',
    ];

    public function items(): HasMany {
        return $this->hasMany(CartItem::class);
    }

    public function recalcTotals(): void {
        $subtotal = $this->items->sum('total_price');
        $this->update([
            'subtotal' => $subtotal,
            'grand_total' => $subtotal - $this->discount_total + $this->shipping_total,
        ]);
    }
}
