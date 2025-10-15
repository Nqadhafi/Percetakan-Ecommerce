<?php

namespace App\Domain\MMT\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MmtProduct extends Model
{
    protected $table = 'mmt_products';
    protected $fillable = ['name','slug','base_sku','is_active','area_unit','min_area'];
    protected $casts = [
  'images_json' => 'array',
];

    public function materials(): HasMany { return $this->hasMany(MmtMaterialPrice::class, 'product_id'); }
    public function finishing(): HasMany { return $this->hasMany(MmtFinishing::class, 'product_id'); }
    public function packages(): HasMany { return $this->hasMany(MmtPackagePrice::class, 'product_id'); }

}
