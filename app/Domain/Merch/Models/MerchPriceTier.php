<?php

namespace App\Domain\Merch\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchPriceTier extends Model
{
    protected $table = 'merch_price_tiers';

    protected $fillable = [
        'variant_id',
        'min_qty',
        'unit_price',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(MerchVariant::class, 'variant_id');
    }
}
