<?php

namespace App\Domain\POP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PopSpec extends Model
{
    protected $table = 'pop_specs';
    protected $fillable = [
        'product_id','size','material','side','lamination','cutting',
        'min_qty','step_qty','is_active'
    ];

    public const CUT_TANPA = 'tanpa_potong';
    public const CUT_LURUS = 'potong_lurus';
    public const CUT_DIE   = 'potong_pola_die_cut';

    public function product(): BelongsTo {
        return $this->belongsTo(PopProduct::class, 'product_id');
    }

    public function priceTiers(): HasMany {
        return $this->hasMany(PopPriceTier::class, 'spec_id')->orderBy('min_qty', 'asc');
    }
}
