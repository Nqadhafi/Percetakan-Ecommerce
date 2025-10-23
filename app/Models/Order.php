<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    protected $fillable = [
        'user_id','code','customer_name','customer_phone','customer_address',
        'payment_method','payment_proof_path',
        'subtotal_amount','discount_amount','shipping_amount','adjustment_amount','total_amount',
        'status','meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }

    public static function generateCode(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';
        // singkat: random 4 digit; bisa diganti sequence harian
        return $prefix.str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        return $this->payment_proof_path ? Storage::disk('public')->url($this->payment_proof_path) : null;
    }
}
