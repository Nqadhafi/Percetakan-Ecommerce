<?php

namespace App\Domain\BusinessPrint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizAddonPricing extends Model
{
    protected $table = 'biz_addon_pricing';

    protected $fillable = [
        'biz_product_id',
        'code',
        'label',
        'amount_per_unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(BizProduct::class, 'biz_product_id');
    }
}
