<?php

namespace App\Domain\MMT\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MmtMaterialPrice extends Model
{
    protected $table = 'mmt_material_prices';
    protected $fillable = ['product_id','material','base_price_per_m2','is_active'];

    public function product(): BelongsTo { return $this->belongsTo(MmtProduct::class, 'product_id'); }
}
