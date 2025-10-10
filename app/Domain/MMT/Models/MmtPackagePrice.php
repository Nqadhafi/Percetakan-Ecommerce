<?php

namespace App\Domain\MMT\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MmtPackagePrice extends Model
{
    protected $table = 'mmt_package_prices';
    protected $fillable = ['product_id','material','size','base_price'];
    public function product(): BelongsTo { return $this->belongsTo(MmtProduct::class, 'product_id'); }
}
