<?php

namespace App\Domain\BusinessPrint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BizProduct extends Model
{
    protected $table = 'biz_products';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'unit_label',
        'base_price',
        'thumbnail_url',
        'images_json',
        'spec_json',
        'is_active',
    ];

    protected $casts = [
        'images_json' => 'array',
        'spec_json'   => 'array',
        'is_active'   => 'boolean',
    ];

    public function addons(): HasMany
    {
        return $this->hasMany(BizAddonPricing::class, 'biz_product_id')
            ->where('is_active', true);
    }
}
