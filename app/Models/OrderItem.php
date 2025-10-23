<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id','product_type','product_id','product_name',
        'spec_snapshot','pricing_breakdown','note',
        'qty','unit_price','total_price',
    ];

    protected $casts = [
        'spec_snapshot' => 'array',
        'pricing_breakdown' => 'array',
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
