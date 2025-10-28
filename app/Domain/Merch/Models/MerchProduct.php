<?php

namespace App\Domain\Merch\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchProduct extends Model
{
    protected $table = 'merch_products';

    protected $fillable = [
        'name',
        'slug',
        'variant_label',
        'min_order_qty',
        'thumbnail_url',
        'images_json',
        'is_active',
    ];

    protected $casts = [
        'images_json' => 'array',
        'is_active'   => 'boolean',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(MerchVariant::class, 'merch_product_id')
            ->where('is_active', true);
    }
}
