<?php

namespace App\Domain\MMT\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MmtFinishing extends Model
{
    protected $table = 'mmt_finishing';
    protected $fillable = ['product_id','finishing','price_type','price_value','is_active'];

    public function product(): BelongsTo { return $this->belongsTo(MmtProduct::class, 'product_id'); }
}
