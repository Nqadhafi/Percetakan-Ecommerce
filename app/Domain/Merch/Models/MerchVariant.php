<?php

namespace App\Domain\Merch\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchVariant extends Model
{
    protected $table = 'merch_variants';

    protected $fillable = [
        'merch_product_id',
        'code',
        'label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(MerchProduct::class, 'merch_product_id');
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(MerchPriceTier::class, 'variant_id')
            ->orderBy('min_qty', 'asc');
    }
}
