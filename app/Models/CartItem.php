<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id','product_type','product_id','name','qty',
        'unit_price','total_price','spec_snapshot','pricing_breakdown','note'
    ];

    protected $casts = [
        'spec_snapshot' => 'array',
        'pricing_breakdown' => 'array',
    ];

    public function cart(): BelongsTo {
        return $this->belongsTo(Cart::class);
    }
}
