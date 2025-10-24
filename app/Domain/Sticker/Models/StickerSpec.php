<?php
// app/Domain/Sticker/Models/StickerSpec.php
namespace App\Domain\Sticker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StickerSpec extends Model
{
    protected $table = 'sticker_specs';
    protected $fillable = ['product_id','material','supports_lamination','min_qty','is_active'];
    protected $casts = ['supports_lamination'=>'boolean','is_active'=>'boolean'];

    public function product(): BelongsTo { return $this->belongsTo(StickerProduct::class, 'product_id'); }
    public function priceTiers(): HasMany { return $this->hasMany(StickerPriceTier::class, 'spec_id')->orderBy('min_qty'); }
}
