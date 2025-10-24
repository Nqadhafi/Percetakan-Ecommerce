<?php
// app/Domain/Sticker/Models/StickerPriceTier.php
namespace App\Domain\Sticker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StickerPriceTier extends Model
{
    protected $table = 'sticker_price_tiers';
    protected $fillable = ['spec_id','min_qty','unit_price'];

    public function spec(): BelongsTo { return $this->belongsTo(StickerSpec::class, 'spec_id'); }
}
