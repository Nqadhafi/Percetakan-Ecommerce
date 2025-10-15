<?php

namespace App\Domain\POP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PopProduct extends Model
{
    protected $table = 'pop_products';
    protected $fillable = ['name','slug','base_sku','is_active'];
    protected $casts = [
  'images_json' => 'array',
];

    public function specs(): HasMany {
        return $this->hasMany(PopSpec::class, 'product_id');
    }
}
