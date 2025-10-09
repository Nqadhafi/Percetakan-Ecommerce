<?php

namespace App\Domain\POP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopPriceTier extends Model
{
    protected $table = 'pop_price_tiers';
    protected $fillable = ['spec_id','min_qty','unit_price'];

    public function spec(): BelongsTo {
        return $this->belongsTo(PopSpec::class, 'spec_id');
    }
}
